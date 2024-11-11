<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $profile_image = 'default.png'; // Default profile image

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $target_file;
        }
    } else {
        $error = 'Gambar profil wajib diunggah.';
    }

    if (!isset($error)) {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            $error = 'Username yang Anda pilih sudah digunakan. Silakan coba Username lain.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO user (username, password, profile_image) VALUES (:username, :password, :profile_image)");
            $stmt->execute(['username' => $username, 'password' => password_hash($password, PASSWORD_BCRYPT), 'profile_image' => $profile_image]);

            // Redirect to login page after successful registration
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <div class="password-container">
                <input type="password" id="register-password" name="password" placeholder="Password" required>
                <i id="toggle-register-password" class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('register-password', 'toggle-register-password')"></i>
            </div>
            <input type="file" name="profile_image" required>
            <button type="submit">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>