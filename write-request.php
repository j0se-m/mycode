php
Copy code
<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if event ID is provided
if (!isset($_GET['event_id'])) {
    header("Location: user-profile.php");
    exit();
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

// Fetch event details for display or validation purposes
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

$event_query = "SELECT * FROM events WHERE id = $event_id";
$event_result = mysqli_query($conn, $event_query);

if (!$event_result || mysqli_num_rows($event_result) != 1) {
    $_SESSION['error_message'] = "Event not found.";
    header("Location: user-profile.php");
    exit();
}

$event_row = mysqli_fetch_assoc($event_result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_message'])) {
    $request_message = $_POST['request_message'];

    // You can perform additional validation or sanitization here

    // Insert request message into database
    $insert_query = "INSERT INTO request_messages (user_id, event_id, message) VALUES ($user_id, $event_id, '$request_message')";
    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success_message'] = "Your request message has been sent.";
    } else {
        $_SESSION['error_message'] = "Error sending request message. Please try again.";
    }

    mysqli_close($conn);
    header("Location: user-profile.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Request</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Write Request Message</h2>
        <form method="POST">
            <div class="form-group">
                <label for="request_message">Your Request Message:</label>
                <textarea class="form-control" id="request_message" name="request_message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Request</button>
        </form>
    </div>
</body>
</html>