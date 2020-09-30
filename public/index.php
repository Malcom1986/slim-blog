<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;
use App\Repository;

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
session_start();
$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    $response->write('Welcome to Slim!');
    return $response;
});

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => [
            'name' => '',
            'email' => '',
            'password' => '',
            'passwordConfirmation' => '',
        ],
        'errors' => [],
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $name = $request->getQueryParam('name', 'noname');
    $params = [
        'id' => $id,
        'nickname' => "user - {$id}",
        'name' => $name,
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');


$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term', null);
    $filteredUsers = array_filter($users, fn ($user) => is_numeric(strpos($user, $term)));
    $flash = $this->get('flash')->getMessages();
    $params = [
        'term' => $term,
        'users' => $filteredUsers,
        'flash' => $flash,
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->post('/users', function ($request, $response) use ($router) {
    $validator = new Validator();
    $repo = new Repository();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $repo->add($user);
        $url = $router->urlFor('users');
        $this->get('flash')->addMessage('succes', 'User has been created!');
        return $response->withRedirect($url, 302);
    }
    $params = [
        'user' => $user,
        'errors' => $errors,
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);
});

// $app->patch()



$app->run();
