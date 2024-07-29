<?php
// Include necessary files
include 'config.php'; // Database connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Validate POST data
if (isset($_POST['invite_id']) && $_POST['action'] === 'reject') {
    $invite_id = intval($_POST['invite_id']);
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Reject invite
    $stmt = $mysqli->prepare("UPDATE event_invites SET status = 'rejected' WHERE id = ? AND invited_user_id = ?");
    $stmt->bind_param('ii', $invite_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reject invite']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$mysqli->close();
?>
