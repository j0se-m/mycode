<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];

    // Establish database connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Disable the request instead of deleting it
    $sql = "UPDATE event_requests SET status = 'deleted' WHERE event_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $event_id);
        if ($stmt->execute()) {
            echo "Request deleted successfully.";
        } else {
            echo "Error deleting request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}
header("Location: event-requests.php");
exit();
?>
