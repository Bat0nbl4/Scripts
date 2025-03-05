<?php

namespace vendor\routing;

use vendor\rendering\View;

class Route
{
    protected $routes = [];

    public function get($path, $callback, $name = null)
    {
        $this->routes[] = [
            "method" => "GET",
            "path" => $path,
            "callback" => $callback,
            "name" => $name
        ];
    }
    public function post($path, $callback, $name = null)
    {
        $this->routes[] = [
            "method" => "POST",
            "path" => $path,
            "callback" => $callback,
            "name" => $name
        ];
    }

    public function resolve()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route["path"] !== $uri || !isset($route["method"])) {
                continue;
            }
            list($className, $methodName) = $route["callback"];
            $controller = new $className();
            $result = $controller->$methodName();

            return $result;
        }

        http_response_code(404);
        echo "<br> Page not found";
    }
}