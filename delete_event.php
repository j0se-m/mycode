<?php
require('config.php');

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Check if confirmation is received from user
    if(isset($_GET['confirm'])) {
        if ($_GET['confirm'] === 'yes') {
            $sql = "DELETE FROM events WHERE id='$event_id'";
            if ($conn->query($sql) === TRUE) {
                header("Location:events.php");
                exit();
            } else {
                echo "Error deleting event: " . $conn->error;
            }
        } else {
            header("Location:events.php");
            exit();
        }
    }

    // If confirmation not received, show confirmation dialog
    echo '<script>
            if(confirm("Are you sure you want to delete this event?")) {
                window.location.href = "delete_event.php?id=' . $event_id . '&confirm=yes";
            } else {
                window.location.href = "events.php";
            }
          </script>';
} else {
    echo "Event ID not provided";
}

$conn->close();
?>
