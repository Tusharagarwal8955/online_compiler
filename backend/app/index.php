<?php
require_once("./../PHPLibrary/Router/Router.php");
require_once("./../app_autoload.php");


$router = new \Bramus\Router\Router();

$router->get("compiler/language", function () {
    $obj = new Compiler();
    echo $obj->getSupportedLanguage();
});
$router->post("compiler/code", function () {
    $obj = new Compiler();
    echo $obj->compileCode();
});

//TEST ROUTES
$router->get("test/name", function () {
    $first_name = $_GET["first_name"] ?? "";
    $second_name = $_GET["second_name"] ?? "";

    echo "Test data: " . $first_name . " " . $second_name;
});


//ERROR ROUTE
$router->set404(function () {
    $obj = new GetFunction();
    $response = ["error" => "No page found!"];
    echo $obj->sendResponse($response, 404);
});
$router->run();
