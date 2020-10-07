<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;
use App\Repository;
use Slim\Middleware\MethodOverrideMiddleware;

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
$app->add(MethodOverrideMiddleware::class);
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
    $repo = new Repository();
    $id = $args['id'];
    $user = $repo->get($id);
    $params = [
        'user' => $user,
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/users/{id}/edit', function ($request, $response, $args) {
    $repo = new Repository();
    $id = $args['id'];
    $user = $repo->get($id);
    $params = [
        'user' => $user,
        'errors' => [],
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');


$app->get('/users', function ($request, $response) {
    $repo = new Repository();
    $term = $request->getQueryParam('term', null);
    $allUsers = $repo->all();
    $filteredUsers = array_filter($allUsers, fn ($user) => is_numeric(strpos($user->name, $term)));
    $users = $term ? $filteredUsers : $allUsers;
    $flash = $this->get('flash')->getMessages();
    $params = [
        'term' => $term,
        'users' => $users,
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

$app->patch('/users/{id}', function ($request, $response, $args) use ($router) {
    $id = $args['id'];
    $repo = new Repository();
    $user = $repo->get($id);
    $data = $request->getParsedBodyParam('user');
    print_r($data);
    $validator = new Validator();
    $errors = $validator->validate($data);
    if (count($errors) === 0) {
        $user->name = $data['name'];
        $user->email = $data['email'];
        $repo->save($user);
        $url = $router->urlFor('editUser', ['id' => $user->id]);
        return $response->withRedirect($url);
    }
    $params = [
        'user' => $user,
        'errors' => $errors,
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/edit.phtml', $params);
});

$app->run();
