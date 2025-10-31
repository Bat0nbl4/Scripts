<?php

namespace vendor\routing;

class Router
{
    protected static $routes = [];
    protected static $groupStack = [];

    public static function get(string $path, array $callback, string $name, array $middleware = []): void
    {
        self::addRoute('GET', $path, $callback, $name, $middleware);
    }

    public static function post(string $path, array $callback, string $name, array $middleware = []): void
    {
        self::addRoute('POST', $path, $callback, $name, $middleware);
    }

    public static function group(string $prefix, callable $callback, array $middleware = []): void
    {
        self::$groupStack[] = [
            'prefix' => $prefix,
            'middleware' => $middleware,
        ];

        $callback();

        array_pop(self::$groupStack);
    }

    protected static function addRoute(string $method, string $path, array $callback, string $name, array $routeMiddleware = []): void
    {
        $path = self::applyGroupPrefix($path);
        if (defined('USE_BASE_PATH') && USE_BASE_PATH) {
            $path = BASE_PATH . $path;
        }

        $groupMiddleware = [];
        foreach (self::$groupStack as $group) {
            $groupMiddleware = array_merge($groupMiddleware, $group['middleware']);
        }

        $middleware = array_merge($groupMiddleware, $routeMiddleware);

        self::$routes[$name] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middleware' => $middleware,
        ];
    }

    protected static function applyGroupPrefix(string $path): string
    {
        $fullPrefix = '';
        foreach (self::$groupStack as $group) {
            $fullPrefix .= $group['prefix'];
        }
        return $fullPrefix . $path;
    }

    public static function route(string $name, array $params = []): string
    {
        if (!isset(self::$routes[$name])) {
            http_response_code(404);
            throw new \Exception("Route '{$name}' not found.");
        }

        $path = BASE_METHOD."://".APP_DOMEN.self::$routes[$name]['path'];
        $query = http_build_query($params);
        return $path . ($query ? '?'.$query : '');
    }

    public static function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    public static function resolve(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method || $route['path'] !== $uri) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareClass) {
                $middlewareClass::handle();
            }

            [$class, $methodName] = $route['callback'];
            $controller = new $class();

            // Получение параметров метода контроллера
            $reflectionMethod = new \ReflectionMethod($class, $methodName);
            $parameters = $reflectionMethod->getParameters();
            $args = [];

            foreach ($parameters as $param) {
                $paramName = $param->getName();
                $value = $_GET[$paramName] ?? null;

                // Приведение типа, если требуется
                if ($value !== null && $param->getType()) {
                    $type = $param->getType()->getName();
                    settype($value, $type);
                }

                $args[] = $value ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }

            $controller->$methodName(...$args);
            return;
        }

        http_response_code(404);
    }
}