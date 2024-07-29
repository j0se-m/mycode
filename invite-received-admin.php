<?php
include 'headerr.php';

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

if (!$user) {
    die('User not found');
}

// Fetch invites for the user
$invite_query = $mysqli->prepare("SELECT event_invites.id as invite_id, events.id as event_id, events.name as event_name, events.image as event_image, events.description as event_description, event_invites.sender_id FROM event_invites JOIN events ON event_invites.event_id = events.id WHERE event_invites.invited_user_id = ?");
$invite_query->bind_param('i', $user['id']);
$invite_query->execute();
$invite_result = $invite_query->get_result();

if (!$invite_result) {
    die('No invites found');
}

// Handle invite acceptance/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure invite_id is set in the POST data
    if (!isset($_POST['invite_id'])) {
        die('Invite ID not provided');
    }

    $invite_id = $_POST['invite_id'];
    $invite_status = '';
    $notification_message = '';

    if (isset($_POST['accept_invite'])) {
        $invite_status = 'accepted';
        $notification_message = 'has accepted your event invitation.';
    } elseif (isset($_POST['reject_invite'])) {
        $invite_status = 'rejected';
        $notification_message = 'has rejected your event invitation.';
    }

    // Update invite status
    $status_query = $mysqli->prepare("UPDATE event_invites SET status = ? WHERE id = ?");
    $status_query->bind_param('si', $invite_status, $invite_id);
    $status_query->execute();

    // Fetch event and sender details
    $invite_query = $mysqli->prepare("SELECT events.name as event_name, event_invites.sender_id FROM event_invites JOIN events ON event_invites.event_id = events.id WHERE event_invites.id = ?");
    $invite_query->bind_param('i', $invite_id);
    $invite_query->execute();
    $invite_result = $invite_query->get_result();
    $invite = $invite_result->fetch_assoc();

    if (!$invite) {
        die('Invite not found');
    }

    // Check if sender_id exists in the crud table
    $sender_query = $mysqli->prepare("SELECT id FROM crud WHERE id = ?");
    $sender_query->bind_param('i', $invite['sender_id']);
    $sender_query->execute();
    $sender_result = $sender_query->get_result();
    $sender = $sender_result->fetch_assoc();

    if (!$sender) {
        die('Sender not found');
    }

    // Insert notification for sender
    $notification_message = $username . ' ' . $notification_message . ' Event: ' . $invite['event_name'];
    $notification_query = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notification_query->bind_param('is', $invite['sender_id'], $notification_message);

    try {
        $notification_query->execute();
    } catch (mysqli_sql_exception $e) {
        error_log("Failed to insert notification: " . $e->getMessage());
        die('Failed to insert notification');
    }

    // Redirect after processing
    header("location: invite-received-admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invites Received - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .content {
            margin-top:12px; /* Adjusting for navbar height */
            margin-left: 180px; /* Adjusting for sidebar width */
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <h4 class="my-4">Invites Received</h4>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Event Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($invite = $invite_result->fetch_assoc()): ?>
                        <tr>
                            <td class="truncate"><?php echo htmlspecialchars($invite['event_name']); ?></td>
                            <td class="truncate"><?php echo htmlspecialchars($invite['event_description']); ?></td>
                            <td>
                                <form method="post" action="invite-received-admin.php">
                                    <input type="hidden" name="invite_id" value="<?php echo htmlspecialchars($invite['invite_id']); ?>">
                                    <button type="submit" name="accept_invite" class="btn btn-success btn-sm">Accept</button>
                                    <button type="submit" name="reject_invite" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
