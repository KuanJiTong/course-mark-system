<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/all_marks', function (Request $request, Response $response) {
    $pdo = getPDO();

    $queryParams = $request->getQueryParams();
    $course_id = $queryParams['course_id'] ?? null;
    $section_id = $queryParams['section_id'] ?? null;

    if (!$course_id || !$section_id) {
        $error = ['error' => 'Missing course_id or section_id'];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("
        SELECT
            s.student_id,
            u.name AS student_name,
            COALESCE(SUM(m.mark), 0) AS coursework_mark,
            COALESCE(f.mark, 0) AS final_exam_mark,
            COALESCE(SUM(m.mark), 0) + COALESCE(f.mark, 0) AS total_mark
        FROM students s
        JOIN users u ON s.user_id = u.user_id
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
        GROUP BY s.student_id, u.name, f.mark
        ORDER BY s.student_id
    ");

    $stmt->execute([
        $course_id, $section_id,  // marks join
        $course_id, $section_id,  // final_exam join
        $course_id, $section_id,  // IN (...) subquery for marks
        $course_id, $section_id   // IN (...) subquery for final_exam
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader('Content-Type', 'application/json');
});
