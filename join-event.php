<?php
// Include necessary files and start session if not already started
include 'user-nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the event ID is provided via GET parameter
if (!isset($_GET['id'])) {
    echo "Event ID not specified.";
    exit();
}

$event_id = $_GET['id'];

// Database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

// Check if the user has already joined or requested to join the event
$sql_check = "SELECT * FROM event_requests WHERE event_id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $event_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // User has already requested to join or is a participant
    $message = "You have already sent a request to join this event or are already a participant.";
    $stmt_check->close();
    $conn->close();
} else {
    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize the input data
        $message = $_POST['message'] ?? '';
        $message = htmlspecialchars(trim($message)); // Sanitize the message

        // Validate if the message is not empty
        if (empty($message)) {
            $error = "Please enter a message.";
        } else {
            // Insert the request into the database
            $sql = "INSERT INTO event_requests (event_id, user_id, attend_text) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $event_id, $user_id, $message);
            if ($stmt->execute()) {
                $success = "Request sent successfully.";
            } else {
                $error = "Error sending request. Please try again.";
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Event</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-left: 210px;
        }
        .fade-out {
            opacity: 1;
            transition: opacity 1s ease-out;
        }
        .fade-out.hidden {
            opacity: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4 mb-4">Join Event</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger fade-out"><?php echo $error; ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success fade-out"><?php echo $success; ?></div>
    <?php elseif (isset($message)): ?>
        <div class="alert alert-warning fade-out"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!isset($message) || $message !== "You have already sent a request to join this event or are already a participant."): ?>
        <form method="POST">
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Request</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Apply the fade-out effect to all elements with the class 'fade-out'
        setTimeout(function() {
            $('.fade-out').addClass('hidden');
        }, 3000); // Adjust the timeout duration as needed
    });
</script>

</body>
</html>
