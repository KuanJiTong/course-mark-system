<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET Students 
$app->get('/students/{sectionId}', function ($request, $response, $args) {
    try {
        $pdo = getPDO();

        $params = $request->getQueryParams();
        $keyword = $params['keyword'] ?? null;
        $sectionId = (int)$args['sectionId'];

        // Base SQL
        $sql = "
            SELECT 
                s.student_id AS studentId,
                s.matric_no AS matricNo,
                u.name AS studentName
            FROM 
                students s
            JOIN users u ON s.user_id = u.user_id
            WHERE NOT EXISTS (
                -- Exclude if enrolled in the same section
                SELECT 1
                FROM enrollment e
                WHERE e.student_id = s.student_id
                AND e.section_id = ?
            )
            AND NOT EXISTS (
                -- Exclude if enrolled in any section of the same course
                SELECT 1
                FROM enrollment e
                JOIN sections sec ON e.section_id = sec.section_id
                WHERE e.student_id = s.student_id
                AND sec.course_id = (
                    SELECT course_id
                    FROM sections
                    WHERE section_id = ?
                    LIMIT 1
                )
            )
        ";

        $paramsToBind = [$sectionId, $sectionId];

        // Add keyword filter if present
        if ($keyword) {
            $sql .= " AND (u.name LIKE ? OR s.matric_no LIKE ?)";
            $likeKeyword = "%$keyword%";
            $paramsToBind[] = $likeKeyword;
            $paramsToBind[] = $likeKeyword;
        }

        // Optional: GROUP BY to ensure uniqueness
        $sql .= " ORDER BY u.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsToBind);

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


//GET section students
$app->get('/student-enrollment/{sectionId}', function ($request, $response,$args) {
    try {
        $pdo = getPDO();

        $sectionId = (int)$args['sectionId'];
        $params = $request->getQueryParams();
        $keyword = $params['keyword'] ?? null;

        // Base SQL
        $sql = "
            SELECT 
                s.student_id AS studentId,
                s.matric_no AS matricNo,
                u.name AS studentName,
                u.email,
                e.enrollment_id AS enrollmentId
            FROM students s
            JOIN users u ON s.user_id = u.user_id 
            JOIN enrollment e ON e.student_id = s.student_id
            JOIN sections sec ON sec.section_id = e.section_id
            WHERE e.section_id = ?
        ";

        $paramsToBind = [$sectionId];

        // If keyword exists, add search filter
        if ($keyword) {
            $sql .= " AND (u.name LIKE ? OR s.matric_no LIKE ? OR u.email LIKE ?)";
            $likeKeyword = "%$keyword%";
            $paramsToBind[] = $likeKeyword;
            $paramsToBind[] = $likeKeyword;
            $paramsToBind[] = $likeKeyword;
        }

        $sql .= " GROUP BY s.student_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsToBind);

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/student-enrollment', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);

    $sectionId = $data['sectionId'] ?? null;
    $enrollList = $data['enrollList'] ?? null;

    if (!$sectionId || !is_array($enrollList)) {
        $response->getBody()->write(json_encode(["error" => "Missing sectionId or invalid enrollList"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare("INSERT INTO enrollment (student_id, section_id) VALUES (?, ?)");

    $inserted = 0;
    foreach ($enrollList as $studentId) {
        if (is_numeric($studentId)) {
            $stmt->execute([$studentId, $sectionId]);
            $inserted++;
        }
    }

    $response->getBody()->write(json_encode([
        "message" => "Enrollments inserted successfully",
        "records_inserted" => $inserted
    ]));

    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// DELETE enrollment
$app->delete('/student-enrollment/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $enrollmentId = (int)$args['id'];

    // Check if the enrollment exists and get the student_id
    $stmtCheck = $pdo->prepare("SELECT student_id FROM enrollment WHERE enrollment_id = ?");
    $stmtCheck->execute([$enrollmentId]);
    $row = $stmtCheck->fetch();

    if (!$row) {
        $response->getBody()->write(json_encode(["error" => "Enrollment not found"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $studentId = (int)$row['student_id'];

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Delete from related tables using student_id
        $pdo->prepare("DELETE FROM marks WHERE student_id = ?")->execute([$studentId]);
        $pdo->prepare("DELETE FROM final_exam WHERE student_id = ?")->execute([$studentId]);
        $pdo->prepare("DELETE FROM remark_requests WHERE student_id = ?")->execute([$studentId]);

        // Finally delete from enrollment
        $pdo->prepare("DELETE FROM enrollment WHERE enrollment_id = ?")->execute([$enrollmentId]);

        $pdo->commit();
        $response->getBody()->write(json_encode(["message" => "Student enrollment and related data deleted successfully."]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(["error" => "Failed to delete data", "details" => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

