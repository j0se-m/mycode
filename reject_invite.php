<?php
session_start();
include 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}

// Database connection using $conn from config.php
$mysqli = $conn;

// Handle reject action for event invite
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invite_id'])) {
    $invite_id = $_POST['invite_id'];

    // Update invite status to rejected
    $status = 'rejected';
    $update_query = $mysqli->prepare("UPDATE event_invites SET status = ? WHERE id = ?");
    $update_query->bind_param('si', $status, $invite_id);
    $update_query->execute();

    // Redirect to avoid form resubmission
    header("Location: invite-display.php");
    exit();
}
?>
