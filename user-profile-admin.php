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
$user_query = $mysqli->prepare("SELECT id, email, first_name, last_name, profile_picture FROM crud WHERE username = ?");
$user_query->bind_param('s', $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Handle profile picture upload
$message = ''; // Variable to store success or error messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $target_dir = "uploads/"; // Directory to save the uploaded file
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $timestamp = time(); // Current timestamp
    $new_filename = $timestamp . '.' . $file_extension; // New file name with timestamp
    $target_file = $target_dir . $new_filename;
    $uploadOk = 1;
    $error = '';

    // Check if image file is a valid image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_picture"]["size"] > 500000) { // Limit to 500KB
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message = "<div class='alert alert-danger'>Upload failed. Error: $error</div>";
    } else {
        // Try to upload file
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update profile picture in database
            $update_query = $mysqli->prepare("UPDATE crud SET profile_picture = ? WHERE username = ?");
            $update_query->bind_param('ss', $target_file, $username);
            if ($update_query->execute()) {
                $message = "<div class='alert alert-success'>Profile picture updated successfully.</div>";
                // Refresh user data to reflect changes
                $user['profile_picture'] = $target_file;
            } else {
                $message = "<div class='alert alert-danger'>Error updating profile picture in database.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
        }
    }
}

// Pagination setup
$items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 5;
$custom_items_per_page = isset($_GET['custom_items_per_page']) ? intval($_GET['custom_items_per_page']) : 0;
if ($custom_items_per_page > 0) {
    $items_per_page = $custom_items_per_page;
}

// Fetch events related to the user
$offset = (isset($_GET['page']) ? intval($_GET['page']) - 1 : 0) * $items_per_page;
$events_query = $mysqli->prepare("SELECT id, name, date, description, location, status FROM events WHERE user_id = ? LIMIT ?, ?");
$events_query->bind_param('iii', $user['id'], $offset, $items_per_page);
$events_query->execute();
$events_result = $events_query->get_result();
$events = $events_result->fetch_all(MYSQLI_ASSOC);

// Fetch total event count for pagination
$count_query = $mysqli->prepare("SELECT COUNT(*) AS total FROM events WHERE user_id = ?");
$count_query->bind_param('i', $user['id']);
$count_query->execute();
$count_result = $count_query->get_result();
$total_events = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_events / $items_per_page);

// Function to truncate text to a specified number of words
function truncateWords($text, $limit) {
    $words = explode(' ', $text);
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
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
            width: 40px;
            height: 40px;
            border: 5px solid #007bff;
        }
        .profile-details {
            margin-top: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            margin-left: 150px; /* Adjust based on your sidebar width */
            padding: 20px;
        }
        .online-status {
            font-weight: bold;
            color: green;
        }
        .offline-status {
            font-weight: bold;
            color: red;
        }
        .fade {
            animation: fadeOut 3s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        .action-buttons a {
            margin-right: 5px; /* Add some space between buttons */
            color: #ffffff; /* Ensure text color is white */
            text-decoration: none; /* Remove underline */
        }
        .action-buttons a.btn-info {
            background-color: #17a2b8; /* Consistent color for view button */
            border-color: #17a2b8;
        }
        .action-buttons a.btn-primary {
            background-color: #007bff; /* Consistent color for invite button */
            border-color: #007bff;
        }
        .action-buttons a.btn-info:hover,
        .action-buttons a.btn-primary:hover {
            opacity: 0.8; /* Slightly transparent on hover */
        }
        .event-link {
            text-decoration: none;
            color: #007bff; /* Link color */
        }
        .event-link:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <?php if ($message): ?>
                <div class="fade">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'uploads/default_profile_picture.jpg'); ?>" alt="Profile Picture" class="profile-picture">
                <h1><?php echo htmlspecialchars($username); ?></h1>
            </div>
            <div class="profile-details">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
            </div>
            <div class="upload-section mt-4">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="mt-3">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Edit Profile Picture:</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept=".jpg, .jpeg, .png, .gif" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
            <div class="table-responsive mt-4">
                <form method="GET" action="user-profile.php">
                    <div class="row mb-3">
                        <div class="col-auto">
                            <label for="items_per_page" class="form-label">Items per page:</label>
                            <select name="items_per_page" id="items_per_page" class="form-select">
                                <option value="5" <?php if ($items_per_page == 5) echo 'selected'; ?>>5</option>
                                <option value="10" <?php if ($items_per_page == 10) echo 'selected'; ?>>10</option>
                                <option value="20" <?php if ($items_per_page == 20) echo 'selected'; ?>>20</option>
                                <option value="50" <?php if ($items_per_page == 50) echo 'selected'; ?>>50</option>
                                <option value="custom" <?php if ($items_per_page == $custom_items_per_page) echo 'selected'; ?>>Custom</option>
                            </select>
                            <input type="number" name="custom_items_per_page" value="<?php echo $custom_items_per_page; ?>" class="form-control mt-2" placeholder="Enter custom number" min="1" max="1000" style="<?php echo $items_per_page != $custom_items_per_page ? 'display:none;' : ''; ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mt-4">Apply</button>
                        </div>
                    </div>
                </form>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><a href="event-details.php?id=<?php echo $event['id']; ?>" class="event-link"><?php echo htmlspecialchars($event['name']); ?></a></td>
                                <td><?php echo htmlspecialchars($event['date']); ?></td>
                                <td><?php echo htmlspecialchars(truncateWords($event['description'], 10)); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td><?php echo htmlspecialchars($event['status']); ?></td>
                                <td class="action-buttons">
                                    <!-- <a href="view-event.php?id=<?php echo $event['id']; ?>" class="btn btn-info">View</a> -->
                                    <a href="invite-users.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">Invite</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($total_pages > 1): ?>
                            <?php if ($offset > 0): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($offset / $items_per_page); ?>">Previous</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($i == ($offset / $items_per_page) + 1) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($offset + $items_per_page < $total_events): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo (($offset / $items_per_page) + 2); ?>">Next</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
