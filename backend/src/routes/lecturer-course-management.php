<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET lecturer-courses / Search
$app->get('/lecturer-course/{lecturerId}', function ($request, $response,$args) {
    try {
        $pdo = getPDO();

        // Get query params
        $params = $request->getQueryParams();
        $keyword = $params['keyword'] ?? null;

        $lecturerId = (int)$args['lecturerId'];

        if (!$lecturerId) {
            $response->getBody()->write(json_encode(["error" => "Missing lecturer_id"]));
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
                s.section_id AS sectionId,
                s.section_number AS sectionNumber,
                s.capacity,
                COUNT(e.enrollment_id) AS studentCount
            FROM courses c 
            JOIN sections s ON c.course_id = s.course_id 
            LEFT JOIN enrollment e ON s.section_id = e.section_id
            WHERE s.lecturer_id = ?
        ";

        $paramsToBind = [$lecturerId];

        // If keyword exists, add search filter
        if ($keyword) {
            $sql .= " AND (c.course_code LIKE ? OR c.course_name LIKE ?)";
            $likeKeyword = "%$keyword%";
            $paramsToBind[] = $likeKeyword;
            $paramsToBind[] = $likeKeyword;
        }

        $sql .= " GROUP BY c.course_id, s.section_id
";

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