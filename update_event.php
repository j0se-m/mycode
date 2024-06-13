<?php
require('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['event_id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['location']) && isset($_POST['date'])) {
        $event_id = $_POST['event_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $location = $_POST['location'];
        $date = $_POST['date'];

        if ($_FILES['image']['name']) {
            $image = $_FILES['image']['name'];
            $temp_image = $_FILES['image']['tmp_name'];
            $upload_dir = "uploads/"; 

            move_uploaded_file($temp_image, $upload_dir.$image);

            $stmt = $conn->prepare("UPDATE events SET name=?, description=?, location=?, date=?, image=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $description, $location, $date, $image, $event_id);
        } else {
            $stmt = $conn->prepare("UPDATE events SET name=?, description=?, location=?, date=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $description, $location, $date, $event_id);
        }

        if ($stmt->execute()) {
            header("Location: pending.php");
            exit();
        } else {
            echo "Error updating event: " . $stmt->error;
        }
    } else {
        echo "All fields are required";
    }
}

$conn->close();
?>
