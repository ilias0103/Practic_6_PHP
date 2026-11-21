<?php
declare(strict_types=1);

namespace Course\Http;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);

        $hasId = str_contains($path, '{id}');

        $pattern = preg_quote($path, '~');

        $pattern = str_replace(
            '\\{id\\}',
            '([1-9][0-9]{0,8})',
            $pattern
        );

        $this->routes[] = [
            'method' => $method,
            'pattern' => '~^' . $pattern . '$~D',
            'handler' => $handler,
            'hasId' => $hasId,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method);
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $matches = [];

            if (preg_match($route['pattern'], $request->path, $matches) !== 1) {
                continue;
            }

            $routeMethod = $route['method'];

            $allowedMethods[] = $routeMethod;

            if ($routeMethod === 'GET') {
                $allowedMethods[] = 'HEAD';
            }

            $methodMatches = $method === $routeMethod
                || ($method === 'HEAD' && $routeMethod === 'GET');

            if (!$methodMatches) {
                continue;
            }

            if ($route['hasId']) {
                return ($route['handler'])($request, (int) $matches[1]);
            }

            return ($route['handler'])($request);
        }

        if ($allowedMethods !== []) {
            $allowedMethods = array_values(array_unique($allowedMethods));

            throw new HttpError(
                405,
                'method_not_allowed',
                'Метод запроса не разрешён',
                [],
                ['Allow' => implode(', ', $allowedMethods)]
            );
        }

        throw new HttpError(
            404,
            'not_found',
            'Страница не найдена'
        );
    }
}
