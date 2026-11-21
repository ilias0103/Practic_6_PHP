<?php
declare(strict_types=1);

use Course\Api\{Catalog, Login};
use Course\Http\Router;

return static function(Router $router, Catalog $catalog, Login $login): void {

    $router->add('POST', '/api/login',
        static fn($request) => $login($request)
    );

    $router->add('GET', '/api/items',
        static fn($request) => $catalog->listing($request)
    );

    $router->add('GET', '/api/items/{id}',
        static fn($request, $id) => $catalog->show($id)
    );

    $router->add('POST', '/api/items',
        static fn($request) => $catalog->create($request)
    );

    $router->add('PUT', '/api/items/{id}',
        static fn($request, $id) => $catalog->update($request, $id)
    );

    $router->add('DELETE', '/api/items/{id}',
        static fn($request, $id) => $catalog->delete($request, $id)
    );
};
