<?php
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

if (!$user) {
    die('User not found');
}

// Fetch invites for the user
$invite_query = $mysqli->prepare("SELECT event_invites.id as invite_id, events.id as event_id, events.name as event_name, events.image as event_image, events.description as event_description, event_invites.sender_id FROM event_invites JOIN events ON event_invites.event_id = events.id WHERE event_invites.invited_user_id = ?");
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
            margin-top: 12px; /* Adjusting for navbar height */
            margin-left: 180px; /* Adjusting for sidebar width */
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <h4 class="my-4">Invites Received</h4>
            <div id="invite-message"></div>
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
                        <tr id="invite-<?php echo $invite['invite_id']; ?>">
                            <td class="truncate"><?php echo htmlspecialchars($invite['event_name']); ?></td>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.accept-invite').on('click', function() {
                let inviteId = $(this).data('invite-id');
                $.ajax({
                    type: 'POST',
                    url: 'invite-action.php',
                    data: {invite_id: inviteId, action: 'accept'},
                    success: function(response) {
                        $('#invite-' + inviteId).remove();
                        $('#invite-message').html('<div class="alert alert-success">Invite accepted successfully.</div>');
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
                    url: 'invite-action.php',
                    data: {invite_id: inviteId, action: 'reject'},
                    success: function(response) {
                        $('#invite-' + inviteId).remove();
                        $('#invite-message').html('<div class="alert alert-success">Invite rejected successfully.</div>');
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
