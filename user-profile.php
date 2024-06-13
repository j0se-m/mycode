<?php
include 'user-nav.php';
// include 'config.php';

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
$event_query = $mysqli->prepare("SELECT id, name, image, description, created_at, approved FROM events WHERE user_id = ?");
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

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $file_path = 'uploads/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $file_path)) {
            // Update profile picture path in the database
            $update_query = $mysqli->prepare("UPDATE crud SET profile_picture = ? WHERE id = ?");
            $update_query->bind_param('si', $file_path, $user['id']);
            $update_query->execute();

            // Refresh the page to reflect the new profile picture
            header("location: user-profile.php");
            exit();
        }
    } elseif (isset($_POST['remove_picture'])) {
        // Remove profile picture
        $remove_query = $mysqli->prepare("UPDATE crud SET profile_picture = NULL WHERE id = ?");
        $remove_query->bind_param('i', $user['id']);
        $remove_query->execute();

        // Refresh the page to reflect the removal of the profile picture
        header("location: user-profile.php");
        exit();
    }
}

// Fetch invites for the user
$invite_query = $mysqli->prepare("SELECT id, event_id FROM event_invites WHERE invited_user_id = ?");
$invite_query->bind_param('i', $user['id']);
$invite_query->execute();
$invite_result = $invite_query->get_result();

// Initialize $invites array
$invites = [];
if ($invite_result !== false) {
    while ($invite = $invite_result->fetch_assoc()) {
        $invites[] = $invite['event_id'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-header {
            text-align: center;
            margin: 20px 0;
        }
        .profile-picture {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            border: 5px solid #007bff;
        }
        .profile-details {
            margin-top: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card img {
            width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: cover;
        }
        .card-body {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .read-more {
            cursor: pointer;
            color: #007bff;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        .tab-content {
            margin-top: 20px;
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: #ffffff !important;
        }
        .tab-content .card {
            margin-bottom: 20px;
        }
        .invite-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .invite-card h5 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="profile-header">
                    <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'path_to_default_profile_picture.jpg'); ?>" alt="Profile Picture" class="profile-picture">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                </div>
                <div class="profile-details">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $status = $event['approved'] == 1 ? '' : '(pending)';
                        $image_url = !empty($event['image']) ? "uploads/" . htmlspecialchars($event['image']) : "path_to_placeholder_image.jpg";
                        ?>
                        <div class="col">
                            <div class="card">
                                <img src="<?php echo $image_url; ?>" alt="Event Image" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['name']) . $status; ?></h5>
                                    <?php
                                    $description = htmlspecialchars($event['description']);
                                    $words = explode(' ', $description);
                                    $shortDescription = implode(' ', array_slice($words, 0, 20));
                                    ?>
                                    <p class="card-text">
                                        <span class="short-description"><?php echo $shortDescription; ?>...</span>
                                        <a href="user-event_details.php?id=<?php echo $event
['id']; ?>" class="read-more">Read More</a>
</p>
</div>
<div class="card-footer">
<?php if ($event['approved'] == 1): ?>
    <button class="btn btn-success btn-sm">Approved</button>
<?php else: ?>
    <a href="invite-users.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">Invite Users</a>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<div class="col-md-4">
<ul class="nav nav-tabs" id="myTab" role="tablist">
<li class="nav-item" role="presentation">
<a class="nav-link active" id="invites-tab" href="invite-display.php" role="tab" aria-controls="invites" aria-selected="true">Invites</a>
</li>
</ul>
<div class="tab-content" id="myTabContent">
<div class="tab-pane fade show active" id="invites" role="tabpanel" aria-labelledby="invites-tab">
<!-- Invites content goes here -->
<div class="invite-card">
<h5>Invites Received</h5>
<?php
if (!empty($invites)) {
foreach ($invites as $inviteId) {
$event_info_query = $mysqli->prepare("SELECT name FROM events WHERE id = ?");
$event_info_query->bind_param('i', $inviteId);
$event_info_query->execute();
$event_info_result = $event_info_query->get_result();
$event_info = $event_info_result->fetch_assoc();
echo '<p>You have been invited to the event: ' . htmlspecialchars($event_info['name']) . '</p>';
echo '<a href="accept_invite.php?invited_user_id=' . $inviteId . '" class="btn btn-success btn-sm">Accept</a>';
echo ' ';
echo '<a href="reject_invite.php?invited_user_id=' . $inviteId . '" class="btn btn-danger btn-sm">Reject</a>';
}
} else {
echo '<p>No invites yet.</p>';
}
?>
</div>
</div>
</div>
</div>
</div>

</div>
<?php
// Check if there is a success message
if (isset($_SESSION['success_message'])) {
echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';

// Clear the success message to avoid displaying it again on page refresh
unset($_SESSION['success_message']);
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
