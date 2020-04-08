<?php
require "Router.php";

$uri = $_SERVER["REQUEST_URI"];
$uri = trim($uri, "/");

$router = new Router();

require $router->route($uri);