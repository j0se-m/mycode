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

// Get the event details based on invite ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invite_id'])) {
    $invite_id = $_POST['invite_id'];

    $event_query = $mysqli->prepare("
        SELECT events.name AS event_name, events.user_id, crud.username AS inviter_username
        FROM event_invites
        JOIN events ON event_invites.event_id = events.id
        JOIN crud ON events.user_id = crud.id
        WHERE event_invites.id = ?
    ");
    $event_query->bind_param('i', $invite_id);
    $event_query->execute();
    $event_result = $event_query->get_result();

    if ($event_result->num_rows > 0) {
        $event = $event_result->fetch_assoc();
        $event_name = $event['event_name'];
        $inviter_username = $event['inviter_username'];
        $user_id = $event['user_id'];

        // Send notification to event poster
        $notification_message = "Your invite for the event '$event_name' has been accepted by " . $_SESSION['username'];
        $insert_notification_query = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $insert_notification_query->bind_param('is', $user_id, $notification_message);
        $insert_notification_query->execute();

        $_SESSION['success_message'] = "Notification sent to event poster.";
        header("Location: user-profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error fetching event details.";
    }
}

// Redirect back in case of errors
header("Location: user-profile.php");
exit();
?>
