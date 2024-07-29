<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];

    // Establish database connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the status based on the action
    $status = ($action == 'approve') ? 'approved' : 'disapproved';

    $sql = "UPDATE event_requests SET status = ? WHERE event_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('si', $status, $event_id);
        if ($stmt->execute()) {
            echo "Request status updated successfully.";
        } else {
            echo "Error updating status: " . $stmt->error;
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
