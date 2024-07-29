<?php
// Include necessary files
include 'user-nav.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}

// Database connection using $conn from config.php
$mysqli = $conn;

// Fetch user details
$username = $_SESSION['username'];
$user_query = $mysqli->prepare("SELECT id FROM crud WHERE username = ?");
$user_query->bind_param('s', $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Validate parameters from URL
if (!isset($_GET['event_id']) || !isset($_GET['invited_user'])) {
    // Handle error if parameters are missing
    echo "Error: Event ID or Invited User not specified.";
    exit();
}

$event_id = (int)$_GET['event_id'];
$invited_user = $_GET['invited_user'];

// Fetch event details
$event_query = $mysqli->prepare("
    SELECT 
        name AS event_name,
        description AS event_description,
        date,
        location
    FROM events
    WHERE id = ? AND user_id = ?
");
$event_query->bind_param('ii', $event_id, $user['id']);
$event_query->execute();
$event_result = $event_query->get_result();
$event = $event_result->fetch_assoc();

// Fetch invite details
$invite_query = $mysqli->prepare("
    SELECT 
        event_invites.status AS invite_status,
        crud.username AS invited_user_name
    FROM event_invites
    JOIN crud ON event_invites.invited_user_id = crud.id
    WHERE event_invites.event_id = ? AND crud.username = ?
");
$invite_query->bind_param('is', $event_id, $invited_user);
$invite_query->execute();
$invite_result = $invite_query->get_result();
$invite = $invite_result->fetch_assoc();

// Check if invite exists
if (!$invite) {
    // Handle error if invite does not exist
    echo "Error: Invite not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invite Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 20px;
            margin-left: 200px;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Invite Details</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Event Name: <?php echo htmlspecialchars($event['event_name']); ?></h5>
                <p class="card-text">Event Description: <?php echo htmlspecialchars($event['event_description']); ?></p>
                <p class="card-text">Event Date: <?php echo htmlspecialchars($event['date']); ?></p>
                <p class="card-text">Location: <?php echo htmlspecialchars($event['location']); ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Invited User: <?php echo htmlspecialchars($invite['invited_user_name']); ?></h5>
                <p class="card-text">Invite Status: <?php echo htmlspecialchars($invite['invite_status']); ?></p>
            </div>
        </div>
        <a href="invite-display.php" class="btn btn-primary">Back to Your Events</a>
    </div>
    <!-- JavaScript links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
