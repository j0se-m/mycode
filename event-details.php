<?php
include 'headerr.php';

// Database connection using $conn from config.php
$mysqli = $conn;

// Fetch event details based on event ID from URL
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch event details
    $event_query = $mysqli->prepare("SELECT * FROM events WHERE id = ?");
    $event_query->bind_param('i', $event_id);
    $event_query->execute();
    $event_result = $event_query->get_result();
    $event = $event_result->fetch_assoc();

    if (!$event) {
        echo '<script>alert("Event not found.");</script>';
        echo '<script>window.location.href = "invite-display-admin.php";</script>';
        exit();
    }

    // Fetch invited users and their statuses
    $invite_query = $mysqli->prepare("
        SELECT 
            event_invites.id AS invite_id,
            crud.username AS invited_user, 
            event_invites.status AS invite_status
        FROM event_invites
        JOIN crud ON event_invites.invited_user_id = crud.id
        WHERE event_invites.event_id = ?
    ");
    $invite_query->bind_param('i', $event_id);
    $invite_query->execute();
    $invite_result = $invite_query->get_result();

    // Initialize $invites array
    $invites = [];
    if ($invite_result !== false) {
        while ($invite = $invite_result->fetch_assoc()) {
            $invites[] = $invite;
        }
    }
} else {
    echo '<script>alert("No event specified.");</script>';
    echo '<script>window.location.href = "invite-display-admin.php";</script>';
    exit();
}

// Handle form submissions for approving/disapproving invites
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['invite_id'])) {
    $action = $_POST['action'];
    $invite_id = $_POST['invite_id'];

    if ($action == 'approve' || $action == 'disapprove') {
        $status = ($action == 'approve') ? 'approved' : 'rejected';

        $update_query = $mysqli->prepare("UPDATE event_invites SET status = ? WHERE id = ?");
        $update_query->bind_param('si', $status, $invite_id);
        if ($update_query->execute()) {
            echo '<script>alert("Invite '.$status.' successfully.");</script>';
            echo '<script>window.location.href = "event-details.php?id=' . $event_id . '";</script>';
            exit();
        } else {
            echo '<script>alert("Failed to update invite status.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title>Event Details - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            flex-direction: column;
        }
        .container {
            flex: 12;
            margin-left: 200px;
            width: calc(100% - 200px);
            overflow-x: auto;
        }
        .event-details {
            margin-top: 20px;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
        }
        table {
            table-layout: auto;
            width: 100%;
        }
        th, td {
            white-space: nowrap;
        }
        .table th, .table td {
            min-width: 150px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="event-details">
        <h2><?php echo htmlspecialchars($event['name']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
        <!-- Add more event details as needed -->

        <h3>Invited Users</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invites as $invite): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invite['invited_user']); ?></td>
                        <td><?php echo htmlspecialchars($invite['invite_status']); ?></td>
                     
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($invites)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No invites found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>