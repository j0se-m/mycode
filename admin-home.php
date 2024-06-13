<?php
include 'config.php';
include 'headerr.php';

$sqlUsers = "SELECT COUNT(*) AS total_users FROM crud";
$resultUsers = $conn->query($sqlUsers);

$totalUsers = 0;
if ($resultUsers->num_rows > 0) {
    $rowUsers = $resultUsers->fetch_assoc();
    $totalUsers = $rowUsers["total_users"];
}

$sqlApproved = "SELECT COUNT(*) AS total_approved FROM events WHERE approved = '1'";
$resultApproved = $conn->query($sqlApproved);

$totalApproved = 0;
if ($resultApproved->num_rows > 0) {
    $rowApproved = $resultApproved->fetch_assoc();
    $totalApproved = $rowApproved["total_approved"];
}

$sqlPending = "SELECT COUNT(*) AS total_pending FROM events WHERE approved = '0'";
$resultPending = $conn->query($sqlPending);

$totalPending = 0;
if ($resultPending->num_rows > 0) {
    $rowPending = $resultPending->fetch_assoc();
}

$sqlLogins = "SELECT username, last_login FROM crud ORDER BY last_login DESC LIMIT 10";
$resultLogins = $conn->query($sqlLogins);

$logins = [];
if ($resultLogins->num_rows > 0) {
    while ($rowLogins = $resultLogins->fetch_assoc()) {
        $logins[] = $rowLogins;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zetech University Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: red;
        }

        .card-body {
            padding: 20px;
            border-color: red;
        }

        .card-title {
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 24px;
            font-weight: bold;
        }

        .content-area {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .row {
            width: 100%;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="content-area">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="card-title">Current Users</h4>
                            <p class="card-text"><?php echo $totalUsers; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="card-title">Pending Posts</h4>
                            <p class="card-text"><?php echo $totalPending; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="card-title">Approved Posts</h4>
                            <p class="card-text"><?php echo $totalApproved; ?></p>
                        </div>
                    </div>
                </div>

                <!-- New Event Tab Card -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="card-title">Events</h4>
                            <a href="events.php" class="btn btn-primary">Go to Events</a>
                        </div>
                    </div>
                </div>

               
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="card-title">View Users</h4>
                            <a href="index.php" class="btn btn-primary">Go to Users</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row table-container justify-content-center">
                <div class="col-lg-100% col-md-10 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Recent Login Sessions</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Last Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logins as $login): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($login['username']); ?></td>
                                                <td><?php echo htmlspecialchars($login['last_login']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($logins)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No recent login sessions</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
<?php
include 'footer.php';
?>