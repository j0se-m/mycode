<?php
// Include the necessary files or configuration
include 'user-nav.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Connect to the database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch events where the current user is not the creator
$sql_events = "SELECT id, name FROM events WHERE user_id != ?";
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $user_id);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
$events = [];
while ($row = $result_events->fetch_assoc()) {
    $events[] = $row;
}
$stmt_events->close();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    // Sanitize and validate the input
    $event_id = $_POST['event_id'];
    $message = $_POST['message'] ?? '';
    $message = htmlspecialchars(trim($message)); // Sanitize the message

    // Validate if the message is not empty
    if (empty($message)) {
        $error = "Please enter a message.";
    } else {
        // Check if the user has already requested to attend this event
        $check_request_query = "SELECT id FROM event_requests WHERE event_id = ? AND user_id = ?";
        $stmt_check_request = mysqli_prepare($conn, $check_request_query);
        mysqli_stmt_bind_param($stmt_check_request, "ii", $event_id, $user_id);
        mysqli_stmt_execute($stmt_check_request);
        mysqli_stmt_store_result($stmt_check_request);

        if (mysqli_stmt_num_rows($stmt_check_request) > 0) {
            $error = "You have already requested to attend this event.";
        } else {
            // Prepare the SQL statement to check if the event_id exists in the events table and user is not the creator
            $event_check_query = "SELECT id FROM events WHERE id = ? AND user_id != ?";
            $stmt = mysqli_prepare($conn, $event_check_query);
            mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            // Check if the event exists and the user is not the creator
            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Event exists, proceed with inserting the request
                $insert_query = "INSERT INTO event_requests (event_id, user_id, attend_text) VALUES (?, ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt_insert, "iis", $event_id, $user_id, $message);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $success = "Request sent successfully.";
                } else {
                    $error = "Error sending request. Please try again.";
                }

                // Close the statement for inserting
                mysqli_stmt_close($stmt_insert);
            } else {
                $error = "Invalid event ID or you are the creator of this event."; // Display error message if event ID is invalid or user is the creator
            }

            // Close the statement for checking event existence
            mysqli_stmt_close($stmt);
        }

        // Close the statement for checking existing request
        mysqli_stmt_close($stmt_check_request);
    }
    mysqli_close($conn); // Close the connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Attend Event</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 50px;
        }
        .form-container {
            margin-left: 150px; /* Adjust the margin as needed */
        }
        .alert {
            text-align: center; /* Center the text in the alert box */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Request to Attend Event</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="event_id">Select Event:</label>
                    <select class="form-control" id="event_id" name="event_id" required>
                        <option value="">Choose an event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </form>
        </div>
    </div>
</body>
</html>
