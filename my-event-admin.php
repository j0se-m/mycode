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
        // Limit title and description to 35 words
        $title = str_word_count($event['name']) > 15 ? implode(' ', array_slice(explode(' ', $event['name']), 0, 35)) . '...' : $event['name'];
        $description = str_word_count($event['description']) > 10 ? implode(' ', array_slice(explode(' ', $event['description']), 0,10)) . '...' : $event['description'];
        
        $event['title'] = htmlspecialchars($title);
        $event['description'] = htmlspecialchars($description);
        
        $events[] = $event;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
            margin-left: 200px;
        }
        .table-responsive {
            /* Remove overflow-x: auto; to prevent horizontal scrolling */
            overflow-x: hidden;
        }
        .fixed-table {
            width: 100%; /* Allow table to expand based on content */
            table-layout: auto; /* Use auto table layout */
        }
        .fixed-table th, .fixed-table td {
            word-wrap: break-word; /* Allow wrapping for long words */
            white-space: normal; /* Preserve spaces and wrap text */
            border: 1px solid #dee2e6;
        }
        .fixed-table td a {
            text-decoration: none; /* Remove underline */
            color: blue ; /* Set link color to green */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <h2>My events</h2>
                <table class="table table-striped fixed-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><a href="invite-users-admin.php?event_id=<?php echo $event['id']; ?>"><?php echo $event['title']; ?></a></td>
                                <td><?php echo $event['description']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
