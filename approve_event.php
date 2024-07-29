<?php
require('config.php');

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $sql = "UPDATE events SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        // Event disapproved successfully
        header("Location: admin-readmore.php?id=" . $event_id);

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
