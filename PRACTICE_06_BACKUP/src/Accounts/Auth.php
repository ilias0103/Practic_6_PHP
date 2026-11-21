<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;
use Course\Http\Response;
use Course\Security\AccountSession;

final class Auth
{
    public function __construct(private UserRepository $users) {}
    public function current(): ?User
    {
        $id = $_SESSION['user_id'] ?? null;
    
        if (!is_int($id)) {
            return null;
        }
    
        $user = $this->users->find($id);
    
        if ($user === null) {
            unset($_SESSION['user_id']);
            return null;
        }
    
        return $user;
    }
    public function login(string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email), 'UTF-8');
    
        $user = $this->users->byEmail($email);
    
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return false;
        }
    
        AccountSession::renew();
    
        $_SESSION['user_id'] = $user->id;
    
        return true;
    }
    public function logout(): void
{
    AccountSession::finish();
}
public function guard(bool $admin = false): User|Response
{
    $user = $this->current();

    // Если пользователь не авторизован
    if ($user === null) {
        return Response::redirect('/login');
    }

    // Если требуется администратор
    if ($admin && $user->role !== 'admin') {
        throw new HttpError(
            403,
            'forbidden',
            'Недостаточно прав для выполнения этого действия'
        );
    }

    return $user;
}
}
