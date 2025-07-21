<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET: List advisees for a given advisor
$app->get('/advisor/advisees', function ($request, $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['advisor_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing advisor_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT s.student_id, u.name AS student_name, s.matric_no
        FROM advisor_advisee aa
        JOIN students s ON aa.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        WHERE aa.advisor_id = ?
        ORDER BY u.name
    ");
    $stmt->execute([$params['advisor_id']]);
    $advisees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($advisees));
    return $response->withHeader('Content-Type', 'application/json');
}); 

// GET: List all courses/sections a student is enrolled in
$app->get('/student/enrollments', function ($request, $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['student_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing student_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT
            c.course_id,
            c.course_code,
            c.course_name,
            s.section_id,
            s.section_number
        FROM enrollment e
        JOIN sections s ON e.section_id = s.section_id
        JOIN courses c ON s.course_id = c.course_id
        WHERE e.student_id = ?
        ORDER BY c.course_name, s.section_number
    ");
    $stmt->execute([$params['student_id']]);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($enrollments));
    return $response->withHeader('Content-Type', 'application/json');
}); 

// GET: Section marks with student names and advisee flag
$app->get('/advisor/section-marks', function ($request, $response) {
    try {
        $pdo = getPDO();
        $params = $request->getQueryParams();
        $advisor_id = $params['advisor_id'] ?? null;
        $course_id = $params['course_id'] ?? null;
        $section_id = $params['section_id'] ?? null;

        if (!$advisor_id || !$course_id || !$section_id) {
            $response->getBody()->write(json_encode(['error' => 'Missing advisor_id, course_id, or section_id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Get all advisee student_ids for this advisor
        $stmt = $pdo->prepare("SELECT student_id FROM advisor_advisee WHERE advisor_id = ?");
        $stmt->execute([$advisor_id]);
        $advisee_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'student_id');

        // Get all students enrolled in the section, with their marks (even if none)
        $stmt = $pdo->prepare("
            SELECT
                s.student_id,
                u.name AS student_name,
                COALESCE(SUM(m.mark), 0) AS coursework_mark,
                COALESCE(f.mark, 0) AS final_exam_mark,
                COALESCE(SUM(m.mark), 0) + COALESCE(f.mark, 0) AS total_mark
            FROM enrollment e
            JOIN students s ON e.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            JOIN sections sec ON e.section_id = sec.section_id
            LEFT JOIN marks m ON s.student_id = m.student_id
                AND m.component_id IN (
                    SELECT component_id
                    FROM components
                    WHERE course_id = ? AND section_id = ? AND component_name != 'Final Exam'
                )
            LEFT JOIN final_exam f ON s.student_id = f.student_id
                AND f.section_id = ?
            WHERE e.section_id = ? AND sec.course_id = ?
            GROUP BY s.student_id, u.name, f.mark
            ORDER BY s.student_id
        ");
        $stmt->execute([
            $course_id, $section_id,
            $section_id,
            $section_id, $course_id
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all components for this section
        $compStmt = $pdo->prepare("SELECT component_id, component_name FROM components WHERE section_id = ?");      
        $compStmt->execute([$section_id]);
        $components = $compStmt->fetchAll(PDO::FETCH_ASSOC);

        // For each student, build a marks object: {component_name: mark}
        foreach ($results as &$row) {
            $marksObj = [];
            foreach ($components as $comp) {
                if ($comp['component_name'] === 'Final Exam') {
                    // Fetch from final_exam table
                    $feStmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND section_id = ?");
                    $feStmt->execute([$row['student_id'], $section_id]);
                    $feRow = $feStmt->fetch(PDO::FETCH_ASSOC);
                    $marksObj[$comp['component_name']] = $feRow ? floatval($feRow['mark']) : 0;
                } else {
                    $markStmt = $pdo->prepare("SELECT mark FROM marks WHERE student_id = ? AND component_id = ?");
                    $markStmt->execute([$row['student_id'], $comp['component_id']]);
                    $markRow = $markStmt->fetch(PDO::FETCH_ASSOC);
                    $marksObj[$comp['component_name']] = $markRow ? floatval($markRow['mark']) : 0;
                }
            }
            $row['marks'] = $marksObj;
            $row['is_advisee'] = in_array($row['student_id'], $advisee_ids);
        }

        $response->getBody()->write(json_encode($results));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}); 

$app->get('/advisor/component-averages', function ($request, $response) {
    try {
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
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}); 

