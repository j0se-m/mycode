<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title img="">Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-navbar {
            background-color: #1C1D3C !important;
            font-family: "Times New Roman", Times, serif;
            font-size: 18px;
            line-height: 1.7em;
            color: #333;
            font-weight: normal;
            font-style: normal;
            padding-right: 20px;
        }
        .custom-navbar .navbar-nav .nav-link {
            color: whitesmoke !important; 
            text-transform: uppercase;
            margin-right: 15px; 
        }
        .custom-navbar .navbar-brand {
           margin-left: 70px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="http://localhost/Header/welcome.php">
                <img src="images/logo.png" alt="Zetech University" width="auto" height="auto" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">
                  
                        <a  href="user-profile.php" class="nav-link">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="welcome.php">Events</a>
                    </li>
                    <ul class="navbar-nav mr-auto">
    <!-- Other menu items -->
    <li class="nav-item">
        <a class="nav-link" href="event-requests.php">Event Requests</a>
    </li>
    <li class="nav-item">
                        <a class="nav-link" href="my-requests.php">My Request</a>
                    </li>
</ul>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="user-post-event.php">Post Event</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    
                    
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
