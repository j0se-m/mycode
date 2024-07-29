<?php
// change-status.php

// Include database connection or initialization file
include "headerr.php";

// Check if the POST parameters are set
if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update the status in the database
    $updateSql = "UPDATE crud SET status = $status WHERE id = $id";
    if (mysqli_query($conn, $updateSql)) {
        // Success response
        $response = array('success' => true);
        echo json_encode($response);
    } else {
        // Error response
        $response = array('success' => false);
        echo json_encode($response);
    }
} else {
    // Invalid request response
    $response = array('success' => false);
    echo json_encode($response);
}
?>
