<?php
header('Content-Type: application/json');
require_once 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $subject     = $conn->real_escape_string(trim($_POST['subject'] ?? ''));
    $priority    = $conn->real_escape_string($_POST['priority'] ?? 'Medium');
    $due_date    = $conn->real_escape_string($_POST['due_date'] ?? '');
    $status      = 'Pending'; // Default status on creation

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Task title is required.']);
        exit;
    }

    $sql = "INSERT INTO tasks (title, description, subject, priority, due_date, status)
            VALUES ('$title', '$description', '$subject', '$priority', '$due_date', '$status')";

    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully.',
            'id'      => $conn->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
