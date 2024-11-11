<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $pdo->prepare("SELECT username, profile_image FROM user WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_username'])) {
        $new_username = $_POST['new_username'];
        $stmt = $pdo->prepare("UPDATE user SET username = :username WHERE id = :user_id");
        $stmt->execute(['username' => $new_username, 'user_id' => $user_id]);
        $user['username'] = $new_username;
    }

    if (isset($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE user SET password = :password WHERE id = :user_id");
        $stmt->execute(['password' => $new_password, 'user_id' => $user_id]);
    }

    if (isset($_FILES['new_profile_image']) && $_FILES['new_profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["new_profile_image"]["name"]);
        if (move_uploaded_file($_FILES["new_profile_image"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("UPDATE user SET profile_image = :profile_image WHERE id = :user_id");
            $stmt->execute(['profile_image' => $target_file, 'user_id' => $user_id]);
            $user['profile_image'] = $target_file;
        }
    }
}

if (isset($_GET['download'])) {
    $file = $user['profile_image'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "File not found.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Detail</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .user-detail-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            max-width: 800px;
            width: 100%;
        }

        .user-profile {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
            cursor: pointer; /* Add cursor pointer to indicate clickable */
        }

        .user-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .user-info input,
        .user-info button,
        .user-info a {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        .user-info button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .user-info button:hover {
            background-color: #45a049;
        }

        .download-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .download-button:hover {
            background-color: #0056b3;
        }

        .back-button {
            background-color: #ff4b5c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .back-button:hover {
            background-color: #ff1f3a;
        }

        /* Modal container */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
        }

        /* Modal content (image) */
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }

        /* Caption of modal image */
        #caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
            height: 150px;
        }

        /* Add animation (zoom in the modal) */
        .modal-content, #caption { 
            animation-name: zoom;
            animation-duration: 0.6s;
        }

        @keyframes zoom {
            from {transform: scale(0)} 
            to {transform: scale(1)}
        }

        /* The close button */
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="user-detail-container">
        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="user-profile" id="profileImage">
        <div class="user-info">
            <form action="user_detail.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="new_username" placeholder="New Username" value="<?php echo htmlspecialchars($user['username']); ?>">
                <input type="password" name="new_password" placeholder="New Password">
                <input type="file" name="new_profile_image">
                <button type="submit">Update</button>
            </form>
            <a href="user_detail.php?download=true" class="download-button">Download Profile</a>
            <a href="index.php" class="back-button">Back</a>
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var img = document.getElementById("profileImage");
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() { 
            modal.style.display = "none";
        }
    </script>
</body>
</html>