<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

$secretKey = 'my-secret-key';

// POST /login
$app->post('/login', function (Request $request, Response $response) use ($secretKey) {
    $data = json_decode($request->getBody()->getContents(), true);
    $userID = $data['userID'] ?? '';
    $password = $data['password'] ?? '';

    if (!$userID || !$password) {
        $response->getBody()->write(json_encode(['error' => 'User ID and password required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT u.*, GROUP_CONCAT(r.role_name) as roles FROM users u
        JOIN user_roles ur ON u.user_id = ur.user_id
        JOIN roles r ON ur.role_id = r.role_id
        WHERE u.user_id = ? GROUP BY u.user_id');
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $issuedAt = time();
        $expire = $issuedAt + 3600;
        $payload = [
            'user_id' => $user['user_id'],
            'login_id' => $user['login_id'],
            'name' => $user['name'],
            'roles' => explode(',', $user['roles']),
            'iat' => $issuedAt,
            'exp' => $expire
        ];
        $token = JWT::encode($payload, $secretKey, 'HS256');
        $response->getBody()->write(json_encode([
            'token' => $token,
            'user' => [
                'user_id' => $user['user_id'],
                'login_id' => $user['login_id'],
                'name' => $user['name'],
                'roles' => explode(',', $user['roles'])
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
}); 