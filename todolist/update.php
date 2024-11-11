<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE tasks SET taskstatus = :status WHERE taskid = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);

    echo json_encode(['success' => true]);
    exit; // Pastikan tidak ada output tambahan
}
?>

<script>
function updateTaskStatus(taskId, status) {
    console.log("Status changed to:", status);
    const formData = new FormData();
    formData.append('id', taskId);
    formData.append('status', status);

    fetch('update.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text()) // Gunakan .text() dulu untuk debug
.then(text => {
    console.log('Raw response:', text); // Lihat respon sebelum diubah ke JSON
    const data = JSON.parse(text); // Parse ke JSON setelah pengecekan
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