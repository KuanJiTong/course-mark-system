<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/components', function ($request, $response, $args) {
    $params = $request->getQueryParams();
    $sectionId = $params['section_id'] ?? null;

    if (!$sectionId) {
        return $response->withStatus(400)->write("Missing section_id");
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT component_id AS componentId, component_name AS componentName, max_mark AS maxMark FROM components WHERE section_id = ?");
    $stmt->execute([$sectionId]);
    $components = $stmt->fetchAll();

    $response->getBody()->write(json_encode($components));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/components', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();
    $sectionId = $data['sectionId'];
    $componentName = $data['componentName'];
    $maxMark = $data['maxMark'];

    if (!isset($sectionId, $componentName, $maxMark)){
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("INSERT INTO components (section_id, component_name, max_mark) VALUES (?, ?, ?)");
    $stmt->execute([$sectionId, $componentName, $maxMark]);

    $response->getBody()->write(json_encode(['message' => 'Component added']));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: Fetch marks for a student and course
$app->get('/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $sectionId = $params['section_id'];
    if (!isset($sectionId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT m.mark_id AS markId, m.student_id AS studentId, m.mark
        FROM marks m
        JOIN components c ON m.component_id = c.component_id
        WHERE c.section_id = ?
    ");
    $stmt->execute([$sectionId]);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
});



$app->post('/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();
    $studentId = $data['studentId'];
    $componentId = $data['componentId'];
    $mark = $data['mark'];

    try {
        if (!isset($studentId,$componentId,$mark)) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Check if record exists
        $stmt = $pdo->prepare("SELECT mark_id FROM marks WHERE student_id = ? AND component_id = ?");
        $stmt->execute([$studentId,$componentId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE marks SET mark = ? WHERE mark_id = ?");
            $stmt->execute([$data['mark'], $existing['mark_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO marks (student_id, component_id, mark) VALUES (?, ?, ?)");
            $stmt->execute([$studentId,$componentId,$mark]);
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

$app->get('/students', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $sectionId = $params['section_id'];

    if (!isset($sectionId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT s.student_id, s.matric_no, u.name AS student_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        JOIN enrollment e ON s.student_id = e.student_id
        JOIN sections sec ON e.section_id = sec.section_id
        WHERE sec.section_id = ?
    ");
    $stmt->execute([$sectionId]);
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
    $stmt = $pdo->prepare("SELECT 
        c.component_id AS componentId,
        c.component_name AS componentName,
        c.max_mark AS maxMark,
        s.section_id AS sectionId,
        s.section_number AS sectionNumber,
        cr.course_id AS courseId,
        cr.course_code AS courseCode,
        cr.course_name AS courseName
        FROM components c
        JOIN sections s ON c.section_id = s.section_id
        JOIN courses cr ON s.course_id = cr.course_id
        WHERE c.component_id = ?
        ");
    $stmt->execute([$args['id']]);
    $component = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$component) {
        $response->getBody()->write(json_encode(['error' => 'Component not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode($component));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/components/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $data = $request->getParsedBody();
    $componentId = $args['id'];
    $componentName = $data['componentName'];
    $maxMark = $data['maxMark'];

    if (!isset($componentName, $maxMark)) {
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("UPDATE components SET component_name = ?, max_mark = ? WHERE component_id = ?");
    $stmt->execute([$componentName, $maxMark, $componentId]);

    $response->getBody()->write(json_encode(['message' => 'Component updated']));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->delete('/components/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $componentId = $args['id'];

    // Optional: delete related marks first
    $pdo->prepare("DELETE FROM marks WHERE component_id = ?")->execute([$componentId]);

    $stmt = $pdo->prepare("DELETE FROM components WHERE component_id = ?");
    $stmt->execute([$componentId]);

    $response->getBody()->write(json_encode(['message' => 'Component deleted']));
    return $response->withHeader('Content-Type', 'application/json');
});
