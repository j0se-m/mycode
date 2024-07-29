<?php
ob_start();
session_start();

include 'config.php';

if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}

// Fetch user details
$username = $_SESSION['username'];
$user_query = $conn->prepare("SELECT id, email, first_name, last_name, profile_picture FROM crud WHERE username = ?");
$user_query->bind_param('s', $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title>Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .custom-navbar {
            background-color: #1C1D3C !important;
            font-family: "Times New Roman", Times, serif;
            font-size: 18px;
            line-height: 1.7em;
            color: #333;
            font-weight: normal;
            font-style: normal;
        }
        .custom-navbar .navbar-nav .nav-link {
            color: whitesmoke !important;
            text-transform: uppercase;
            margin-right: 15px;
        }
        .custom-navbar .navbar-brand {
            margin-left: 70px;
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
        .sidebar a {
            color: black;
            text-transform: uppercase;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar .submenu a {
            padding-left: 40px;
        }
        .sidebar a:hover {
            background-color: #333;
        }
        .content {
            padding: 20px;
            margin-top: 10px;
            margin-left: 95px;
        }
        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="http://localhost/Header/user-home.php">
                <img src="images/logo.png" alt="Zetech University" width="auto" height="auto" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'path_to_default_profile_picture.jpg'); ?>" alt="Profile Picture" class="profile-picture">
                    </li>
                    <li class="nav-item">
                        <a href="user-profile.php" class="nav-link">Hi, <?php echo htmlspecialchars($user['first_name']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <a href="user-profile.php"><i class="bi bi-person"></i> Profile</a>
        <a href="#eventsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="bi bi-calendar-event"></i> Events</a>
        <div id="eventsSubmenu" class="collapse submenu">
            <a href="user-home.php"><i class="bi bi-journal-text"></i> View Events</a>
            <a href="user-post-event.php"><i class="bi bi-pencil-square"></i> Post Events</a>
            <a href="user-request.php"><i class="bi bi-arrow-clockwise"></i> Request to Attend</a>
        </div>
        <!-- <a href="invite-received.php"><i class="bi bi-inbox"></i> Invitation</a> -->
        <a href="my-requests.php"><i class="bi bi-journal-check"></i> My Requests</a>
        <a href="event-requests.php"><i class="bi bi-journal-check"></i> Events Requests</a>
        
                <a href="invite-display.php"><i class="fas fa-envelope"></i> View Invites</a>
         
    </div>

    <div class="content">
        <!-- Your content here -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
