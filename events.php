<?php
include 'config.php';
include 'headerr.php';

$whereClause = "";
$params = array();

if(isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $whereClause .= "(events.name LIKE ? OR events.description LIKE ? OR events.location LIKE ? OR CONCAT(crud.first_name, ' ', crud.last_name) LIKE ?) AND ";
    $keyword = '%' . $_GET['keyword'] . '%';
    $params = array_fill(0, 4, $keyword);
}

if(isset($_GET['date']) && !empty(trim($_GET['date']))) {
    $whereClause .= "events.date = ? AND ";
    $params[] = $_GET['date'];
}

if(isset($_GET['status']) && ($_GET['status'] === 'approved' || $_GET['status'] === 'pending')) {
    $whereClause .= "events.approved = ? AND ";
    $params[] = $_GET['status'] === 'approved' ? 1 : 0;
}

$whereClause = rtrim($whereClause, "AND ");

$sql = "SELECT events.id, events.name, events.description, events.date, events.location, events.approved, 
        CONCAT(crud.first_name, ' ', crud.last_name) AS full_name
        FROM events
        JOIN crud ON events.user_id = crud.id";

if(!empty($whereClause)) {
    $sql .= " WHERE $whereClause";
}

$stmt = $conn->prepare($sql);
if(count($params) > 0) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Events Table</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container mt-5">
    <a class="btn btn-primary" href="admin-add-event.php" role="button">Post</a>
    <a class="btn btn-primary" href="user-request.php" role="button">Request</a>

        <!-- <a class="btn btn-primary" href="index.php" role="button">Users</a> -->
        <button type="button" onclick="selectAll()" class="btn btn-secondary">Select All</button>
        <button type="submit" name="approve_selected" class="btn btn-primary">Approve Selected</button>
        <button type="submit" name="disapprove_selected" class="btn btn-warning">Disapprove Selected</button>
        <h2 class="mb-3">Events Table</h2>
       

        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-xl-3">
                    <input type="text" class="form-control" placeholder="Keyword" name="keyword" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                </div>

                <div class="col-md-3">
                    <input type="date" class="form-control" placeholder="Date" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="status">
                        <option value="">Select Status</option>
                        <option value="approved" <?php echo isset($_GET['status']) && $_GET['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-md-1">
    <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
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
                                $counter = 1;

                                while($row = $result->fetch_assoc()) {
                                    $status = $row['approved'] == 1 ? 'Approved' : 'Pending';

                                    echo "<tr>";
                                    echo "<td>" . $counter . "</td>";
                                    echo "<td><a href=\"admin-readmore.php?id=" . htmlspecialchars($row["id"]) . "\">" . htmlspecialchars(truncateDescription($row["name"])) . "</a></td>";
                                    // echo "<td class='truncate'>" . htmlspecialchars(truncateDescription($row["name"])) . "</td>";
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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
        function selectAll() {
            var checkboxes = document.getElementsByName('event_ids[]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
        }
      


</script>

<script>
function resetFilters() {
    document.getElementsByName('keyword')[0].value = '';
    document.getElementsByName('date')[0].value = '';
    document.getElementsByName('status')[0].selectedIndex = 0;

    document.getElementById('eventsForm').action = 'events.php';

    document.getElementById('eventsForm').submit();
}
</script>


    </script>
</body>
</html>

<?php
function truncateDescription($text, $maxLength =26) {
    if (strlen($text) <= $maxLength) {
        return $text;
    }
    $truncatedText = substr($text, 0, $maxLength) . '...';
    return $truncatedText;
}
//include 'footer.php';
?>
