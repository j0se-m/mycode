<?php
require 'config.php';
include 'headerr.php';

// Check if the user is authenticated and allowed to send requests
// if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'user') {
//     header("Location: login.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];

    // Connect to the database
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check if the event_id exists in the events table
    $event_check_query = "SELECT id FROM events WHERE id = ?";
    $stmt = mysqli_prepare($conn, $event_check_query);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Event exists, proceed with inserting the request
        $insert_query = "INSERT INTO event_requests (event_id, user_id) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "ii", $event_id, $user_id);

        if (mysqli_stmt_execute($stmt_insert)) {
            header("Location: view-event.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }

        // Close the statement for inserting
        mysqli_stmt_close($stmt_insert);
    } else {
        echo "Invalid event ID.";
    }

    // Close the statement for checking event existence and the connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Attend Event</title>
</head>
<body>
    <div class="container">
        <h2>Request to Attend Event</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="event_id">Event ID:</label>
                <input type="text" class="form-control" id="event_id" name="event_id">
            </div>
            <button type="submit" class="btn btn-primary">Send Request</button>
        </form>
    </div>
</body>
</html>
