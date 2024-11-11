<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = $_POST['task'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (tasktable, user_id, taskstatus) VALUES (:task, :user_id, 0)");
    $stmt->execute(['task' => $task, 'user_id' => $user_id]);

    header("Location: index.php");
    exit;
}
?>
