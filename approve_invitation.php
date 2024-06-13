<?php
include 'config.php';
include 'user-nav.php';

if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}

if (isset($_GET['id'])) {
    $invitation_id = $_GET['id'];

    $update_query = $conn->prepare("UPDATE event_invitations SET status = 'approved' WHERE id = ?");
    $update_query->bind_param('i', $invitation_id);
    $update_query->execute();

    if ($update_query->affected_rows > 0) {
        echo "Invitation approved successfully!";
    } else {
        echo "Error approving invitation.";
    }
} else {
    echo "No invitation ID provided.";
}

header("location: user-profile.php");
exit();
?>
