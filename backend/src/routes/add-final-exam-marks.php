<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/final_exam', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    $studentId = $data['studentId'];
    $sectionId = $data['sectionId'];
    $mark =  $data['mark'];
    if (!isset($studentId,$sectionId,$mark)) {
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT * FROM final_exam WHERE student_id = ? AND section_id = ?");
    $stmt->execute([$studentId, $sectionId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE final_exam SET mark = ? WHERE exam_id = ?");
        $stmt->execute([$mark, $existing['exam_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO final_exam (student_id, section_id, mark) VALUES (?, ?, ?)");
        $stmt->execute([$studentId,$sectionId,$mark]);
    }

    $response->getBody()->write(json_encode(['message' => 'Final exam mark saved']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/final_exam', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();
    $sectionId =  $params['section_id'];
    if (!isset($sectionId)) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT exam_id AS examId, student_id AS studentId, mark FROM final_exam WHERE section_id = ?");
    $stmt->execute([$sectionId]);
    $examMarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($examMarks));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/final_exam/student', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['student_id'], $params['course_id'], $params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT mark FROM final_exam WHERE student_id = ? AND course_id = ? AND section_id = ?");
    $stmt->execute([$params['student_id'], $params['course_id'], $params['section_id']]);
    $mark = $stmt->fetch(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($mark ?: ['mark' => null]));
    return $response->withHeader('Content-Type', 'application/json');
});
