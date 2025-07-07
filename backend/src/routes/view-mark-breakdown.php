<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/all_marks', function (Request $request, Response $response) {
    $pdo = getPDO();

    $sectionId = $request->getQueryParams()['section_id'] ?? null;

    if (!$sectionId) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Get all components for this course/section (except final exam)
    $componentsStmt = $pdo->prepare("SELECT component_id AS componentId, component_name AS componentName, max_mark AS maxMark FROM components WHERE section_id = ?");
    $componentsStmt->execute([$sectionId]);
    $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

    $maxFinalStmt = $pdo->prepare("
        SELECT IFNULL(c.max_fm, 0) AS maxFm
        FROM sections s
        JOIN courses c ON s.course_id = c.course_id
        WHERE s.section_id = ?
    ");
    $maxFinalStmt->execute([$sectionId]);
    $maxFinalExamMark = $maxFinalStmt->fetchColumn();

    // Get all students who have any marks or final exam in this section
    $studentsStmt = $pdo->prepare("
        SELECT s.student_id AS studentId,
         u.name AS studentName,
         s.matric_no AS matricNo
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id IN (
            SELECT student_id FROM marks WHERE component_id IN (
                SELECT component_id FROM components WHERE section_id = ?
            )
            UNION
            SELECT student_id FROM final_exam WHERE section_id = ?
        )
    ");
    $studentsStmt->execute([$sectionId, $sectionId]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare component marks
    $results = [];

    foreach ($students as $student) {
        $studentId = $student['studentId'];

        // Get all component marks for student
        $marksStmt = $pdo->prepare("
            SELECT c.component_name AS componentName, m.mark
            FROM marks m
            JOIN components c ON m.component_id = c.component_id
            WHERE m.student_id = ? AND c.section_id = ?
        ");
        $marksStmt->execute([$studentId, $sectionId]);
        $marks = $marksStmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['quiz' => 5, 'assignment' => 10, ...]

        // Get final exam mark
        $finalExamStmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND section_id = ?");
        $finalExamStmt->execute([$studentId,$sectionId]);
        $finalExamMark = $finalExamStmt->fetchColumn();
        if ($finalExamMark === false) {
            $finalExamMark = 0.00;
        }

        // Sum total marks
        $total = array_sum($marks) + $finalExamMark;

        $results[] = [
            'studentId' => $studentId,
            'studentName' => $student['studentName'],
            'matricNo' => $student['matricNo'],
            'marks' => $marks,
            'finalExamMark' => $finalExamMark,
            'total' => $total
        ];
    }

    $response->getBody()->write(json_encode([
        'components' => $components,
        'maxFm' => $maxFinalExamMark,
        'data' => $results
    ]));
    
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/all_marks_csv', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $sectionId = $params['section_id'] ?? null;

    if (!$sectionId) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $courseStmt = $pdo->prepare("
        SELECT 
            c.course_code,
            s.section_number
        FROM 
            sections s
        JOIN 
            courses c ON s.course_id = c.course_id
        WHERE 
            s.section_id = ?
    ");
    $courseStmt->execute([$sectionId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    // Get components
    $componentsStmt = $pdo->prepare("SELECT component_id, component_name FROM components WHERE section_id = ?");
    $componentsStmt->execute([$sectionId]);
    $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);
    $componentNames = array_column($components, 'component_name');

    // Get students
    $studentsStmt = $pdo->prepare("
        SELECT s.student_id, u.name AS student_name, s.matric_no
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id IN (
            SELECT student_id FROM marks WHERE component_id IN (
                SELECT component_id FROM components WHERE section_id = ?
            )
            UNION
            SELECT student_id FROM final_exam WHERE section_id = ?
        )
    ");
    $studentsStmt->execute([$sectionId,$sectionId]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    $csv = fopen('php://temp', 'r+');
    fputcsv($csv, array_merge(['Student Name','Matric No'], $componentNames, ['Final Exam', 'Total']));

    foreach ($students as $student) {
        $studentId = $student['student_id'];

        // Fetch marks for components
        $marksStmt = $pdo->prepare("
            SELECT c.component_name, m.mark
            FROM marks m
            JOIN components c ON m.component_id = c.component_id
            WHERE m.student_id = ? AND c.section_id = ?
        ");
        $marksStmt->execute([$studentId, $sectionId]);
        $componentMarks = $marksStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch final exam
        $finalStmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND section_id = ?");
        $finalStmt->execute([$studentId, $sectionId]);
        $finalExamMark = $finalStmt->fetchColumn();
        $finalExamMark = ($finalExamMark === false || $finalExamMark === null) ? 0.00 : (float)$finalExamMark;

        // Build row: ID, Name, Component Marks..., Final Exam, Total
        $row = [
            $student['student_name'],
            $student['matric_no'],
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
    ->withHeader('Content-Type', 'text/csv; charset=utf-8')
    ->withHeader('Content-Disposition', 'attachment; filename="marks_' . $course['course_code'] . '_' . $course['section_number'] . '.csv"');

});

