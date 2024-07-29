<?php
include 'headerr.php';

if (!isset($_SESSION['username'])) {
    header("location: userLogin.php");
    exit();
}

// Pagination variables
$limit = 10; // Number of posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build the where clause and parameters
$whereClause = "";
$params = array();

if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $whereClause .= "(events.name LIKE ? OR events.description LIKE ? OR events.location LIKE ? OR CONCAT(crud.first_name, ' ', crud.last_name) LIKE ?) AND ";
    $keyword = '%' . $_GET['keyword'] . '%';
    $params = array_fill(0, 4, $keyword);
}

if (isset($_GET['date']) && !empty(trim($_GET['date']))) {
    $whereClause .= "events.date = ? AND ";
    $params[] = $_GET['date'];
}

if (isset($_GET['status']) && ($_GET['status'] === 'approved' || $_GET['status'] === 'pending')) {
    $whereClause .= "events.approved = ? AND ";
    $params[] = $_GET['status'] === 'approved' ? 1 : 0;
}

$whereClause = rtrim($whereClause, "AND ");

// Get the total number of records for pagination
$countSql = "SELECT COUNT(*) as total FROM events JOIN crud ON events.user_id = crud.id";
if (!empty($whereClause)) {
    $countSql .= " WHERE $whereClause";
}
$countStmt = $conn->prepare($countSql);
if (count($params) > 0) {
    $countStmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch the events for the current page
$sql = "SELECT events.id, events.name, events.description, events.date, events.location, events.approved, 
        CONCAT(crud.first_name, ' ', crud.last_name) AS full_name
        FROM events
        JOIN crud ON events.user_id = crud.id";

if (!empty($whereClause)) {
    $sql .= " WHERE $whereClause";
}
$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title>Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .truncate {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

<div class="content">
    <div class="container mt-5">
        <a class="btn btn-primary" href="admin-add-event.php" role="button">Post</a>
        <button type="button" onclick="selectAll()" class="btn btn-secondary">Select All</button>
        <button type="submit" name="approve_selected" class="btn btn-primary" form="eventsForm">Approve Selected</button>
        <button type="submit" name="disapprove_selected" class="btn btn-warning" form="eventsForm">Disapprove Selected</button>

        <form method="GET" class="mt-3 mb-5">
            <div class="form-row d-flex align-items-center">
                <div class="col-xl-3 col-md-3 col-sm-12 mb-2 me-2">
                    <input type="text" class="form-control" placeholder="Keyword" name="keyword" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                </div>
                <div class="col-xl-2 col-md-3 col-sm-12 mb-2 me-2">
                    <input type="date" class="form-control" placeholder="Date" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                </div>
                <div class="col-xl-2 col-md-3 col-sm-12 mb-2 me-2">
                    <select class="form-control" name="status">
                        <option value="">Select Status</option>
                        <option value="approved" <?php echo isset($_GET['status']) && $_GET['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-2 col-sm-6 mb-2 me-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-xl-1 col-md-1 col-sm-6 mb-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
                </div>
            </div>
        </form>

        <form method="POST" action="bulk_action.php" id="eventsForm">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $counter = $offset + 1;
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['approved'] == 1 ? 'Approved' : 'Pending';
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td><a href=\"admin-readmore.php?id=" . htmlspecialchars($row["id"]) . "\">" . htmlspecialchars(truncateDescription($row["name"])) . "</a></td>";
                            echo "<td class='truncate'>" . htmlspecialchars(truncateDescription($row["description"])) . "</td>";
                            echo "<td>" . htmlspecialchars($row["location"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                            echo "<td>" . htmlspecialchars($status) . "</td>";
                            echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                            echo "<td><input type=\"checkbox\" name=\"event_ids[]\" value=\"" . htmlspecialchars($row["id"]) . "\"></td>";
                            echo "</tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='8'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>

        <!-- Pagination Controls -->
        <nav>
            <ul class="pagination">
                <?php
                if ($page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">First</a></li>';
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';
                }

                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }

                if ($page < $totalPages) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>';
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">Last</a></li>';
                }
                ?>
            </ul>
        </nav>

    </div>
</div>

<script>
    function selectAll() {
        var checkboxes = document.getElementsByName('event_ids[]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = true;
        });
    }

    function resetFilters() {
        document.getElementsByName('keyword')[0].value = '';
        document.getElementsByName('date')[0].value = '';
        document.getElementsByName('status')[0].selectedIndex = 0;
        window.location.href = 'events.php';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
function truncateDescription($text, $maxLength = 26) {
    if (strlen($text) <= $maxLength) {
        return $text;
    }
    $truncatedText = substr($text, 0, $maxLength) . '...';
    return $truncatedText;
}
?>
