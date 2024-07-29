<?php
// Include database configuration
include 'config.php';

// Check if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: userLogin.php");
//     exit();
// }

// Check if request method is POST and event_id is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Establish database connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("UPDATE event_requests SET status = 'approved' WHERE event_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header("Location: event-request.php");
    
    exit();
} else {
    header("Location:event-request.php");
    exit();
}
?>