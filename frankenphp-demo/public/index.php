<?php

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $r->addRoute('GET', '/todos', 'getTodos');
    $r->addRoute('POST', '/todos', 'addTodo');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

header('Content-Type: application/json');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $response = call_user_func($handler);
        echo json_encode($response);
        break;
}

function getTodos() {
    return [
        ['id' => 1, 'task' => 'Learn FrankenPHP'],
        ['id' => 2, 'task' => 'Try RoadRunner'],
    ];
}

function addTodo() {
    $data = json_decode(file_get_contents('php://input'), true);
    return [
        'status' => 'added',
        'todo' => $data
    ];
}
