<?php
include 'headerr.php';
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: userLogin.php");
    exit();
}
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
        .container{
            margin-bottom:19px ;
        }
        .content{
            margin-left: 200px;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="row">
      
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title"> Post Event</h5>
                        <p class="card-text">Click here to post a new event.</p>
                        <a href="admin-add-event.php" class="btn btn-primary">Post Event</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title"> View My events</h5>
                        <p class="card-text">Click here view your events.</p>
                        <a href="user-profile-admin.php" class="btn btn-primary">View My  Event</a>
                    </div>
                </div>
            </div>


            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">View </h5>
                        <p class="card-text">Click here to view the invites you sent</p>
                        <a href="invite-display-admin.php" class="btn btn-primary">View Invites</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Event Requests</h5>
                        <p class="card-text">View those  requested to join your events.</p>
                        <a href="events-request-admin.php" class="btn btn-primary">Event Requests</a>
                    </div>
                </div>
            </div>
         
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Invites Received</h5>
                        <p class="card-text">Click here to view the invites received.</p>
                        <a href="invite-received-admin.php" class="btn btn-primary">Invites Received</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
