<?php
include 'user-nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'approved'; // Default to approved events
$items_per_page = isset($_POST['items_per_page']) ? (int)$_POST['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

// Define the query based on the filter
$sql_count = "SELECT COUNT(*) as total FROM events WHERE approved = 1"; // Count only approved events
$sql = "SELECT events.*, crud.first_name FROM events INNER JOIN crud ON events.user_id = crud.id WHERE events.approved = 1";

$count_result = $conn->query($sql_count);
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];
$total_pages = max(ceil($total_items / $items_per_page), 1);

// Adjust the page number if it exceeds the total number of pages
if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $items_per_page;
$sql .= " ORDER BY created_at DESC LIMIT $items_per_page OFFSET $offset";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .content-container {
            margin-left: 0px; 
            padding: 60px;
            flex: 1;
        }
        .form-control {
            width: 100px;
        }
        .container {
            margin-top: 30px;
            margin-left: 0px;
            margin-right: 10px; /* Added margin to place content 5px from the sidebar */
        }
        .btn-primary {
            background-color: #1C1D3C;
        }
        .btn-edit, .btn-delete {
            margin-right: 5px; /* Adjust margin between buttons */
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .pagination {
            justify-content: center;
        }
        .filter-form {
            display: flex;
            align-items: flex-end;
        }
        .filter-form .form-group {
            margin-right: 10px;
            margin-bottom: 0;
        }
        .filter-form button {
            margin-bottom: 0;
        }
        .table-wrapper {
            height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="content-container">
    <div class="container">
        <form method="POST" class="filter-form mb-3">
            <!-- <div class="form-group">
                <label for="filter">Filter Events:</label>
                <select name="filter" id="filter" class="form-control">
                    <option value="approved" <?php echo $filter == 'approved' ? 'selected' : ''; ?>>Approved Events</option>
                    <option value="disapproved" <?php echo $filter == 'disapproved' ? 'selected' : ''; ?>>Disapproved Events</option>
                </select>
            </div> -->
            <div class="form-group">
                <label for="items_per_page">Items Per Page:</label>
                <input type="number" name="items_per_page" id="items_per_page" class="form-control" value="<?php echo $items_per_page; ?>" min="1" max="100">
            </div>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </form>

        <?php
        if ($result->num_rows > 0) {
            echo '<div class="table-wrapper">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="thead-dark">';
            echo '<tr>';
            echo '<th>Event</th>';
            echo '<th>Description</th>';
            echo '<th>Posted By</th>';
            echo '<th>Date</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><a href="user-readmore.php?id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></td>';
                echo '<td>' . substr(htmlspecialchars($row['description']), 0, 50) . '...</td>'; // Changed the length to 50 characters
                echo '<td>' . htmlspecialchars($row['first_name']) . '</td>';
                echo '<td>' . date('F j, Y', strtotime($row['created_at'])) . '</td>';
                echo '<td class="action-buttons">';
                echo '<a href="user-readmore.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">Read More</a>';
                
                // Check if the event is posted by the logged-in user
                if ($row['user_id'] == $user_id) {
                    // Only show Edit and Delete buttons if the event is not approved
                    if ($row['approved'] != 1) {
                        echo '<a href="edit-event.php?id=' . $row['id'] . '" class="btn btn-warning btn-edit btn-sm">Edit</a>';
                        echo '<a href="delete-event.php?id=' . $row['id'] . '" class="btn btn-danger btn-delete btn-sm" onclick="return confirm(\'Are you sure you want to delete this post?\')">Delete</a>';
                    }
                }
                
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            // Pagination links
            echo '<nav>';
            echo '<ul class="pagination">';
            if ($page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '&items_per_page=' . $items_per_page . '&filter=' . $filter . '">Previous</a></li>';
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                echo '<a class="page-link" href="?page=' . $i . '&items_per_page=' . $items_per_page . '&filter=' . $filter . '">' . $i . '</a>';
                echo '</li>';
            }
            if ($page < $total_pages) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '&items_per_page=' . $items_per_page . '&filter=' . $filter . '">Next</a></li>';
            }
            echo '</ul>';
            echo '</nav>';
        } else {
            echo '<div class="alert alert-info">No events found</div>';
        }
        $conn->close();
        ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php echo "<br>";
// include 'footer.php';
?>
