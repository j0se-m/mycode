<?php
include 'headerr.php';

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
$event_query = $mysqli->prepare("SELECT id, name, description FROM events WHERE user_id = ?");
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

// Function to truncate text
function truncate($text, $limit = 85) {
    return mb_strimwidth($text, 0, $limit, "...");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title>Zetech University</title>
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
        .profile-header {
            text-align: center;
            margin: 12px 0;
        }
        .profile-details {
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
        .truncate {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="profile-details">
        <h2>Your Events</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Event Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><a href="event-details.php?id=<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['name']); ?></a></td>
                        <td class="truncate"><?php echo htmlspecialchars(truncate($event['description'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="2" class="text-center">No events found.</td>
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
