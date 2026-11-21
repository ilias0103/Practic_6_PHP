<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Accounts\{User, UserRepository};
use Course\Http\{Request, HttpError};

final class Identity
{
    public function __construct(
        private UserRepository $users,
        private Tokens $tokens
    ) {}

    public function require(Request $request, bool $admin = false): User
    {
        $authorization = $request->header('authorization');

        if (
            $authorization === null ||
            preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches) !== 1
        ) {
            throw new HttpError(
                401,
                'missing_token',
                'Требуется Bearer-токен',
                [],
                ['WWW-Authenticate' => 'Bearer']
            );
        }

        $userId = $this->tokens->subject($matches[1]);

        $user = $this->users->find($userId);

        if ($user === null) {
            throw new HttpError(
                401,
                'invalid_token',
                'Пользователь токена не найден',
                [],
                ['WWW-Authenticate' => 'Bearer error="invalid_token"']
            );
        }

        if ($admin && $user->role !== 'admin') {
            throw new HttpError(
                403,
                'forbidden',
                'Недостаточно прав для выполнения операции'
            );
        }

        return $user;
    }
}
