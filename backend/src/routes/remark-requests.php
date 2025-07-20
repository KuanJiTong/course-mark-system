<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET /remark-requests/lecturer/{lecturerId}
// Fetches all remark requests relevant to a specific lecturer
$app->get('/remark-requests/lecturer/{lecturerId}', function (Request $request, Response $response, array $args) {
    $pdo = getPDO();
    $lecturerId = (int)$args['lecturerId'];

    try {
        $sql = "
            SELECT
                rr.request_id,
                rr.student_id,
                u.name AS student_name,
                s.matric_no,
                c.course_code,
                sec.section_number,
                comp.component_name,
                rr.mark,
                rr.justification,
                rr.status
            FROM
                remark_requests rr
            JOIN
                students stu ON rr.student_id = stu.student_id
            JOIN
                users u ON stu.user_id = u.user_id
            JOIN
                sections sec ON rr.section_id = sec.section_id
            JOIN
                courses c ON sec.course_id = c.course_id
            JOIN
                components comp ON rr.component_id = comp.component_id
            JOIN
                lecturer_sections ls ON sec.section_id = ls.section_id
            WHERE
                ls.lecturer_id = :lecturerId
            ORDER BY
                rr.request_id DESC;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':lecturerId', $lecturerId, PDO::PARAM_INT);
        $stmt->execute();

        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($requests));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// PATCH /remark-request/{requestId}
// Updates the status of a specific remark request
$app->patch('/remark-request/{requestId}', function (Request $request, Response $response, array $args) {
    $pdo = getPDO();
    $requestId = (int)$args['requestId'];
    $data = json_decode($request->getBody()->getContents(), true);

    if (!isset($data['status'])) {
        $response->getBody()->write(json_encode(["error" => "Missing 'status' field"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $newStatus = $data['status'];

    // Validate status
    $allowedStatuses = ['Approved', 'Rejected']; // Assuming 'Pending' is the initial status
    if (!in_array($newStatus, $allowedStatuses)) {
        $response->getBody()->write(json_encode(["error" => "Invalid status provided. Allowed: Approved, Rejected"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        // First, check if the request exists and its current status is 'Pending'
        $stmtCheck = $pdo->prepare("SELECT status FROM remark_requests WHERE request_id = :requestId");
        $stmtCheck->bindParam(':requestId', $requestId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $currentStatus = $stmtCheck->fetchColumn();

        if (!$currentStatus) {
            $response->getBody()->write(json_encode(["error" => "Remark request not found."]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        if ($currentStatus !== 'Pending') {
            $response->getBody()->write(json_encode(["error" => "Remark request status cannot be updated from '{$currentStatus}'. Only 'Pending' requests can be updated."]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json'); // 409 Conflict
        }

        // Update the status
        $stmtUpdate = $pdo->prepare("UPDATE remark_requests SET status = :newStatus WHERE request_id = :requestId");
        $stmtUpdate->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':requestId', $requestId, PDO::PARAM_INT);
        $stmtUpdate->execute();

        if ($stmtUpdate->rowCount() > 0) {
            // If approved, you might want to automatically update the student's mark in the marks table
            // This logic is crucial and should be carefully considered based on your mark structure.
            // For now, I'll add a placeholder.
            if ($newStatus === 'Approved') {
                // Fetch details of the approved request to get student_id, component_id, requested_mark
                $stmtFetchDetails = $pdo->prepare("
                    SELECT student_id, component_id, requested_mark
                    FROM remark_requests
                    WHERE request_id = :requestId
                ");
                $stmtFetchDetails->bindParam(':requestId', $requestId, PDO::PARAM_INT);
                $stmtFetchDetails->execute();
                $requestDetails = $stmtFetchDetails->fetch(PDO::FETCH_ASSOC);

                if ($requestDetails) {
                    $studentId = $requestDetails['student_id'];
                    $componentId = $requestDetails['component_id'];
                    $updatedMark = $requestDetails['requested_mark'];

                    // Update the student's mark for the specific component
                    // Assuming 'marks' table has columns like student_id, component_id, mark_obtained
                    $stmtUpdateMark = $pdo->prepare("
                        UPDATE marks
                        SET mark_obtained = :updatedMark
                        WHERE student_id = :studentId AND component_id = :componentId
                    ");
                    $stmtUpdateMark->bindParam(':updatedMark', $updatedMark, PDO::PARAM_STR); // Use STR or INT based on your column type
                    $stmtUpdateMark->bindParam(':studentId', $studentId, PDO::PARAM_INT);
                    $stmtUpdateMark->bindParam(':componentId', $componentId, PDO::PARAM_INT);
                    $stmtUpdateMark->execute();

                    if ($stmtUpdateMark->rowCount() > 0) {
                        // Mark updated successfully
                    } else {
                        // Mark not found or no change
                        error_log("No mark record found or mark not changed for student_id: {$studentId}, component_id: {$componentId}");
                    }
                }
            }


            $response->getBody()->write(json_encode(["message" => "Remark request status updated to {$newStatus}."]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "No changes made or request not found."]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        }

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error during update",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => "Server error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});