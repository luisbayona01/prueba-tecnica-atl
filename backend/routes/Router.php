<?php

declare(strict_types=1);

namespace App\Routes;

use App\Utils\ApiException;
use App\Utils\Response;

/**
 * Router simple: registra rutas con patrones {param} y las resuelve
 * contra el metodo HTTP y la URI de la peticion.
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * Ejecuta la ruta que coincida con el metodo y la URI actual.
     */
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $regex = $this->buildRegex($route['pattern']);

            if (preg_match($regex, $path, $matches) === 1) {
                $params = $this->extractParams($route['pattern'], $matches);
                ($route['handler'])($params);
                return;
            }
        }

        Response::error('Ruta no encontrada.', 404);
    }

    /**
     * Convierte '/api/contactos/{id}' en una expresion regular.
     */
    private function buildRegex(string $pattern): string
    {
        $escaped = preg_quote($pattern, '#');
        $regex   = preg_replace('#\\\{([a-zA-Z_]+)\\\}#', '(?P<$1>[0-9]+)', $escaped);

        return '#^' . $regex . '$#';
    }

    private function extractParams(string $pattern, array $matches): array
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $pattern, $names);

        $params = [];
        foreach ($names[1] as $name) {
            $params[$name] = isset($matches[$name]) ? (int) $matches[$name] : 0;
        }

        return $params;
    }
}
