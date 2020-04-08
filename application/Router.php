<?php
// Simple routing for application returns
class Router 
{
    protected $routes = [
        "" => "controllers/index.controller.php"
    ];

    public function route($uri) {
        if(array_key_exists($uri, $this->routes)) {
            return $this->routes[$uri];
        } else {
            return "controllers/404.controller.php";
        }
    }
}

