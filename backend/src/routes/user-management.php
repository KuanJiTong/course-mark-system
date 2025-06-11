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

// GET users
$app->get('/users', function ($request, $response) {
    try {
        $pdo = getPDO();

        $sql = "SELECT
                    u.user_id AS userId,
                    u.login_id AS loginId,
                    u.name,
                    u.email,
                    r.role_name AS roleName,
                    f.faculty_abbreviation AS facultyAbbreviation
                FROM
                    users u
                JOIN
                    user_roles ur ON u.user_id = ur.user_id
                JOIN
                    roles r ON ur.role_id = r.role_id
                JOIN
                    faculty f ON u.faculty_id = f.faculty_id;
                ";
        
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($rows as $row) {
            $userId = $row['userId'];

            // Initialize user if not already in the array
            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'userId' => $row['userId'],
                    'loginId' => $row['loginId'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'facultyAbbreviation' => $row['facultyAbbreviation'],
                    'roleNames' => []
                ];
            }

            // Add roleName to the user's roleNames array
            $users[$userId]['roleNames'][] = $row['roleName'];
        }

        // Re-index the array if you want a plain numeric index
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






