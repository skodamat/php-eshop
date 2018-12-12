<?php
require '../vendor/autoload.php';
$app = new \Slim\App();

$app->get('/products', function ($request, $response, $args) {
    return ($response->write('asd'));
});

$app->run();