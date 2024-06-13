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

// Fetch users for invite selection
$users_query = $mysqli->query("SELECT id, username FROM crud WHERE id != {$user['id']}");
$users = [];
while ($row = $users_query->fetch_assoc()) {
    $users[] = $row;
}

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

// Process invite form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invite_button'])) {
    $event_id = $_POST['event_id'];
    $invite_user_ids = $_POST['invite_user_id'];

    if (!empty($invite_user_ids)) {
        $invite_messages = [];

        foreach ($invite_user_ids as $invite_user_id) {
            // Check if the user to invite exists in the CRUD table
            $invite_query = $mysqli->prepare("SELECT id FROM event_invites WHERE event_id = ? AND invited_user_id = ?");
            $invite_query->bind_param('ii', $event_id, $invite_user_id);
            $invite_query->execute();
            $invite_result = $invite_query->get_result();

            if ($invite_result->num_rows > 0) {
                // User has already been invited
                $invite_messages[] = "User ID $invite_user_id has already been invited.";
            } else {
                // User not invited yet, proceed with sending invite
                $insert_invite_query = $mysqli->prepare("INSERT INTO event_invites (event_id, user_id, invited_user_id, status) VALUES (?, ?, ?, 'pending')");
                $insert_invite_query->bind_param('iii', $event_id, $user['id'], $invite_user_id);
                $insert_invite_query->execute();

                if ($insert_invite_query->affected_rows > 0) {
                    $invite_messages[] = "User ID $invite_user_id has been successfully invited.";
                } else {
                    $invite_messages[] = "Failed to invite User ID $invite_user_id.";
                }
            }
        }

        if (!empty($invite_messages)) {
            $invite_message = implode("<br>", $invite_messages);
        }
    } else {
        $invite_message = "No users selected for invitation.";
    }
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
            background-color: #f8f9fa;
        }
        .profile-header {
            text-align: center;
            margin: 20px 0;
            font-family: 'Roboto', sans-serif;
        }
        .profile-picture {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }
        .card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            color: #333;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .card-text {
            color: #666;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 120px; /* Limit the height of the description */
        }
        .invite-form {
            margin-top: 20px;
        }
        .invite-form select {
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .invite-form button {
            margin-top: 10px;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .invite-form button:hover {
            background-color: #0056b3;
        }
        .fade-out {
            animation: fadeOut 2s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($username); ?></h1>
        </div>
        <?php if (isset($invite_message)) : ?>
            <div class="alert alert-warning fade-out" role="alert">
                <?php echo $invite_message; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <!-- Event Cards Section -->
            <div class="col-md-8">
                <?php foreach ($events as $event): ?>
                    <?php
                    $status = $event['approved'] == 1 ? '' : '(pending)';
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['name']) . $status; ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">Posted on <?php echo htmlspecialchars($event['created_at']); ?></small>
                        </div>
                        <?php if ($event['approved'] == 0): ?>
                            <div class="card-footer">
                                <a href="user-edit.php" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete-event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Invite Cards Section -->
            <div class="col-md-4">
                <?php foreach ($events as $event): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Invite Users to <?php echo htmlspecialchars($event['name']); ?></h5>
                            <form method="post" class="invite-form">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <div>
                                    <?php foreach ($users as $user): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="invite_user_id[]" value="<?php echo $user['id']; ?>" id="user<?php echo $user['id']; ?>">
                                            <label class="form-check-label" for="user<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" name="invite_button">Invite</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fade out alert after 5 seconds
        setTimeout(function() {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade-out');
            }
        }, 5000);
    </script>
</body>
</html>
