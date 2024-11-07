<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $pdo->prepare("SELECT username, profile_image FROM user WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
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
    </script>
</head>
<body>
    <div class="profile-container">
        <?php if ($user): ?>
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image">
            <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
        <?php endif; ?>
    </div>
    <div class="container">
        <h2 class="title">To-Do List</h2>
        
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
