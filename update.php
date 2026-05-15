<?php
header('Content-Type: application/json');
require_once 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['id'] ?? 0);
    $title       = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $subject     = $conn->real_escape_string(trim($_POST['subject'] ?? ''));
    $priority    = $conn->real_escape_string($_POST['priority'] ?? 'Medium');
    $due_date    = $conn->real_escape_string($_POST['due_date'] ?? '');
    $status      = $conn->real_escape_string($_POST['status'] ?? 'Pending');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
        exit;
    }

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Task title is required.']);
        exit;
    }

    $sql = "UPDATE tasks SET
                title       = '$title',
                description = '$description',
                subject     = '$subject',
                priority    = '$priority',
                due_date    = '$due_date',
                status      = '$status'
            WHERE id = $id";

    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or task not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
