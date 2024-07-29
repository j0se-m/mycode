<?php
include 'user-nav.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

// Initialize search variables
$search_event = isset($_GET['search_event']) ? $_GET['search_event'] : '';
$search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';

// Pagination configuration
$items_per_page = isset($_GET['items_per_page']) ? $_GET['items_per_page'] : 7;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Prepare the SQL query for counting the total number of matching records
$count_sql = "SELECT COUNT(*) AS total FROM event_requests 
              INNER JOIN events ON event_requests.event_id = events.id 
              WHERE event_requests.user_id = ?";

$bind_params = "i";
$bind_values = [$user_id];

if ($search_event != '') {
    $count_sql .= " AND events.name LIKE ?";
    $search_event_param = "%" . $search_event . "%";
    $bind_params .= "s";
    $bind_values[] = $search_event_param;
}
if ($search_status != '') {
    $count_sql .= " AND event_requests.status = ?";
    $bind_params .= "s";
    $bind_values[] = $search_status;
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($bind_params, ...$bind_values);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Prepare the SQL query with search filters and pagination
$sql = "SELECT event_requests.*, events.name AS event_name, events.id AS event_id
        FROM event_requests 
        INNER JOIN events ON event_requests.event_id = events.id 
        WHERE event_requests.user_id = ?";

$bind_params = "i";
$bind_values = [$user_id];

if ($search_event != '') {
    $sql .= " AND events.name LIKE ?";
    $search_event_param = "%" . $search_event . "%";
    $bind_params .= "s";
    $bind_values[] = $search_event_param;
}
if ($search_status != '') {
    $sql .= " AND event_requests.status = ?";
    $bind_params .= "s";
    $bind_values[] = $search_status;
}

$sql .= " LIMIT ?, ?";
$bind_params .= "ii";
$bind_values[] = $offset;
$bind_values[] = $items_per_page;

$stmt = $conn->prepare($sql);
$stmt->bind_param($bind_params, ...$bind_values);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            flex: 1;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-inline {
            margin-bottom: 20px;
        }
        .form-control {
            width: 200px;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #CB6015;
            border-color: #CB6015;
            color: #041E42 !important;
            font-weight: bold;
            height: 38px;
        }
        .btn-primary:hover {
            background-color: #041E42;
            border-color: #041E42;
            color: #CB6015 !important;
        }
        .table-wrapper {
            max-height: 400px; /* Adjust as needed */
            overflow-y: auto;
        }
        .table-striped {
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-striped th, .table-striped td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .table-striped th {
            background-color: #f0f0f0;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4 mb-4">My Requests</h2>

    <!-- Search form -->
    <form class="form-inline mb-4" id="searchForm" method="get" action="">
        <input type="text" id="search_event" name="search_event" class="form-control mr-2" placeholder="Search by event name" value="<?php echo htmlspecialchars($search_event); ?>">
        <select id="search_status" name="search_status" class="form-control mr-2">
            <option value="">Search by status</option>
            <option value="approved" <?php echo $search_status == 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="pending" <?php echo $search_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="rejected" <?php echo $search_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <label for="items_per_page" class="mr-2">Items per page:</label>
        <select id="items_per_page" name="items_per_page" class="form-control mr-2">
            <option value="5" <?php echo $items_per_page == 5 ? 'selected' : ''; ?>>5</option>
            <option value="10" <?php echo $items_per_page == 10 ? 'selected' : ''; ?>>10</option>
            <option value="15" <?php echo $items_per_page == 15 ? 'selected' : ''; ?>>15</option>
            <option value="20" <?php echo $items_per_page == 20 ? 'selected' : ''; ?>>20</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Display requests -->
    <div id="results">
        <?php if ($result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Request Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="clickable-row" data-href="user-readmore.php?id=<?php echo $row['event_id']; ?>">
                                <td><a href="user-readmore.php?id=<?php echo $row['event_id']; ?>"><?php echo htmlspecialchars($row['event_name']); ?></a></td>
                                <td><?php echo htmlspecialchars($row['attend_text']); ?></td>
                                <td>
                                    <?php
                                    if (isset($row['status'])) {
                                        // Check the status of the request
                                        if ($row['status'] == "approved") {
                                            echo '<p class="card-text">Your request to attend this event has been approved</p>';
                                        } elseif ($row['status'] == "pending") {
                                            echo '<p class="card-text">Pending</p>';
                                        } else {
                                            echo '<p class="card-text">Rejected</p>';
                                        }
                                    } else {
                                        echo '<p class="card-text">Not Available</p>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?search_event=<?php echo urlencode($search_event); ?>&search_status=<?php echo urlencode($search_status); ?>&items_per_page=<?php echo urlencode($items_per_page); ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php else: ?>
            <p>No requests found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // Handle row click event to navigate to the corresponding user-readmore page
    document.querySelectorAll('.clickable-row').forEach(function(row) {
        row.addEventListener('click', function() {
            window.location.href = this.getAttribute('data-href');
        });
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
