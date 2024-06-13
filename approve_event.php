<?php
require 'config.php';
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: pending.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No event ID provided.";
    exit();
}

$event_id = intval($_GET['id']);

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE events SET approved = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    $_SESSION['toast_message'] = "Event approved successfully.";
} else {
    $_SESSION['toast_message'] = "Error approving event: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: pending.php");
exit();
?>
