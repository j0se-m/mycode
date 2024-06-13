<?php
include 'user-nav.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-title {
            color: #333;
        }

        .card-text {
            color: #555;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">View Events</h5>
                        <p class="card-text">Click here to view upcoming events.</p>
                        <a href="user-home.php" class="btn btn-primary">View Events</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Post Event</h5>
                        <p class="card-text">Click here to post a new event.</p>
                        <a href="user-post-event.php" class="btn btn-primary">Post Event</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">View Invites</h5>
                        <p class="card-text">Click here to view the invited events.</p>
                        <a href="invite-display.php" class="btn btn-primary">View Invites</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Event Requests</h5>
                        <p class="card-text">Click here to view event requests.</p>
                        <a href="event-requests.php" class="btn btn-primary">Event Requests</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">My Requests</h5>
                        <p class="card-text">Click here to view your requests.</p>
                        <a href="my-requests.php" class="btn btn-primary">My Requests</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
