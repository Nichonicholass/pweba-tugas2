<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $image = $_FILES['profile_image'];

    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $imagePath = '';
    if ($image['error'] == UPLOAD_ERR_OK) {
        $imagePath = 'uploads/' . basename($image['name']);
        if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
            echo 'Failed to upload image';
            exit;
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username AND password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch();

    if ($user) {
        if ($imagePath) {
            $stmt = $pdo->prepare("UPDATE user SET profile_image = :image WHERE id = :id");
            $stmt->execute(['image' => $imagePath, 'id' => $user['id']]);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_image'] = $imagePath ? $imagePath : $user['profile_image'];

        header('Location: index.php');
        exit;
    } else {
        echo 'Invalid username or password';
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2 class="title">Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST" class="login-form" enctype="multipart/form-data">
            <h3>Username</h3>
            <input type="text" name="username" placeholder="Username" required class="login-input">
            <h3>Password</h3>
            <input type="password" name="password" placeholder="Password" required class="login-input">
            <h3>Profile Image</h3>
            <input type="file" name="profile_image" class="login-input">
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>
