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
$event_query = $mysqli->prepare("SELECT id, name, description, created_at, approved FROM events WHERE user_id = ?");
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

// Fetch users for invite selection
$users_query = $mysqli->query("SELECT id, username FROM crud WHERE id != {$user['id']}");
$users = [];
while ($row = $users_query->fetch_assoc()) {
    $users[] = $row;
}

// Set the active event
$active_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : (count($events) > 0 ? $events[0]['id'] : null);
$active_event = null;
foreach ($events as $event) {
    if ($event['id'] == $active_event_id) {
        $active_event = $event;
        break;
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

// Fetch invited users for the active event
$invited_users_query = $mysqli->prepare("SELECT c.username, ei.id AS invite_id FROM event_invites ei JOIN crud c ON ei.invited_user_id = c.id WHERE ei.event_id = ?");
$invited_users_query->bind_param('i', $active_event_id);
$invited_users_query->execute();
$invited_users_result = $invited_users_query->get_result();

// Initialize array to store invited users
$invited_users = [];
while ($invited_user = $invited_users_result->fetch_assoc()) {
    $invited_users[] = [
        'username' => htmlspecialchars($invited_user['username']),
        'invite_id' => $invited_user['invite_id']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Users - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-J4U4hFUn9+XaKUMLRQKn8T8O8TQIXMwQ9WfBC5MlK27I7gSPhaX7lP3n/YIvF9vR9ZCHdfnt54bFdrPht3UE1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .profile-header {
            text-align: center;
            margin: 20px 0;
            font-family: 'Roboto', sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
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
            overflow: visible;
            text-overflow: ellipsis;
            max-height: auto; /* Limit the height of the description */
        }
        .invite-form {
            margin-top: 0px;
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
        .sidebarr {
            padding: 1rem;
            margin-top: 20px;
            margin-left: 200px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-height: 400px; /* Set maximum height for scrollability */
            overflow-y: auto; /* Enable vertical scrolling */
        }
        .main-content {
            margin-top: 0px;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
        .container {
            margin-top: 5px; /* Adjusted margin */
        }

        .invite-notification {
            width: 700px;
            margin: 0 auto;
            text-align: center;
            background-color: #CB6015; /* Changed background color */
            border-color: #CB6015;
            color: #333;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            animation: fadeOut 3s ease-in-out forwards;
            position: relative; /* Ensure position relative for z-index */
            z-index: 9999; /* Set a high z-index value */
        }

        /* Custom styles for invited users list */
        .invited-users-list {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .invited-user {
            display: flex;
            align-items: center;
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            max-width: 300px;
        }
        .invited-user-name {
            margin-left: 10px;
        }
        .cancel-invite-icon {
            color: #dc3545; /* Bootstrap danger color */
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($invite_message)) : ?>
        <div class="alert alert-warning invite-notification" role="alert">
            <?php echo $invite_message; ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-4">
            <div class="sidebarr">
                <h5 class="mt-4">Invite Users</h5>
                <form method="post" class="invite-form">
                    <input type="hidden" name="event_id" value="<?php echo $active_event_id; ?>">
                    
                    <!-- Search input for filtering users -->
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchUser" name="searchUser" placeholder="Search users...">
                    </div>
                    
                    <!-- Select All checkbox -->
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllUsers">
                        <label class="form-check-label" for="selectAllUsers">Select All</label>
                    </div>
                    
                    <div>
                        <?php foreach ($users as $user): ?>
                            <div class="form-check">
                                <input class="form-check-input invite-user-checkbox" type="checkbox" name="invite_user_id[]" value="<?php echo $user['id']; ?>" id="user<?php echo $user['id']; ?>">
                                <label class="form-check-label" for="user<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="invite_button" class="btn btn-primary">Invite</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="main-content">
                <?php if ($active_event): ?>
                    <!-- Existing event card -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($active_event['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($active_event['description']); ?></p>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">Posted on <?php echo htmlspecialchars($active_event['created_at']); ?></small>
                        </div>
                        <?php if ($active_event['approved'] == 0): ?>
                            <div class="card-footer">
                                <a href="user-edit.php" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete-event.php?id=<?php echo $active_event['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Display invited users -->
                    <?php if (!empty($invited_users)): ?>
                        <div class="mt-4">
                            <h5>Invited Users</h5>
                            <div class="invited-users-list">
                                <?php foreach ($invited_users as $invited_user): ?>
                                    <div class="invited-user">
                                        <i class="fas fa-times-circle cancel-invite-icon" data-invite-id="<?php echo $invited_user['invite_id']; ?>"></i>
                                        <div class="invited-user-name"><?php echo $invited_user['username']; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No event selected.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" integrity="sha512-SUG5c7QfoX75cLxeiETPTgDWlXunE9xg9I0E7R9sWW3gG4xDmEHZaW4Xo5PLOh4e+PGz9C2rJfJfLHrLh4kSYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    // Function to handle "Select All" checkbox
    document.getElementById('selectAllUsers').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.invite-user-checkbox');
        checkboxes.forEach(function(checkbox) {
            // Check if the checkbox should be checked based on visibility
            if (checkbox.closest('.form-check').style.display !== 'none') {
                checkbox.checked = this.checked;
            }
        }, this); // Pass `this` to maintain correct context
    });

    // Function to filter users based on search input
    document.getElementById('searchUser').addEventListener('input', function() {
        var searchKeyword = this.value.trim().toLowerCase();
        var checkboxes = document.querySelectorAll('.invite-user-checkbox');

        checkboxes.forEach(function(checkbox) {
            var label = checkbox.nextElementSibling.textContent.trim().toLowerCase();
            if (label.includes(searchKeyword)) {
                checkbox.parentElement.style.display = 'block';
            } else {
                checkbox.parentElement.style.display = 'none';
            }
        });

        // Ensure "Select All" checkbox is unchecked when filtering changes
        document.getElementById('selectAllUsers').checked = false;
    });

    // Ensure "Select All" checkbox state reflects dynamically checked checkboxes
    document.querySelectorAll('.invite-user-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var allChecked = true;
            document.querySelectorAll('.invite-user-checkbox').forEach(function(cb) {
                if (!cb.checked) {
                    allChecked = false;
                }
            });
            document.getElementById('selectAllUsers').checked = allChecked;
        });
    });

    // Cancel invite action
    document.querySelectorAll('.cancel-invite-icon').forEach(function(icon) {
        icon.addEventListener('click', function() {
            var inviteId = this.getAttribute('data-invite-id');

            // Perform AJAX request to cancel invite
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'cancel-invite.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Remove the canceled invite from the UI
                    var invitedUserElement = icon.closest('.invited-user');
                    invitedUserElement.remove();
                }
            };
            xhr.send('invite_id=' + encodeURIComponent(inviteId));
        });
    });

    // Automatically fade out invite notification after 3 seconds
    var inviteNotification = document.querySelector('.invite-notification');
    if (inviteNotification) {
        setTimeout(function() {
            inviteNotification.style.opacity = '0';
        }, 3000); // 3000 milliseconds = 3 seconds
    }
</script>

</body>
</html>
