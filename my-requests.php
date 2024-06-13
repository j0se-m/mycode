<?php
include 'user-nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

// Initialize search variables
$search_event = isset($_GET['search_event']) ? $_GET['search_event'] : '';
$search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';

// Prepare the SQL query with search filters
$sql = "SELECT event_requests.*, events.name AS event_name 
        FROM event_requests 
        INNER JOIN events ON event_requests.event_id = events.id 
        WHERE event_requests.user_id = ?";

if ($search_event != '') {
    $sql .= " AND events.name LIKE ?";
    $search_event_param = "%" . $search_event . "%";
}
if ($search_status != '') {
    $sql .= " AND event_requests.status = ?";
}

$stmt = $conn->prepare($sql);

if ($search_event != '' && $search_status != '') {
    $stmt->bind_param("iss", $user_id, $search_event_param, $search_status);
} elseif ($search_event != '') {
    $stmt->bind_param("is", $user_id, $search_event_param);
} elseif ($search_status != '') {
    $stmt->bind_param("is", $user_id, $search_status);
} else {
    $stmt->bind_param("i", $user_id);
}

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
        }
        .container {
            flex: 1;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f9f9f9;
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
            color: #041E42!important;
            font-weight: bold;
            height: 38px;
        }
        .btn-primary:hover {
            background-color: #041E42;
            border-color: #041E42;
            color: #CB6015!important;
        }
        .table-striped {
            border-collapse: collapse;
        }
        .table-striped th, .table-striped td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .table-striped th {
            background-color: #f0f0f0;
        }
        footer {
            background-color: #0088cc;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4 mb-4">My Requests</h2>
    
    <form class="form-inline mb-4" method="get" action="">
        <input type="text" name="search_event" class="form-control mr-2" placeholder="Search by event name" value="<?php echo htmlspecialchars($search_event); ?>">
        <select name="search_status" class="form-control mr-2">
            <option value="">Search by status</option>
            <option value="approved" <?php echo $search_status == 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="pending" <?php echo $search_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="rejected" <?php echo $search_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
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
                        <tr>
                            <td><?php echo htmlspecialchars($row['event_name']); ?></td>
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
    <?php else: ?>
        <p>No requests found.</p>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php
include 'footer.php';
?>

</body>
</html>
