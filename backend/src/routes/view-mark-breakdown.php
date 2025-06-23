<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/all_marks', function (Request $request, Response $response) {
    $pdo = getPDO();

    $course_id = $request->getQueryParams()['course_id'] ?? null;
    $section_id = $request->getQueryParams()['section_id'] ?? null;

    if (!$course_id || !$section_id) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Get all components for this course/section (except final exam)
    $componentsStmt = $pdo->prepare("SELECT component_id, component_name FROM components WHERE course_id = ? AND section_id = ? AND component_name != 'Final Exam'");
    $componentsStmt->execute([$course_id, $section_id]);
    $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all students who have any marks or final exam in this section
    $studentsStmt = $pdo->prepare("
        SELECT s.student_id, u.name AS student_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id IN (
            SELECT student_id FROM marks WHERE component_id IN (
                SELECT component_id FROM components WHERE course_id = ? AND section_id = ?
            )
            UNION
            SELECT student_id FROM final_exam WHERE course_id = ? AND section_id = ?
        )
    ");
    $studentsStmt->execute([$course_id, $section_id, $course_id, $section_id]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare component marks
    $results = [];

    foreach ($students as $student) {
        $studentId = $student['student_id'];

        // Get all component marks for student
        $marksStmt = $pdo->prepare("
            SELECT c.component_name, m.mark
            FROM marks m
            JOIN components c ON m.component_id = c.component_id
            WHERE m.student_id = ? AND c.course_id = ? AND c.section_id = ? AND c.component_name != 'Final Exam'
        ");
        $marksStmt->execute([$studentId, $course_id, $section_id]);
        $marks = $marksStmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['quiz' => 5, 'assignment' => 10, ...]

        // Get final exam mark
        $finalExamStmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND course_id = ? AND section_id = ?");
        $finalExamStmt->execute([$studentId, $course_id, $section_id]);
        $finalExamMark = $finalExamStmt->fetchColumn();
        if ($finalExamMark === false) {
            $finalExamMark = 0.00;
        }

        // Sum total marks
        $total = array_sum($marks) + $finalExamMark;

        $results[] = [
            'student_id' => $studentId,
            'student_name' => $student['student_name'],
            'marks' => $marks,
            'final_exam_mark' => $finalExamMark,
            'total' => $total
        ];
    }

    $response->getBody()->write(json_encode([
        'components' => array_column($components, 'component_name'),
        'data' => $results
    ]));
    
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/all_marks_csv', function (Request $request, Response $response) {
    $pdo = getPDO();

    $course_id = $request->getQueryParams()['course_id'] ?? null;
    $section_id = $request->getQueryParams()['section_id'] ?? null;

    if (!$course_id || !$section_id) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Get components
    $componentsStmt = $pdo->prepare("SELECT component_id, component_name FROM components WHERE course_id = ? AND section_id = ? AND component_name != 'Final Exam'");
    $componentsStmt->execute([$course_id, $section_id]);
    $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);
    $componentNames = array_column($components, 'component_name');

    // Get students
    $studentsStmt = $pdo->prepare("
        SELECT s.student_id, u.name AS student_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id IN (
            SELECT student_id FROM marks WHERE component_id IN (
                SELECT component_id FROM components WHERE course_id = ? AND section_id = ?
            )
            UNION
            SELECT student_id FROM final_exam WHERE course_id = ? AND section_id = ?
        )
    ");
    $studentsStmt->execute([$course_id, $section_id, $course_id, $section_id]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    $csv = fopen('php://temp', 'r+');
    fputcsv($csv, array_merge(['Student ID', 'Student Name'], $componentNames, ['Final Exam', 'Total']));

    foreach ($students as $student) {
        $studentId = $student['student_id'];

        // Fetch marks for components
        $marksStmt = $pdo->prepare("
            SELECT c.component_name, m.mark
            FROM marks m
            JOIN components c ON m.component_id = c.component_id
            WHERE m.student_id = ? AND c.course_id = ? AND c.section_id = ? AND c.component_name != 'Final Exam'
        ");
        $marksStmt->execute([$studentId, $course_id, $section_id]);
        $componentMarks = $marksStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch final exam
        $finalStmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND course_id = ? AND section_id = ?");
        $finalStmt->execute([$studentId, $course_id, $section_id]);
        $finalExamMark = $finalStmt->fetchColumn();
        $finalExamMark = ($finalExamMark === false || $finalExamMark === null) ? 0.00 : (float)$finalExamMark;

        // Build row: ID, Name, Component Marks..., Final Exam, Total
        $row = [
            $student['student_id'],
            $student['student_name'],
        ];

        $total = 0;
        foreach ($componentNames as $comp) {
            $mark = $componentMarks[$comp] ?? 0;
            $row[] = $mark;
            $total += $mark;
        }

        $row[] = $finalExamMark;
        $row[] = $total + $finalExamMark;

        fputcsv($csv, $row);
    }

    rewind($csv);
    $csvContent = stream_get_contents($csv);
    fclose($csv);

    $response->getBody()->write($csvContent);
    return $response
        ->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', 'attachment; filename="marks_section_' . $section_id . '.csv"');
});

