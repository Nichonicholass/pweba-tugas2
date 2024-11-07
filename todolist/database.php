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

function addUser($pdo, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
    return $stmt->execute(['username' => $username, 'password' => $hashedPassword]);
}

function updateUserProfileImage($pdo, $userId, $imagePath) {
    $stmt = $pdo->prepare("UPDATE users SET profile_image = :image WHERE id = :id");
    return $stmt->execute(['image' => $imagePath, 'id' => $userId]);
}

?>
