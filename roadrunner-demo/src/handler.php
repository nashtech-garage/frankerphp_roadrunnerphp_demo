<?php

use Spiral\RoadRunner\Http\PSR7Worker;
use Nyholm\Psr7\Factory\Psr17Factory;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

$psr17Factory = new Psr17Factory();
$worker = new PSR7Worker(
    \Spiral\Goridge\StreamRelay::create('php://stdin', 'php://stdout'),
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/todos', 'getTodos');
    $r->addRoute('POST', '/todos', 'addTodo');
});

while ($req = $worker->waitRequest()) {
    try {
        $method = $req->getMethod();
        $uri = $req->getUri()->getPath();
        $routeInfo = $dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $resp = $psr17Factory->createResponse(404)->withBody(
                    $psr17Factory->createStream(json_encode(['error' => 'Not found']))
                );
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $resp = $psr17Factory->createResponse(405)->withBody(
                    $psr17Factory->createStream(json_encode(['error' => 'Method not allowed']))
                );
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $resp = $psr17Factory->createResponse(200)->withBody(
                    $psr17Factory->createStream(json_encode(call_user_func($handler, $req)))
                );
                break;
        }
        $resp = $resp->withHeader('Content-Type', 'application/json');
        $worker->respond($resp);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string)$e);
    }
}

function getTodos() {
    return [
        ['id' => 1, 'task' => 'Learn RoadRunner'],
        ['id' => 2, 'task' => 'Write blog post']
    ];
}

function addTodo($request) {
    $data = json_decode($request->getBody()->getContents(), true);
    return ['status' => 'added', 'todo' => $data];
}
