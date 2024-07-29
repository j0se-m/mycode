<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: userLogin.php");
    exit();
}

// Database connection
$mysqli = $conn;

// Handle accept action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invite_id = $_POST['invite_id'];

    if (empty($invite_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid invite ID']);
        exit();
    }

    // Update the invite status in the database
    $query = $mysqli->prepare("UPDATE event_invites SET status = 'accepted' WHERE id = ?");
    $query->bind_param('i', $invite_id);

    if ($query->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to accept invite']);
    }
    exit();
}
