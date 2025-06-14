<?php
function getPDO() {
    $host = 'localhost';
    $db   = 'cmms'; 	// nama database anda
    $user = 'root1';		//userid
    $pass = '';		//password anda
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die('DB Connection failed: ' . $e->getMessage());
    }
}
