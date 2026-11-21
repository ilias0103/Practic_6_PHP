<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;

final class Registration
{
    /** @return array{name:string,email:string,password:string} */
   
public static function validate(array $input): array
{
    $errors = [];

    // Проверяем, что форма не содержит посторонних полей
    $allowed = ['name', 'email', 'password', 'confirmation'];

    foreach (array_keys($input) as $key) {
        if (!in_array($key, $allowed, true)) {
            $errors[$key] = 'Недопустимое поле';
        }
    }

    // Имя
    $name = is_string($input['name'] ?? null)
        ? trim($input['name'])
        : '';

    if (
        !mb_check_encoding($name, 'UTF-8')
        || mb_strlen($name, 'UTF-8') < 2
        || mb_strlen($name, 'UTF-8') > 80
    ) {
        $errors['name'] = 'Имя должно содержать от 2 до 80 символов';
    }

    // Email
    $email = is_string($input['email'] ?? null)
        ? mb_strtolower(trim($input['email']), 'UTF-8')
        : '';

    if (
        strlen($email) > 254
        || !preg_match('/\A[\x00-\x7F]*\z/', $email)
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false
    ) {
        $errors['email'] = 'Введите корректный email';
    }

    // Пароль
    $password = is_string($input['password'] ?? null)
        ? $input['password']
        : '';

    if (
        !mb_check_encoding($password, 'UTF-8')
        || mb_strlen($password, 'UTF-8') < 8
        || strlen($password) > 72
        || str_contains($password, "\0")
    ) {
        $errors['password'] = 'Пароль должен содержать не менее 8 символов и не более 72 байт';
    }

    // Подтверждение пароля
    $confirmation = $input['confirmation'] ?? null;

    if (!is_string($confirmation) || $confirmation !== $password) {
        $errors['confirmation'] = 'Пароли не совпадают';
    }

    if ($errors !== []) {
        throw new HttpError(
            422,
            'validation_failed',
            'Проверьте правильность заполнения формы',
            $errors
        );
    }

    return [
        'name' => $name,
        'email' => $email,
        'password' => $password,
    ];
}

}
