<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/tools/textModifier.php';


use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

// Handle OPTIONS requests
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// === Include route files ===
require __DIR__ . '/../src/routes/course-management.php';
require __DIR__ . '/../src/routes/section-management.php';
require __DIR__ . '/../src/routes/user-management.php';

$app->run();
