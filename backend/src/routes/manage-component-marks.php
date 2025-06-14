<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/components', function (Request $request, Response $response) {
    $pdo = getPDO();
    $courseId = $request->getQueryParams()['course_id'] ?? null;

    if (!$courseId) {
        $response->getBody()->write(json_encode(['error' => 'Missing course_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT component_id, component_name, max_mark FROM components WHERE course_id = ?");
    $stmt->execute([$courseId]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/components', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    if (!isset($data['course_id'], $data['component_name'], $data['max_mark'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("INSERT INTO components (course_id, component_name, max_mark) VALUES (?, ?, ?)");
    $stmt->execute([$data['course_id'], $data['component_name'], $data['max_mark']]);

    $response->getBody()->write(json_encode(['message' => 'Component added']));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: Fetch marks for a student and course
$app->get('/marks', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();
        $params = $request->getQueryParams();

        $studentId = $params['student_id'] ?? null;
        $courseId = $params['course_id'] ?? null;

        if (!$studentId || !$courseId) {
            $response->getBody()->write(json_encode(["error" => "Missing student_id or course_id"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($marks));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    try {
        if (!isset($data['student_id'], $data['component_id'], $data['mark'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Check if record exists
        $stmt = $pdo->prepare("SELECT mark_id FROM marks WHERE student_id = ? AND component_id = ?");
        $stmt->execute([$data['student_id'], $data['component_id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE marks SET mark = ? WHERE mark_id = ?");
            $stmt->execute([$data['mark'], $existing['mark_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO marks (student_id, component_id, mark) VALUES (?, ?, ?)");
            $stmt->execute([$data['student_id'], $data['component_id'], $data['mark']]);
        }

        $response->getBody()->write(json_encode(['message' => 'Mark saved']));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Database error',
            'details' => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});




$app->get('/courses', function ($request, $response, $args) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM courses");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});

