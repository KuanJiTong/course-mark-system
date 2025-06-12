<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// GET role 
$app->get('/role', function ($request, $response) {
    try {
        $pdo = getPDO();

        $sql = "SELECT role_id AS roleId, role_name AS roleName FROM roles ORDER BY role_id ASC";
        
        $stmt = $pdo->query($sql);

        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($roles));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// GET faculty
$app->get('/faculty', function ($request, $response) {
    try {
        $pdo = getPDO();

        $sql = "SELECT faculty_id AS facultyId, faculty_abbreviation AS facultyAbbreviation, faculty_name AS facultyName FROM faculty";
        
        $stmt = $pdo->query($sql);

        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($faculty));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// GET users with optional keyword search
$app->get('/users', function ($request, $response) {
    try {
        $pdo = getPDO();
        $queryParams = $request->getQueryParams();
        $keyword = isset($queryParams['keyword']) ? '%' . $queryParams['keyword'] . '%' : null;

        $sql = "SELECT
                    u.user_id AS userId,
                    u.login_id AS loginId,
                    u.title,
                    u.name,
                    u.email,
                    r.role_id AS roleId,
                    r.role_name AS roleName,
                    f.faculty_id AS facultyId,
                    f.faculty_abbreviation AS facultyAbbreviation
                FROM
                    users u
                JOIN
                    user_roles ur ON u.user_id = ur.user_id
                JOIN
                    roles r ON ur.role_id = r.role_id
                JOIN
                    faculty f ON u.faculty_id = f.faculty_id";

        if ($keyword) {
            $sql .= " WHERE u.name LIKE :keyword OR u.email LIKE :keyword OR u.login_id LIKE :keyword";
        }

        $stmt = $pdo->prepare($sql);

        if ($keyword) {
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($rows as $row) {
            $userId = $row['userId'];

            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'userId' => $row['userId'],
                    'loginId' => $row['loginId'],
                    'name' => $row['name'],
                    'title' => $row['title'],
                    'email' => $row['email'],
                    'facultyId' => $row['facultyId'],
                    'facultyAbbreviation' => $row['facultyAbbreviation'],
                    'roleIds' => [],
                    'roleNames' => []
                ];
            }
            $users[$userId]['roleIds'][] = $row['roleId'];
            $users[$userId]['roleNames'][] = $row['roleName'];
        }

        $users = array_values($users);

        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


//POST user
$app->post('/user', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);

    // Extract and validate input
    $loginId    = $data['loginId'] ?? null;
    $title      = $data['title'] ?? '';
    $name       = $data['name'] ?? null;
    $email      = $data['email'] ?? null;
    $password   = $data['password'] ?? null;
    $facultyId  = $data['facultyId'] ?? null;
    $createdAt  = $data['createdAt'] ?? null;
    $roleIds    = $data['roleIds'] ?? [];

    if (!$loginId || !$name || !$email || !$password || !$createdAt || $facultyId === null || !is_array($roleIds)) {
        $response->getBody()->write(json_encode(["error" => "Missing required fields"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $pdo->beginTransaction();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (login_id, title, name, email, password, faculty_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$loginId, $title, $name, $email, $hashedPassword, $facultyId, $createdAt]);

        $userId = $pdo->lastInsertId();

        // Insert user roles
        $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($roleIds as $roleId) {
            $stmtRole->execute([$userId, $roleId]);
        }

        $pdo->commit();

        $response->getBody()->write(json_encode([
            "message" => "User created successfully",
            "user_id" => $userId
        ]));

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode([
            "error" => "Failed to create user",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// GET lecturers by faculty with optional search
$app->get('/lecturers/{facultyId}', function ($request, $response, $args) {
    try {
        $pdo = getPDO();
        $facultyId = (int)$args['facultyId'];
        $queryParams = $request->getQueryParams();
        $keyword = isset($queryParams['keyword']) ? '%' . $queryParams['keyword'] . '%' : null;

        $sql = "SELECT
                    u.user_id AS lecturerId,
                    CONCAT(u.title, ' ', u.name) AS lecturerName,
                    u.email
                FROM
                    users u
                JOIN
                    user_roles ur ON u.user_id = ur.user_id
                JOIN
                    roles r ON ur.role_id = r.role_id
                JOIN
                    faculty f ON u.faculty_id = f.faculty_id
                WHERE 
                    r.role_name = 'Lecturer' 
                    AND f.faculty_id = ?
        ";

        // Add search filter if provided
        if ($keyword) {
            $sql .= " AND (CONCAT(u.title, ' ', u.name) LIKE ? OR u.email LIKE ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$facultyId, $keyword, $keyword]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$facultyId]);
        }

        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($lecturers));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// PATCH user
$app->patch('/user/{id}', function ($request, $response, $args) {
    $pdo = getPDO();
    $userId = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    if (!$data) {
        $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $data = camelToSnakeRecursive($data); 

    if (empty($data)) {
        $response->getBody()->write(json_encode(['error' => 'No data to update']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $pdo->beginTransaction();

        $fields = [];
        $values = [];

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $allowed = ['login_id', 'name', 'title', 'email', 'faculty_id', 'password'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $values[] = $key === 'password' ? password_hash($value, PASSWORD_DEFAULT) : $value;
            }
        }

        if (!empty($fields)) {
            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
        }

        // Update roles if role_ids is present
        if (isset($data['role_ids']) && is_array($data['role_ids'])) {
            $stmtDel = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
            $stmtDel->execute([$userId]);

            $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            foreach ($data['role_ids'] as $roleId) {
                $stmtRole->execute([$userId, $roleId]);
            }
        }

        $pdo->commit();
        $response->getBody()->write(json_encode(['message' => 'User updated']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(['error' => 'Failed to update', 'details' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


// DELETE user
$app->delete('/user/{id}', function (Request $request, Response $response, array $args) {
    $pdo = getPDO();
    $userId = (int)$args['id'];

    // Optional: check if user exists before deleting
    $stmtCheck = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmtCheck->execute([$userId]);
    if (!$stmtCheck->fetch()) {
        $response->getBody()->write(json_encode(["error" => "User not found"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    $response->getBody()->write(json_encode(["message" => "User deleted successfully"]));
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});







