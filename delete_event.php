<?php
require('config.php');

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];

    $sql = "DELETE FROM events WHERE id='$event_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location:approved.php");
        exit();
    } else {
        echo "Error deleting event: " . $conn->error;
    }
} else {
    echo "Event ID not provided";
}

$conn->close();
?>
