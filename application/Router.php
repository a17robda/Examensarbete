<?php
// Simple routing for application returns
class Router 
{
    protected $routes = [
        "" => "controllers/index.controller.php",
        "query" => "controllers/query.controller.php"
    ];

    public function route($uri) {
        switch($uri) {
            case "":
                return $this->routes[""];
            break;
            case "query":
                return $this->routes["query"];
            break;
            default:
                return "controllers/404.controller.php";
        break;
        }
    }
}
?>