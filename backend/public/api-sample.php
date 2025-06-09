<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';


// use Slim and PSR interfaces
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app = AppFactory::create();

$app->get('/hello', function ($request, $response) {
    $response->getBody()->write(json_encode(['message' => 'Hello, world']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/hello/{name}/{yob}', function ($request, $response, $args) {
    $name = $args['name']; 
    $yob = $args['yob'];
    $age = 2025 - $yob;

    $person = ['name' => $name, 'yob' => $yob, 'age'=> $age]; // create person
    $response->getBody()->write(json_encode($person));
    return $response->withHeader('Content-Type', 'application/json');
});

// === Person Routes ===

// GET all persons
$app->get('/person', function ($request, $response) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM PERSON");
    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});


// GET person by ID
$app->get('/person/{id}', function ($request, $response, $args) {
    $id = (int) $args['id'];
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM PERSON WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $person = $stmt->fetch();

    if ($person) {
        $response->getBody()->write(json_encode($person));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $error = ['error' => 'Person not found'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
});


// POST create person
$app->post('/person', function ($request, $response) {
    try {
        $pdo = getPDO();

        // Parse JSON input from request body
         $data = json_decode($request->getBody()->getContents(), true);

        // Extract and validate fields
        $name = $data['name'] ?? null;
        $yob = $data['yob'] ?? null;
        $weight = $data['weight'] ?? null;
        $height = $data['height'] ?? null;
        $bmi = $data['bmi'] ?? null;
        $category = $data['category'] ?? null;
        $age = $data['age'] ?? null;

        // Simple backend validation
        if (!$name || !$weight || !$height) {
            $response->getBody()->write(json_encode(["error" => "Missing required fields"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Insert into the database
        $stmt = $pdo->prepare("
            INSERT INTO person (name, yob, weight, height, bmi, category, age)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $yob, $weight, $height, $bmi, $category, $age]);

        // Response
        $response->getBody()->write(json_encode([
            "message" => "Person created successfully"
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        // Handle DB errors
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


// PUT update full person
$app->put('/person/{id}', function ($request, $response, $args) {
    $id = (int) $args['id'];
    $pdo = getPDO();

    $body = json_decode($request->getBody()->getContents(), true);

    $name     = $body['name']     ?? null;
    $yob      = $body['yob']      ?? null;
    $weight   = $body['weight']   ?? null;
    $height   = $body['height']   ?? null;
    $bmi      = $body['bmi']      ?? null;
    $category = $body['category'] ?? null;
    $age      = $body['age']      ?? null;

    if (!$name || !$yob || !$weight || !$height || !$bmi || !$category || !$age) {
        $error = ['error' => 'Missing one or more required fields'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare("
        UPDATE PERSON SET 
            name = :name,
            yob = :yob,
            weight = :weight,
            height = :height,
            bmi = :bmi,
            category = :category,
            age = :age
        WHERE id = :id
    ");

    $stmt->execute([
        'name'     => $name,
        'yob'      => $yob,
        'weight'   => $weight,
        'height'   => $height,
        'bmi'      => $bmi,
        'category' => $category,
        'age'      => $age,
        'id'       => $id,
    ]);

    if ($stmt->rowCount() > 0) {
        $response->getBody()->write(json_encode(['message' =>  "Update person with ID $id"]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['message' => 'Person not found or no changes']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
});


// PATCH partial update person
$app->patch('/person/{id}', function ($request, $response, $args) {
    $id = (int) $args['id'];
    $pdo = getPDO();

    $data = json_decode($request->getBody()->getContents(), true);
    if (!$data || !is_array($data)) {
        $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $allowedFields = ['name', 'yob', 'weight', 'height', 'bmi', 'category', 'age'];
    $fields = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = :$field";
            $params[$field] = $data[$field];
        }
    }

    if (empty($fields)) {
        $response->getBody()->write(json_encode(['error' => 'No valid fields to update']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $params['id'] = $id;
    $sql = "UPDATE PERSON SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        $response->getBody()->write(json_encode(['message' =>  "Patch person with ID $id"]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['message' => 'Person not found or no changes']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
});


// DELETE person
$app->delete('/person/{id}', function ($request, $response, $args) {
    $id = (int) $args['id'];
    $pdo = getPDO();

    $stmt = $pdo->prepare("DELETE FROM PERSON WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() > 0) {
        $response->getBody()->write(json_encode(['message' =>  "Delete person with ID $id"]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['message' => 'Person not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
});

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

// Allow OPTIONS requests
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->addBodyParsingMiddleware();

$app->run();

