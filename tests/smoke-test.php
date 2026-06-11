<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$tests = [
    ['GET', '/login'],
    ['GET', '/register'],
    ['GET', '/'],
];

foreach ($tests as [$method, $uri]) {
    $request = Illuminate\Http\Request::create($uri, $method);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $label = $method.' '.$uri;
    echo str_pad($label, 25).' => '.$status.PHP_EOL;
    if ($status >= 500) {
        echo substr($response->getContent(), 0, 600).PHP_EOL;
    }
    $kernel->terminate($request, $response);
}
