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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            overflow: hidden;
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">My Events</h1>
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
                                <a href="user-event_details.php?id=<?php echo $event['id']; ?>" class="read-more">Read More</a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
