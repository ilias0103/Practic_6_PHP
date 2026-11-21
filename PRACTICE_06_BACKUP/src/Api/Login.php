<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Accounts\UserRepository;
use Course\Http\{Request, Response, HttpError};

final class Login
{
    public function __construct(
        private UserRepository $users,
        private Tokens $tokens
    ) {}

    public function __invoke(Request $request): Response
    {
        $input = Json::object($request);

        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (
            !is_string($email) ||
            !is_string($password) ||
            strlen($email) > 254 ||
            strlen($password) > 72
        ) {
            throw new HttpError(
                422,
                'validation_failed',
                'Некорректные данные авторизации'
            );
        }

        $email = strtolower(trim($email));

        $user = $this->users->byEmail($email);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            throw new HttpError(
                401,
                'invalid_credentials',
                'Неверный email или пароль'
            );
        }

        $token = $this->tokens->issue($user->id);

        return Json::response([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 900,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ]
        ]);
    }
}
