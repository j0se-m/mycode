<?php
ob_start(); // Start output buffering

session_start();
include 'config.php';

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: userLogin.php");
    exit();
}

// Assuming you have a MySQLi connection established in 'config.php'
$username = $_SESSION['username'];

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's first name and profile picture from the database
$stmt = $conn->prepare("SELECT first_name, profile_picture FROM crud WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($firstName, $profilePicture);

// Check if user exists
if ($stmt->fetch()) {
    // User found, $firstName and $profilePicture now contain the user's data
    // If profile picture is null, set a default image
    if (empty($profilePicture)) {
        $profilePicture = 'path/to/default/profile_picture.png'; // Set a path to a default profile picture
    }
} else {
    // Handle error if user is not found
    die("User not found in the database.");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .custom-navbar {
            background-color: #1C1D3C !important;
            font-family: "Times New Roman", Times, serif;
            font-size: 18px;
            line-height: 1.7em;
            color: #333;
            font-weight: normal;
            font-style: normal;
            z-index: 1000;
        }
        .custom-navbar .navbar-nav .nav-link {
            color: whitesmoke !important; 
            text-transform: uppercase;
            margin-right: 15px; 
        }
        .custom-navbar .navbar-brand {
            margin-left: 70px;
        }
        .custom-navbar .navbar-nav .profile-img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            object-fit: cover;
            margin-left: 10px;
        }
        .content {
            padding: 20px;
            margin-left: 200px; /* Width of sidebar */
        }
        .custom-footer {
            background-color: #1C1D3C !important;
            color: whitesmoke;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px); /* Adjusting for sidebar width */
            padding: 10px 0;
            margin-left: 250px; /* Adjusting for sidebar width */
        }
        .sidebar {
            height: 100%;
            background-color: whitesmoke;
            padding-top: 20px;
            color: black;
            position: fixed;
            top: 56px; /* Height of navbar */
            left: 0;
            width: 200px; /* Fixed width for the sidebar */
            overflow-x: hidden;
        }
        .sidebar .nav-link {
            color: black !important; 
            text-transform: uppercase;
            margin-right: 15px; 
        }
        .sidebar .nav-item {
            padding: 12px 15px;
            text-decoration: none;
        }
        .sidebar .nav-item a {
            text-decoration: none;
            color: black;
            font-size: 16px;
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="http://localhost/Header/events.php">
                <img src="images/logo.png" alt="Zetech University" width="auto" height="auto" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center">
                    <li class="nav-item">
                        <?php if ($profilePicture): ?>
                            <a href="user-profile-admin.php">
                                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="profile-img">
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link">Hi, <?php echo htmlspecialchars($firstName); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a>
            </li> 
            <li class="nav-item">
                <a href="my-event-admin.php"><i class="fas fa-user"></i> My Events</a>
            </li>
            <li class="nav-item">
                <a href="admin-add-event.php"><i class="fas fa-plus-circle"></i> Post Event</a>
            </li> 
            <li class="nav-item">
                <a href="invite-display-admin.php"><i class="fas fa-envelope"></i> View Invites</a>
            </li> 
            <li class="nav-item">
                <a href="events-request-admin.php"><i class="fas fa-clipboard-list"></i> Event Requests</a>
            </li>
            <li class="nav-item">
                <a href="invite-received-admin.php"><i class="fas fa-inbox"></i> Invites Received</a>
            </li>
            <li class="nav-item">
                <a href="index.php"><i class="fas fa-users"></i> Users</a>
            </li>
        </ul>
    </div>
    
    <div class="content">
        <!-- Content goes here -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
