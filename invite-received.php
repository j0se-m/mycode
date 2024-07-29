<?php
session_start();
include 'config.php';
include 'user-nav.php';

if (!isset($_SESSION['username'])) {
    header("Location: userLogin.php");
    exit();
}

// Database connection
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
$invite_query = $mysqli->prepare("
    SELECT 
        event_invites.id AS invite_id, 
        events.id AS event_id, 
        events.name AS event_name, 
        events.image AS event_image, 
        events.description AS event_description, 
        event_invites.sender_id 
    FROM 
        event_invites 
    JOIN 
        events ON event_invites.event_id = events.id 
    WHERE 
        event_invites.invited_user_id = ?
");
$invite_query->bind_param('i', $user['id']);
$invite_query->execute();
$invite_result = $invite_query->get_result();
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
            margin-top: 12px; /* Adjust for navbar height */
            margin-left: 180px; /* Adjust for sidebar width */
        }
        .table-wrapper {
            max-height: 400px; /* Adjust the height as needed */
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <h4 class="my-4">Invites Received</h4>
            <div id="invite-message"></div>
            <div class="table-wrapper">
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
                            <tr id="invite-<?php echo htmlspecialchars($invite['invite_id']); ?>">
                                <td class="truncate">
                                    <a href="user-readmore.php?id=<?php echo htmlspecialchars($invite['event_id']); ?>">
                                        <?php echo htmlspecialchars($invite['event_name']); ?>
                                    </a>
                                </td>
                                <td class="truncate"><?php echo htmlspecialchars($invite['event_description']); ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm accept-invite" data-invite-id="<?php echo htmlspecialchars($invite['invite_id']); ?>">Accept</button>
                                    <button class="btn btn-danger btn-sm reject-invite" data-invite-id="<?php echo htmlspecialchars($invite['invite_id']); ?>">Reject</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.accept-invite').on('click', function() {
                let inviteId = $(this).data('invite-id');
                $.ajax({
                    type: 'POST',
                    url: 'accept_invite.php',
                    data: { invite_id: inviteId },
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status === 'success') {
                            $('#invite-' + inviteId).remove();
                            $('#invite-message').html('<div class="alert alert-success">Invite accepted successfully.</div>');
                        } else {
                            $('#invite-message').html('<div class="alert alert-danger">' + result.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#invite-message').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                    }
                });
            });

            $('.reject-invite').on('click', function() {
                let inviteId = $(this).data('invite-id');
                $.ajax({
                    type: 'POST',
                    url: 'reject_invite.php',
                    data: { invite_id: inviteId },
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status === 'success') {
                            $('#invite-' + inviteId).remove();
                            $('#invite-message').html('<div class="alert alert-success">Invite rejected successfully.</div>');
                        } else {
                            $('#invite-message').html('<div class="alert alert-danger">' + result.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#invite-message').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
