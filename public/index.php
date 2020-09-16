<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->write('Welcome to Slim!');
    return $response;
});


$app->get('/users/{id}', function ($request, $response, $args) {
	$id = $args['id'];
    return $response->write("Users id is {$id}");
});

$app->get('/users', function ($request, $response) {
    return $response->write('GET /users');
});



$app->post('/users', function ($request, $response) {
    return $response->write('POST /users');
});

$app->get('/headers', function ($request, $response) {
	$headers = json_encode($request->getHeaders(), JSON_PRETTY_PRINT);
	return $response->write($headers);
});

$app->post('/articles', function ($request, $response) {
	return $response->withStatus(302);
});

$app->run();