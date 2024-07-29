<?php
include 'user-nav.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Include database configuration


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

// Pagination parameters
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

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

$sql .= " ORDER BY r.request_time DESC LIMIT ?, ?";

// Prepare and bind the SQL statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

if ($search != '' && $status_filter != '') {
    $search_param = "%" . $search . "%";
    $stmt->bind_param('issii', $user_id, $search_param, $status_filter, $offset, $records_per_page);
} elseif ($search != '') {
    $search_param = "%" . $search . "%";
    $stmt->bind_param('isii', $user_id, $search_param, $offset, $records_per_page);
} elseif ($status_filter != '') {
    $stmt->bind_param('isii', $user_id, $status_filter, $offset, $records_per_page);
} else {
    $stmt->bind_param('iii', $user_id, $offset, $records_per_page);
}

// Execute the statement
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

// Get the result of the executed statement
$result = $stmt->get_result();

// Fetch total number of records for pagination
$sql_total = "SELECT COUNT(*) AS total_records
              FROM event_requests r
              INNER JOIN events e ON r.event_id = e.id
              WHERE e.user_id = ?";
if ($search != '') {
    $sql_total .= " AND e.name LIKE ?";
}
if ($status_filter != '') {
    $sql_total .= " AND r.status = ?";
}

$stmt_total = $conn->prepare($sql_total);
if ($search != '' && $status_filter != '') {
    $stmt_total->bind_param('iss', $user_id, $search_param, $status_filter);
} elseif ($search != '') {
    $stmt_total->bind_param('is', $user_id, $search_param);
} elseif ($status_filter != '') {
    $stmt_total->bind_param('is', $user_id, $status_filter);
} else {
    $stmt_total->bind_param('i', $user_id);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_records = $result_total->fetch_assoc()['total_records'];
$total_pages = ceil($total_records / $records_per_page);

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
            margin: 0;
            padding: 0;
        }
        .content {
            margin-bottom: 0;
            margin-left: 220px; /* Adjust based on sidebar width */
            padding: 20px;
        }
        .container {
            margin-bottom: 2px;
            margin-top: 0;
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
        .table a {
            color: #007bff; /* Blue color */
            text-decoration: none;
        }
        .table a:hover {
            text-decoration: underline;
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
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
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
        .search-button {
            background-color: #041E42!important;
            color: #fff;
        }
        .reset-button {
            background-color: #dc3545!important;
            color: #fff;
        }
        .clickable-row {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="content">
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
                        <tr class="clickable-row" data-href="user-readmore.php?id=<?php echo $row['event_id']; ?>">
                            <td><a href="user-readmore.php?id=<?php echo $row['event_id']; ?>"><?php echo htmlspecialchars($row['event_name']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['attend_text']); ?></td>
                            <td><?php echo htmlspecialchars($row['requester_username']); ?></td>
                            <td><?php echo htmlspecialchars($row['request_time']); ?></td>
                            <td><span class="badge badge-<?php echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'disapproved' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td class="action-buttons">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <form action="approve_request.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $row['event_id']; ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form action="disapprove_request.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $row['event_id']; ?>">
                                        <button type="submit" class="btn btn-danger">Disapprove</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&status_filter=<?php echo htmlspecialchars($status_filter); ?>&page=<?php echo $page - 1; ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&status_filter=<?php echo htmlspecialchars($status_filter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&status_filter=<?php echo htmlspecialchars($status_filter); ?>&page=<?php echo $page + 1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', () => {
                window.location.href = row.getAttribute('data-href');
            });
        });
    </script>
    <!-- <div class="footer">
         <p>© 2024 Your Website. All rights reserved.</p> -->
    <!-- </div> - -->
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
