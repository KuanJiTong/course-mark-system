<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET sections 
$app->get('/section', function ($request, $response) {
    try {
        $pdo = getPDO();

        // Get course_id from query parameters (optional)
        $params = $request->getQueryParams();
        $courseId = $params['course_id'] ?? null;

        // Build SQL query with optional course_id filter
        $sql = "
            SELECT 
                s.section_id AS sectionId,
                s.section_number AS sectionNumber,
                s.capacity,
                s.lecturer_id AS lecturerId,
                COUNT(e.enrollment_id) AS studentCount,
                CONCAT(l.title, ' ', u.name) AS lecturerName
            FROM sections s
            LEFT JOIN lecturers l ON s.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON u.user_id = l.user_id
            LEFT JOIN enrollment e ON s.section_id = e.section_id
            WHERE s.course_id = ?
            GROUP BY s.section_id, s.section_number, s.capacity, s.lecturer_id, l.title, u.name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courseId]);

        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($sections));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

//POST section
$app->post('/section', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);

    $courseId = $data['courseId'] ?? null;
    $sectionNumber = $data['sectionNumber'] ?? null;
    $capacity = $data['capacity'] ?? null;

    if (!$courseId || !$sectionNumber || $capacity === null) {
        $response->getBody()->write(json_encode(["error" => "Missing required fields"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare("INSERT INTO sections (course_id, section_number, capacity) VALUES (?, ?, ?)");
    $stmt->execute([$courseId, $sectionNumber, $capacity]);

    $sectionId = $pdo->lastInsertId();

    $response->getBody()->write(json_encode([
        "message" => "Section created successfully",
        "section_id" => $sectionId
    ]));

    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

//PATCH section
$app->patch('/section/{id}', function ($request, $response, $args) {
    $pdo = getPDO();
    $sectionId = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    if (!$data) {
        $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $data = camelToSnake($data); // Converts sectionNumber → section_number

    if (empty($data)) {
        $response->getBody()->write(json_encode(['error' => 'No data to update']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $fields = [];
    $values = [];

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }

    $values[] = $sectionId;
    $sql = "UPDATE sections SET " . implode(', ', $fields) . " WHERE section_id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($values)) {
        $response->getBody()->write(json_encode(['message' => 'Section updated']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Failed to update']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// DELETE section
$app->delete('/section/{id}', function (Request $request, Response $response, array $args) {
    $pdo = getPDO();
    $sectionId = (int)$args['id'];

    // Optional: check if section exists before deleting
    $stmtCheck = $pdo->prepare("SELECT section_id FROM sections WHERE section_id = ?");
    $stmtCheck->execute([$sectionId]);
    if (!$stmtCheck->fetch()) {
        $response->getBody()->write(json_encode(["error" => "Section not found"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Delete section
    $stmt = $pdo->prepare("DELETE FROM sections WHERE section_id = ?");
    $stmt->execute([$sectionId]);

    $response->getBody()->write(json_encode(["message" => "Section deleted successfully"]));
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});

