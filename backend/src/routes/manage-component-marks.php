<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/components', function ($request, $response, $args) {
    $params = $request->getQueryParams();
    $courseId = $params['course_id'] ?? null;
    $sectionId = $params['section_id'] ?? null;

    // Optional: Validate inputs
    if (!$courseId || !$sectionId) {
        return $response->withStatus(400)->write("Missing course_id or section_id");
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM components WHERE course_id = ? AND section_id = ?");
    $stmt->execute([$courseId, $sectionId]);
    $components = $stmt->fetchAll();

    $response->getBody()->write(json_encode($components));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/components', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    if (!isset($data['course_id'], $data['section_id'], $data['component_name'], $data['max_mark'])){
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("INSERT INTO components (course_id, section_id, component_name, max_mark) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['course_id'], $data['section_id'], $data['component_name'], $data['max_mark']]);


    $response->getBody()->write(json_encode(['message' => 'Component added']));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: Fetch marks for a student and course
$app->get('/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['course_id']) || !isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing course_id or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT m.mark_id, m.student_id, m.component_id, m.mark, c.component_name, c.max_mark
        FROM marks m
        JOIN components c ON m.component_id = c.component_id
        WHERE c.course_id = ? AND c.section_id = ?
    ");
    $stmt->execute([$params['course_id'], $params['section_id']]);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
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




$app->get('/fetchCourses', function ($request, $response, $args) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM courses");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/students', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['course_id']) || !isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing course_id or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT s.student_id, s.matric_no, u.name AS student_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        JOIN enrollment e ON s.student_id = e.student_id
        JOIN sections sec ON e.section_id = sec.section_id
        WHERE sec.course_id = ? AND sec.section_id = ?
    ");
    $stmt->execute([$params['course_id'], $params['section_id']]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/sections', function (Request $request, Response $response) {
    $pdo = getPDO();
    $courseId = $request->getQueryParams()['course_id'] ?? null;

    if (!$courseId) {
        $response->getBody()->write(json_encode(['error' => 'Missing course_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT section_id, section_number FROM sections WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/components/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM components WHERE component_id = ?");
    $stmt->execute([$args['id']]);
    $component = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$component) {
        $response->getBody()->write(json_encode(['error' => 'Component not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode($component));
    return $response->withHeader('Content-Type', 'application/json');
});

