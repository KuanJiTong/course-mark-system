<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

$secretKey = 'my-secret-key';

// POST /login
$app->post('/login', function (Request $request, Response $response) use ($secretKey) {
    $data = json_decode($request->getBody()->getContents(), true);
    $loginID = $data['loginID'] ?? '';
    $password = $data['password'] ?? '';

    if (!$loginID || !$password) {
        $response->getBody()->write(json_encode(['error' => 'Login ID and password required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT u.*, GROUP_CONCAT(r.role_name) as roles FROM users u
        JOIN user_roles ur ON u.user_id = ur.user_id
        JOIN roles r ON ur.role_id = r.role_id
        WHERE u.login_id = ? GROUP BY u.login_id');
    $stmt->execute([$loginID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $roles = explode(',', $user['roles']);
        $userId = $user['user_id'];

        $studentId = null;
        $lecturerId = null;

        if (in_array('Student', $roles)) {
            $stmt = $pdo->prepare('SELECT student_id FROM students WHERE user_id = ?');
            $stmt->execute([$userId]);
            $studentId = $stmt->fetchColumn();
        }

        if (in_array('Lecturer', $roles)) {
            $stmt = $pdo->prepare('SELECT lecturer_id FROM lecturers WHERE user_id = ?');
            $stmt->execute([$userId]);
            $lecturerId = $stmt->fetchColumn();
        }

        $issuedAt = time();
        $expire = $issuedAt + 3600;
        $payload = [
            'user_id' => $userId,
            'login_id' => $user['login_id'],
            'name' => $user['name'],
            'roles' => $roles,
            'iat' => $issuedAt,
            'exp' => $expire
        ];
        $token = JWT::encode($payload, $secretKey, 'HS256');

        $response->getBody()->write(json_encode([
            'token' => $token,
            'user' => [
                'user_id' => $userId,
                'login_id' => $user['login_id'],
                'name' => $user['name'],
                'roles' => $roles,
                'studentId' => $studentId,
                'lecturerId' => $lecturerId
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
});
