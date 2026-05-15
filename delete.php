<?php
header('Content-Type: application/json');
require_once 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
        exit;
    }

    $sql = "DELETE FROM tasks WHERE id = $id";

    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
