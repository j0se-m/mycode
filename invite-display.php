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
$user_query = $mysqli->prepare("SELECT id, email, first_name, last_name FROM crud WHERE username = ?");
$user_query->bind_param('s', $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Fetch user events
$event_query = $mysqli->prepare("SELECT id, name FROM events WHERE user_id = ?");
$event_query->bind_param('i', $user['id']);
$event_query->execute();
$event_result = $event_query->get_result();

// Initialize $events array
$events = [];
if ($event_result !== false) {
    while ($event = $event_result->fetch_assoc()) {
        $events[] = $event;
    }
}

// Fetch invites for user's events
$invite_query = $mysqli->prepare("
    SELECT 
        event_invites.id AS invite_id,
        events.name AS event_name, 
        crud.username AS invited_user, 
        event_invites.status AS invite_status
    FROM event_invites
    JOIN events ON event_invites.event_id = events.id
    JOIN crud ON event_invites.invited_user_id = crud.id
    WHERE events.user_id = ?
");
$invite_query->bind_param('i', $user['id']);
$invite_query->execute();
$invite_result = $invite_query->get_result();

// Initialize $invites array
$invites = [];
if ($invite_result !== false) {
    while ($invite = $invite_result->fetch_assoc()) {
        $invites[] = $invite;
    }
}

// Handle approve/disapprove/delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['invite_id'])) {
    $action = $_POST['action'];
    $invite_id = $_POST['invite_id'];

    if ($action == 'delete') {
        $delete_query = $mysqli->prepare("DELETE FROM event_invites WHERE id = ?");
        $delete_query->bind_param('i', $invite_id);
        $delete_query->execute();
        header("Location: invite-display.php"); // Redirect to avoid form resubmission
        exit();
    }

    $status = ($action == 'approve') ? 'approved' : 'disapproved';

    $update_query = $mysqli->prepare("UPDATE event_invites SET status = ? WHERE id = ?");
    $update_query->bind_param('si', $status, $invite_id);
    $update_query->execute();
    header("Location: invite-display.php"); // Redirect to avoid form resubmission
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Users - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
            width: 100%;
        }
        .profile-header {
            text-align: center;
            margin: 20px 0;
        }
        .profile-details {
            margin-top: 20px;
        }
        .footer {
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($username); ?></h1>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="my-event.php">My Events</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="invite-display.php">Invites</a>
            </li>
        </ul>
        <div class="profile-details">
            <h2>Your Event Invites</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Invited User</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invites as $invite): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invite['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($invite['invited_user']); ?></td>
                            <td><?php echo htmlspecialchars($invite['invite_status']); ?></td>
                            <td>
                                <?php if ($invite['invite_status'] == 'pending'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="invite_id" value="<?php echo $invite['invite_id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-custom btn-sm">Approve</button>
                                        <button type="submit" name="action" value="disapprove" class="btn btn-danger btn-sm">Disapprove</button>
                                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($invite['invite_status']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($invites)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No invites found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <?php include 'footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
