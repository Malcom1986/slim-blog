<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$container = new Container();
$container->set('renderer' ,function () {
	return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

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
});


$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term', null);
    $filteredUsers = array_filter($users, fn ($user) => is_numeric(strpos($user, $term)));

    $params = [
        'term' => $term,
        'users' => $filteredUsers,
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
	$validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
    	$data = json_encode($user);
        $path = realpath(__DIR__ . '/../repository/users');
        $res = file_put_contents($path, $data . "\n", FILE_APPEND);
        return $response->withRedirect('/users', 302);
    }
    $params = [
    	'user' => $user,
    	'errors' => $errors,
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);    
});

$app->get('/headers', function ($request, $response) {
	$headers = json_encode($request->getHeaders(), JSON_PRETTY_PRINT);
	return $response->write($headers);
});

$app->post('/articles', function ($request, $response) {
	return $response->withStatus(302);
});

$app->run();