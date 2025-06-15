<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/tools/textModifier.php';


use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();



// CORS middleware
$app->add(function ($request, $handler) {
    // Handle CORS preflight request
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withStatus(200);
    }

    // Handle actual requests
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
require __DIR__ . '/../src/routes/lecturer-course-management.php';
require __DIR__ . '/../src/routes/student-enrollment.php';
require __DIR__ . '/../src/routes/manage-component-marks.php';
require __DIR__ . '/../src/routes/add-final-exam-marks.php';
require __DIR__ . '/../src/routes/view-mark-breakdown.php';


$app->run();
