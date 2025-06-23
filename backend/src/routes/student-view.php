<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET: Fetch marks for a specific student in a course and section
$app->get('/student/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['student_id']) || !isset($params['course_id']) || !isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id, course_id, or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare('
        SELECT m.mark_id, m.student_id, m.component_id, m.mark, c.component_name, c.max_mark
        FROM marks m
        JOIN components c ON m.component_id = c.component_id
        WHERE c.course_id = ? AND c.section_id = ? AND m.student_id = ?
    ');
    $stmt->execute([$params['course_id'], $params['section_id'], $params['student_id']]);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: Student rank and percentile in a section
$app->get('/student/rank', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['student_id']) || !isset($params['course_id']) || !isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id, course_id, or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $course_id = $params['course_id'];
    $section_id = $params['section_id'];
    $student_id = $params['student_id'];

    // Get all students' total marks in the section
    $stmt = $pdo->prepare("
        SELECT
            s.student_id,
            COALESCE(SUM(m.mark), 0) AS coursework_mark,
            COALESCE(f.mark, 0) AS final_exam_mark,
            COALESCE(SUM(m.mark), 0) + COALESCE(f.mark, 0) AS total_mark
        FROM students s
        LEFT JOIN marks m ON s.student_id = m.student_id
            AND m.component_id IN (
                SELECT component_id
                FROM components
                WHERE course_id = ? AND section_id = ? AND component_name != 'Final Exam'
            )
        LEFT JOIN final_exam f ON s.student_id = f.student_id
            AND f.course_id = ? AND f.section_id = ?
        WHERE s.student_id IN (
            SELECT DISTINCT student_id
            FROM marks
            WHERE component_id IN (
                SELECT component_id
                FROM components
                WHERE course_id = ? AND section_id = ?
            )
            UNION
            SELECT student_id
            FROM final_exam
            WHERE course_id = ? AND section_id = ?
        )
        GROUP BY s.student_id, f.mark
        ORDER BY total_mark DESC
    ");
    $stmt->execute([
        $course_id, $section_id,
        $course_id, $section_id,
        $course_id, $section_id,
        $course_id, $section_id
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Find the student's rank and percentile
    $rank = null;
    $percentile = null;
    $total_students = count($results);
    foreach ($results as $i => $row) {
        if ($row['student_id'] == $student_id) {
            $rank = $i + 1;
            $percentile = $total_students > 1 ? round(100 * ($total_students - $rank) / ($total_students - 1), 2) : 100;
            break;
        }
    }

    $response->getBody()->write(json_encode([
        'rank' => $rank,
        'total_students' => $total_students,
        'percentile' => $percentile
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST: Submit a remark request
$app->post('/student/remark-request', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    if (!isset($data['student_id'], $data['course_id'], $data['section_id'], $data['justification'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $component_id = $data['component_id'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO remark_requests (student_id, course_id, section_id, component_id, justification) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['student_id'],
        $data['course_id'],
        $data['section_id'],
        $component_id,
        $data['justification']
    ]);

    $response->getBody()->write(json_encode(['message' => 'Remark request submitted']));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: List student's own remark requests
$app->get('/student/remark-requests', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['student_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT rr.*, c.component_name
        FROM remark_requests rr
        LEFT JOIN components c ON rr.component_id = c.component_id
        WHERE rr.student_id = ?
        ORDER BY rr.created_at DESC
    ");
    $stmt->execute([$params['student_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($requests));
    return $response->withHeader('Content-Type', 'application/json');
}); 

// GET: Class average per component for a course and section
$app->get('/class/component-averages', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['course_id']) || !isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing course_id or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT
            c.component_id,
            c.component_name,
            c.max_mark,
            AVG(m.mark) AS average_mark
        FROM components c
        LEFT JOIN marks m ON c.component_id = m.component_id
        WHERE c.course_id = ? AND c.section_id = ?
        GROUP BY c.component_id, c.component_name, c.max_mark
        ORDER BY c.component_id
    ");
    $stmt->execute([$params['course_id'], $params['section_id']]);
    $averages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($averages));
    return $response->withHeader('Content-Type', 'application/json');
});