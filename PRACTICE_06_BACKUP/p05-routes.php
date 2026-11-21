<?php
declare(strict_types=1);

use Course\Http\Request;
use Course\Http\Router;
use Course\Controller\AccountController;
use Course\Controller\AccountCatalogController;

return static function (
    Router $router,
    AccountController $accounts,
    AccountCatalogController $catalog
): void {

    // Каталог
    $router->add('GET', '/', static fn(Request $request) => $catalog->listing($request));
    $router->add('GET', '/items', static fn(Request $request) => $catalog->listing($request));

    // Просмотр и создание товаров
    $router->add('GET', '/items/new', static fn() => $catalog->newForm());
    $router->add('GET', '/items/{id}', static fn(Request $request, int $id) => $catalog->show($id));
    $router->add('POST', '/items', static fn(Request $request) => $catalog->save($request));

    // Редактирование и удаление
    $router->add('GET', '/items/{id}/edit', static fn(Request $request, int $id) => $catalog->editForm($id));
    $router->add('POST', '/items/{id}/edit', static fn(Request $request, int $id) => $catalog->save($request, $id));
    $router->add('POST', '/items/{id}/delete', static fn(Request $request, int $id) => $catalog->delete($request, $id));

    // Формы регистрации и входа
    $router->add('GET', '/register', static fn() => $accounts->form(true));
    $router->add('GET', '/login', static fn() => $accounts->form(false));

    // Регистрация, авторизация и выход
    $router->add('POST', '/register', static fn(Request $request) => $accounts->register($request));
    $router->add('POST', '/login', static fn(Request $request) => $accounts->login($request));
    $router->add('POST', '/logout', static fn(Request $request) => $accounts->logout($request));
};
