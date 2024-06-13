<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $status = isset($_POST['approve']) ? 'approved' : 'rejected';

    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $sql = "UPDATE event_requests SET status='$status' WHERE id='$request_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: approve-requests.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    mysqli_close($conn);
}
?>
