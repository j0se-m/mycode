<?php
include 'user-nav.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Include database configuration
// include 'config.php';

// Establish database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Check if reset button was pressed
if (isset($_GET['reset'])) {
    $search = '';
    $status_filter = '';
}

// Prepare SQL query
$sql = "SELECT e.id AS event_id, e.name AS event_name, r.attend_text, r.request_time, r.status, u.username AS requester_username
        FROM event_requests r
        INNER JOIN events e ON r.event_id = e.id
        INNER JOIN crud u ON r.user_id = u.id
        WHERE e.user_id = ?";

// Add search conditions if parameters are set
if ($search != '') {
    $sql .= " AND e.name LIKE ?";
}
if ($status_filter != '') {
    $sql .= " AND r.status = ?";
}

$sql .= " ORDER BY r.request_time DESC";

// Prepare and bind the SQL statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

if ($search != '' && $status_filter != '') {
    $search_param = "%" . $search . "%";
    $stmt->bind_param('iss', $user_id, $search_param, $status_filter);
} elseif ($search != '') {
    $search_param = "%" . $search . "%";
    $stmt->bind_param('is', $user_id, $search_param);
} elseif ($status_filter != '') {
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt->bind_param('i', $user_id);
}

// Execute the statement
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

// Get the result of the executed statement
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Requests</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .action-buttons form {
            display: inline-block;
            margin-right: 5px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        .footer {
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
            position: fixed;
            bottom: 0;
            left: 0;
        }
        .search-bar {
            background-color: #CB6015;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .search-button {
            background-color: #041E42!important;
            color: #fff;
        }
        .reset-button {
            background-color: #dc3545!important;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Event Requests</h2>
        <form class="search-bar" method="GET" action="">
            <input type="text" name="search" placeholder="Search by event name" value="<?php echo htmlspecialchars($search); ?>">
            <select name="status_filter">
                <option value="">Select Status</option>
                <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="approved" <?php if ($status_filter == 'approved') echo 'selected'; ?>>Approved</option>
                <option value="disapproved" <?php if ($status_filter == 'disapproved') echo 'selected'; ?>>Disapproved</option>
            </select>
            <button type="submit" class="search-button">Search</button>
            <button type="submit" name="reset" class="reset-button">Reset</button>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Request Message</th>
                    <th>Requester</th>
                    <th>Request Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['attend_text']); ?></td>
                        <td><?php echo htmlspecialchars($row['requester_username']); ?></td>
                        <td><?php echo htmlspecialchars($row['request_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="action-buttons">
                            <?php if ($row['status'] == 'pending'): ?>
                                <form action="approve-event.php" method="POST">
                                    <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                                    <button type="submit" class="btn btn-success" name="action" value="approve">Approve</button>
                                </form>
                                <form action="update-request-status.php" method="POST">
                                    <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                                    <button type="submit" class="btn btn-danger" name="action" value="disapprove">Disapprove</button>
                                </form>
                                <form action="delete-request.php" method="POST">
                                    <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                                    <button type="submit" class="btn btn-warning" name="delete">Delete</button>
                                </form>
                            <?php elseif ($row['status'] == 'approved'): ?>
                                <span class="badge badge-success">Approved</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Disapproved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">
        <?php include 'footer.php'; ?>
    </div>
</body>
</html>

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>
