<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Http\Response as Response;

require 'vendor/autoload.php';

use Eshop\Model\Product;
use Eshop\Model\Customer;


$app = new \Slim\App();

$productList = [
    new Product('hruška',5),
    new Product('jablko', 6),
    new Product('mango', 8),
    new Product('ananas', 20)
];

$app->get('/products', function (Request $request, Response $response, array $args) {
    global $productList;
    return $response->withJson($productList, 200);
});

$app->get('/products/{id}', function (Request $request, Response $response, array $args) {
    global $productList;
    foreach ($productList as $key => $product) {
        if ($product->getID() === (int)$args['id']) {
            return $response->withJson($productList[$key], 200);
        }
    }
    return $response->withStatus('404');
});

$customers = [ new Customer("Matouš Škoda", "matts", "asd"),
               new Customer("Honza Novák", "hnovak", "fgh"),
               new Customer("František Dostál", "fdostal", "zxc")];


$app->post('/customers', function (Request $request, Response $response, array $args){

    $body = $request->getParsedBody();
    $errors = [
        "errors"=>[]
    ];

    if ( !isset($body["name"]) ) {
        array_push($errors["errors"], "Missing field name");
    }

    if ( !isset($body["username"]) ) {
        array_push($errors["errors"], "Missing field username");
    }

    if ( !isset($body["password"]) ){
        array_push($errors["errors"], "Missing field password");
    }

    if ( empty($errors["errors"]) ) {
        return $response->withStatus(201);
    } else {
        return $response->withJson( $errors, 400);
    }
});

$mw = function(Request $request, Response $response, $next) {
    global $customers;
    $authHeader = $request->getHeader('Authorization')[0];
    $coded = explode(' ', $authHeader)[1];
    $decoded = base64_decode($coded);
    $username = explode(':', $decoded)[0];
    $password = explode(':', $decoded)[1];

    foreach ( $customers as $key => $customer) {
        if ( ($customer->getUsername() ===  $username) && ($customer->getPassword() === $password) ) {
            $request = $request->withAttribute('user', $key);
            $response = $next($request, $response);
            return $response;
        }
    }

    $response = $response->withStatus(401);
    return $response;
};

$app->get('/customer/current', function(Request $request, Response $response) {
    global $customers;
    $key = $request->getAttribute('user');
    $user = $customers[$key];
    $userJson = [
        'name'=>$user->getName(),
        'username'=>$user->getUsername()
    ];

    $response = $response->withJson($userJson, 200);

    return $response;
})->add($mw);

$app->run();