<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/final_exam', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = $request->getParsedBody();

    if (!isset($data['student_id'], $data['course_id'], $data['section_id'], $data['mark'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT * FROM final_exam WHERE student_id = ? AND course_id = ? AND section_id = ?");
    $stmt->execute([$data['student_id'], $data['course_id'], $data['section_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE final_exam SET mark = ? WHERE exam_id = ?");
        $stmt->execute([$data['mark'], $existing['exam_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO final_exam (student_id, course_id, section_id, mark) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['student_id'], $data['course_id'], $data['section_id'], $data['mark']]);
    }

    $response->getBody()->write(json_encode(['message' => 'Final exam mark saved']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/final_exam', function (Request $request, Response $response) {
    $pdo = getPDO();
    $params = $request->getQueryParams();

    if (!isset($params['course_id'], $params['section_id'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT * FROM final_exam WHERE course_id = ? AND section_id = ?");
    $stmt->execute([$params['course_id'], $params['section_id']]);
    $examMarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($examMarks));
    return $response->withHeader('Content-Type', 'application/json');
});
