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

// Determine sort criteria
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'createdat';
$order = 'ASC';
if ($sort == 'taskstatus') {
    $order = 'ASC';
} elseif ($sort == 'createdat') {
    $order = 'DESC';
}

// Fetch tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY $sort $order");
$stmt->execute(['user_id' => $user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-todo {
            color: blue;
        }

        .status-onprogress {
            color: orange;
        }

        .status-done {
            color: green;
        }

        .completed {
            text-decoration: line-through;
        }

        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .profile-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .username {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .logout-button {
            background-color: #ff4b5c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #ff1f3a;
        }

        .sort-container {
            margin-bottom: 20px;
        }

        .sort-container select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
    <script>
        function updateTaskStatus(taskId, status) {
            const formData = new FormData();
            formData.append('id', taskId);
            formData.append('status', status);

            fetch('update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const taskItem = document.getElementById(`task-item-${taskId}`);
                    const taskName = document.getElementById(`task-name-${taskId}`);
                    
                    taskItem.classList.remove('status-todo', 'status-onprogress', 'status-done');
                    taskName.classList.remove('completed');
                    
                    if (status == 0) {
                        taskItem.classList.add('status-todo');
                    } else if (status == 1) {
                        taskItem.classList.add('status-onprogress');
                    } else if (status == 2) {
                        taskItem.classList.add('status-done');
                        taskName.classList.add('completed');
                    }
                }
            });
        }

        function confirmLogout(event) {
            event.preventDefault(); // Mencegah tindakan default dari link
            const userConfirmed = confirm("Are you sure you want to logout?");
            if (userConfirmed) {
                window.location.href = event.target.href; // Arahkan ke halaman logout jika pengguna mengonfirmasi
            }
        }
    </script>
</head>
<body>
    <div class="profile-container">
        <?php if ($user): ?>
            <div class="profile-info">
                <a href="user_detail.php">
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image">
                </a>
                <a href="user_detail.php" class="username"><?php echo htmlspecialchars($user['username']); ?></a>
            </div>
            <a href="logout.php" class="logout-button" onclick="confirmLogout(event)">Logout</a>
        <?php endif; ?>
    </div>
    <div class="container">
        <h2 class="title">To-Do List</h2>
        <div class="sort-container">
            <form action="index.php" method="GET">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="createdat" <?= $sort == 'createdat' ? 'selected' : '' ?>>Created At</option>
                    <option value="taskstatus" <?= $sort == 'taskstatus' ? 'selected' : '' ?>>Status</option>
                </select>
            </form>
        </div>
        <!-- Form tambah tugas baru -->
        <form action="add.php" method="POST" class="task-form">
            <input type="text" name="task" placeholder="New Task" required class="task-input">
            <button type="submit" class="task-button">Add Task</button>
        </form>
        <!-- Daftar tugas -->
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li id="task-item-<?= $task['taskid'] ?>" class="task-item <?php 
                    echo $task['taskstatus'] == 2 ? 'status-done' : ($task['taskstatus'] == 1 ? 'status-onprogress' : 'status-todo'); ?>">
                    <span id="task-name-<?= $task['taskid'] ?>" class="task-name <?php echo $task['taskstatus'] == 2 ? 'completed' : ''; ?>">
                        <?= htmlspecialchars($task['tasktable']) ?>
                    </span>
                    <div class="task-actions">
                        <form action="update.php" method="POST" class="inline">
                            <input type="hidden" name="id" value="<?= $task['taskid'] ?>">
                            <select name="status" class="status-dropdown" onchange="updateTaskStatus(<?= $task['taskid'] ?>, this.value)">
                                <option value="0" <?= $task['taskstatus'] == 0 ? 'selected' : '' ?>>To Do</option>
                                <option value="1" <?= $task['taskstatus'] == 1 ? 'selected' : '' ?>>On Progress</option>
                                <option value="2" <?= $task['taskstatus'] == 2 ? 'selected' : '' ?>>Done</option>
                            </select>
                        </form>
                        <form action="delete.php" method="POST" class="inline">
                            <input type="hidden" name="id" value="<?= $task['taskid'] ?>">
                            <button type="submit" class="delete-button">✖</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
