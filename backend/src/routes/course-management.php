<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//POST course
$app->post('/course', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);

    $courseCode = $data['courseCode'] ?? null;
    $courseName = $data['courseName'] ?? null;
    $numOfSections = $data['numOfSections'] ?? null;
    $capacity = $data['capacity'] ?? null;
    $faculty = $data['faculty'] ?? null;
    $credit = $data['credit'] ?? null;

    if (!$courseCode || !$courseName || $numOfSections === null || $capacity === null || $faculty === null || $credit === null) {
        $response->getBody()->write(json_encode(["error" => "Missing required fields"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, credit, faculty_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$courseCode, $courseName, $credit, $faculty]);

    $courseId = $pdo->lastInsertId();

    $stmtSection = $pdo->prepare("INSERT INTO sections (course_id, section_number, capacity) VALUES (?, ?, ?)");
    for ($i = 1; $i <= $numOfSections; $i++) {
        $sectionNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
        $stmtSection->execute([$courseId, $sectionNumber, $capacity]);
    }

    $response->getBody()->write(json_encode([
        "message" => "Course and sections created successfully",
        "course_id" => $courseId
    ]));

    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// GET courses / Search
$app->get('/course', function ($request, $response) {
    try {
        $pdo = getPDO();

        // Get query params
        $params = $request->getQueryParams();
        $facultyId = $params['faculty_id'] ?? null;
        $keyword = $params['keyword'] ?? null;

        if (!$facultyId) {
            $response->getBody()->write(json_encode(["error" => "Missing faculty_id"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Base SQL
        $sql = "
            SELECT 
                c.course_id AS courseId, 
                c.course_code AS courseCode,
                c.course_name AS courseName,
                c.credit,
                c.max_fm AS maxFm,
                c.max_cm AS maxCm,
                COUNT(s.section_id) AS numOfSections
            FROM courses c 
            LEFT JOIN sections s ON c.course_id = s.course_id 
            WHERE c.faculty_id = ?
        ";

        $paramsToBind = [$facultyId];

        // If keyword exists, add search filter
        if ($keyword) {
            $sql .= " AND (c.course_code LIKE ? OR c.course_name LIKE ?)";
            $likeKeyword = "%$keyword%";
            $paramsToBind[] = $likeKeyword;
            $paramsToBind[] = $likeKeyword;
        }

        $sql .= " GROUP BY c.course_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsToBind);

        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// GET course
$app->get('/course/{course_id}', function ($request, $response, $args) {
    $pdo = getPDO();

    $courseId = (int) $args['course_id'];

    $stmt = $pdo->prepare("SELECT course_code AS courseCode, course_name AS courseName, faculty_id AS facultyId FROM courses WHERE course_id = :courseId");
    $stmt->execute(['courseId' => $courseId]);
    $course = $stmt->fetch();

    if ($course) {
        $response->getBody()->write(json_encode($course));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $error = ['error' => 'Course not found'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
});

//PATCH  course
$app->patch('/course/{id}', function ($request, $response, $args) {
    $pdo = getPDO();
    $courseId = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    if (!$data) {
        $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $data = camelToSnake($data);

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

    $values[] = $courseId;
    $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE course_id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($values)) {
        $response->getBody()->write(json_encode(['message' => 'Course updated']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Failed to update']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


