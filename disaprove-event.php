<?php
require('config.php');

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $sql = "UPDATE events SET approved = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        // Event disapproved successfully
        header("Location: events.php.php?id=$event_id");
        exit();
    } else {
        // Error occurred
        echo "Error: " . $conn->error;
    }
} else {
    // Invalid request
    echo "Invalid request";
}

$conn->close();
?>
