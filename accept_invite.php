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

// Handle accept action for event invite
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invite_id'])) {
    $invite_id = $_POST['invite_id'];

    // Update invite status to accepted
    $status = 'accepted';
    $update_query = $mysqli->prepare("UPDATE event_invites SET status = ? WHERE id = ?");
    $update_query->bind_param('si', $status, $invite_id);
    if ($update_query->execute()) {
        // Fetch inviter's username
        $inviter_query = $mysqli->prepare("SELECT invited_by FROM event_invites WHERE id = ?");
        $inviter_query->bind_param('i', $invite_id);
        if ($inviter_query->execute()) {
            $inviter_result = $inviter_query->get_result();
            $inviter = $inviter_result->fetch_assoc();
            $inviter_username = $inviter['invited_by'];

            // Send message to inviter
            $message = "Congratulations! Your invite has been accepted.";
            $insert_message_query = $mysqli->prepare("INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)");
            $insert_message_query->bind_param('sss', $_SESSION['username'], $inviter_username, $message);
            if ($insert_message_query->execute()) {
                // Redirect to avoid form resubmission and display message
                $_SESSION['success_message'] = "Thank you for joining our event!";
                header("Location: user-profile.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error sending message to inviter.";
            }
        } else {
            $_SESSION['error_message'] = "Error fetching inviter's username.";
        }
    } else {
        $_SESSION['error_message'] = "Error updating invite status.";
    }
}

// Redirect back in case of errors
header("Location: user-profile.php");
exit();
?>
