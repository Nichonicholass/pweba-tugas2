<?php

$host = "localhost";
$port = "8111";
$username = "root";
$password = "";
$database = "todolist";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $database: " . $e->getMessage());
}

function getAllTasks($pdo) {
    $stmt = $pdo->query("SELECT * FROM tasks");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserByUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
    $stmt->execute(['username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addUser($pdo, $username, $password, $profile_image) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO user (username, password, profile_image) VALUES (:username, :password, :profile_image)");
    $stmt->execute(['username' => $username, 'password' => $hashedPassword, 'profile_image' => $profile_image]);
}

function userExists($pdo, $username) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username");
    $stmt->execute(['username' => $username]);
    return $stmt->fetchColumn() > 0;
}

?>
