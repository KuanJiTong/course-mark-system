<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET /enrolled-courses?student_id=123
$app->get('/enrolled-courses', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $studentId = $params['student_id'];
    if (!isset($studentId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare('
        SELECT DISTINCT c.course_id AS courseId, 
        c.course_code AS courseCode, 
        c.course_name AS courseName,
        s.section_id AS sectionId,
        s.section_number AS sectionNumber
        FROM enrollment e
        JOIN sections s ON e.section_id = s.section_id
        JOIN courses c ON s.course_id = c.course_id
        WHERE e.student_id = ?
    ');
    $stmt->execute([$studentId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


// GET: Fetch marks for a specific student in a course and section
$app->get('/student/marks', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $studentId = $params['student_id'];
    $sectionId = $params['section_id'];

    if (!isset($studentId) || !isset($sectionId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

   // Check if student is enrolled in the section (simpler, more reliable)
    $enrollStmt = $pdo->prepare('SELECT 1 FROM enrollment WHERE student_id = ? AND section_id = ?');
    $enrollStmt->execute([$params['student_id'], $params['section_id']]);
    if (!$enrollStmt->fetch()) {
        $response->getBody()->write(json_encode(['error' => 'Student is not enrolled in this course section']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $stmt = $pdo->prepare('
        SELECT m.mark_id AS markId, 
        m.component_id AS componentId,
        m.mark, 
        c.component_name AS componentName, 
        c.max_mark AS maxMark
        FROM marks m
        JOIN components c ON m.component_id = c.component_id
        WHERE c.section_id = ? AND m.student_id = ?
    ');
    $stmt->execute([ $sectionId, $studentId]);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch final exam mark
    $stmt2 = $pdo->prepare('
        SELECT 
            IFNULL(f.mark, 0) AS mark,
            c.max_fm AS maxFm
        FROM 
            sections s
        JOIN 
            courses c ON s.course_id = c.course_id
        LEFT JOIN 
            final_exam f ON f.section_id = s.section_id AND f.student_id = ?
        WHERE 
            s.section_id = ?;
    ');
    $stmt2->execute([$studentId, $sectionId]);
    $finalExam = $stmt2->fetch(PDO::FETCH_ASSOC);
    $finalExam['mark'] = floatval($finalExam['mark']);

    // Calculate total mark (sum of all component marks + final exam)
    $total = 0;
    foreach ($marks as $m) {
        $total += floatval($m['mark']);
    }
    if ($finalExam !== null) {
        $total += $finalExam['mark'];
    }

    $response->getBody()->write(json_encode([
        'marks' => $marks,
        'finalExam' => $finalExam,
        'totalMark' => $total
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: Student rank and percentile in a section
$app->get('/student/rank', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $studentId = $params['student_id'];
    $sectionId = $params['section_id'];

    if (!isset($studentId) || !isset($sectionId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id or section_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Get all students' total marks in the section
    $stmt = $pdo->prepare("
        SELECT
            s.student_id AS studentId,
            COALESCE(SUM(m.mark), 0) AS coursework_mark,
            COALESCE(f.mark, 0) AS final_exam_mark,
            COALESCE(SUM(m.mark), 0) + COALESCE(f.mark, 0) AS total_mark
        FROM students s
        LEFT JOIN marks m ON s.student_id = m.student_id
            AND m.component_id IN (
                SELECT component_id
                FROM components
                WHERE section_id = ?
            )
        LEFT JOIN final_exam f ON s.student_id = f.student_id
            AND f.section_id = ?
        WHERE s.student_id IN (
            SELECT DISTINCT student_id
            FROM marks
            WHERE component_id IN (
                SELECT component_id
                FROM components
                WHERE section_id = ?
            )
            UNION
            SELECT student_id
            FROM final_exam
            WHERE section_id = ?
        )
        GROUP BY s.student_id, f.mark
        ORDER BY total_mark DESC
    ");
    $stmt->execute([
        $sectionId,
        $sectionId,
        $sectionId,
        $sectionId
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Find the student's rank and percentile
    $rank = null;
    $percentile = null;
    $totalStudents = count($results);
    foreach ($results as $i => $row) {
        if ($row['studentId'] == $studentId) {
            $rank = $i + 1;
            $percentile = $totalStudents > 1 ? round(100 * ($totalStudents - $rank) / ($totalStudents - 1), 2) : 100;
            break;
        }
    }

    $response->getBody()->write(json_encode([
        'rank' => $rank,
        'totalStudents' => $totalStudents,
        'percentile' => $percentile
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST: Submit a remark request
$app->post('/student/remark-request', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();
    $studentId = $data['studentId']; 
    $sectionId = $data['sectionId'];
    $justification = $data['justification'];

    if (!isset($studentId,$sectionId,$justification)) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $componentId = $data['componentId'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO remark_requests (student_id, section_id, component_id, justification) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $studentId,
        $sectionId,
        $componentId,
        $justification
    ]);

    $response->getBody()->write(json_encode(['message' => 'Remark request submitted']));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET: List student's own remark requests
$app->get('/student/remark-requests', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $studentId = $params['student_id'];

    if (!isset($studentId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
       SELECT 
        rr.created_at AS createdAt,
        rr.request_id AS requestId,
        rr.justification AS justification,
        rr.status,
        c.component_name AS componentName, 
        s.section_number AS sectionNumber, 
        crs.course_code AS courseCode, 
        crs.course_name AS courseName
        FROM remark_requests rr
        LEFT JOIN components c ON rr.component_id = c.component_id
        JOIN sections s ON rr.section_id = s.section_id
        JOIN courses crs ON s.course_id = crs.course_id
        WHERE rr.student_id = ?
        ORDER BY rr.created_at DESC;
    ");
    $stmt->execute([$studentId]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($requests));
    return $response->withHeader('Content-Type', 'application/json');
}); 

// GET: Class average per component for a course and section
$app->get('/class/component-averages', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing section_id']));
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
        WHERE c.section_id = ?
        GROUP BY c.component_id, c.component_name, c.max_mark
        ORDER BY c.component_id
    ");
    $stmt->execute([$params['section_id']]);
    $averages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($averages));
    return $response->withHeader('Content-Type', 'application/json');
});