<?php
// Include database configuration
include 'config.php';
include 'user-nav.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$invite_id = $_POST['invite_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validate input
if ($action !== 'reject' || !$invite_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

// Database connection
$mysqli = $conn;

// Fetch user details
$username = $_SESSION['username'];
$user_query = $mysqli->prepare("SELECT id FROM crud WHERE username = ?");
$user_query->bind_param('s', $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Check if user exists
if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit();
}

// Update invite status to rejected
$update_query = $mysqli->prepare("UPDATE event_invites SET status = 'rejected' WHERE id = ? AND invited_user_id = ?");
$update_query->bind_param('ii', $invite_id, $user['id']);

if ($update_query->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Invite rejected']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

$update_query->close();
$mysqli->close();
?>
