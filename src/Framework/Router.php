<?php

declare(strict_types=1);

namespace Framework;

require_once __DIR__ . '/../../src/App/Middleware/TemplateDataMiddleware.php';


class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function add(string $method, string $path, array $controller)
    {
        $path = $this->normalizePath($path);
        $this->routes[] =
            [
                'path' => $path,
                'method' => strtoupper($method),
                'controller' => $controller
            ];
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path, "/");
        $path = "/{$path}/";
        $path = preg_replace('#[/]{2,}#', '/', $path);

        return $path;
    }

    public function dispatch(string $path, string $method, Container $container = null)
    {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if (!preg_match("#^{$route['path']}$#", $path) || $route['method'] !== $method) {
                continue;
            }

            [$class, $function] = $route['controller'];

            $controllerInstance = $container ? $container->resolve($class) : new $class;
            $action = fn() => $controllerInstance->{$function}(); // bice upotrebljena nakon sto su svi middleware-i izvresni

            // aktiviranje middleware loop
            foreach ($this->middlewares as $middleware) {
                $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware; // zato sto middleware nije objekat sam po sebi, vec koristi klasu za instanciranje middlware-a
                $action = fn() => $middlewareInstance->process($action);
            }

            $action();
            return; // return se koristi kako bi se sprecila sledeca ruta da bude aktivirana
        }
    }

    public function addMiddleware(string $middleware)
    {
        $this->middlewares[] = $middleware;
    }
}
