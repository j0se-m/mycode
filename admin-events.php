<?php
include 'headerr.php';
include 'config.php';

$user_id = $_SESSION['user_id'];
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';
$search_keyword = isset($_POST['search_keyword']) ? $_POST['search_keyword'] : '';
$search_date = isset($_POST['search_date']) ? $_POST['search_date'] : '';

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

$sql = "SELECT DISTINCT events.id, events.name, events.description, events.created_at, crud.first_name 
        FROM events 
        INNER JOIN crud ON events.user_id = crud.id WHERE 1=1";

if ($filter == 'approved') {
    $sql .= " AND events.approved = 1";
} elseif ($filter == 'disapproved') {
    $sql .= " AND events.approved = 0";
}

if (!empty($search_keyword)) {
    $sql .= " AND (events.name LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%' 
              OR events.description LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%')";
}

if (!empty($search_date)) {
    $sql .= " AND DATE(events.created_at) = '" . mysqli_real_escape_string($conn, $search_date) . "'";
}

$sql .= " GROUP BY events.id ORDER BY events.created_at DESC";

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
        .btn-container form {
            display: inline;
            margin-right: 5px;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Live filter function
            $('#search_keyword, #search_date').on('input', function() {
                filterTable();
            });

            // Reset filters
            $('#reset_filters').click(function() {
                $('#search_keyword').val('');
                $('#search_date').val('');
                filterTable();
            });

            function filterTable() {
                var keyword = $('#search_keyword').val().toLowerCase();
                var date = $('#search_date').val();
                
                $('tbody tr').each(function() {
                    var row = $(this);
                    var name = row.find('td').eq(0).text().toLowerCase();
                    var description = row.find('td').eq(1).text().toLowerCase();
                    var eventDate = row.find('td').eq(3).text();

                    var matchesKeyword = (name.includes(keyword) || description.includes(keyword));
                    var matchesDate = (!date || eventDate.includes(date));

                    if (matchesKeyword && matchesDate) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            }
        });
    </script>
</head>
<body>

<div class="container">

    <!-- Search Form -->
    <div class="row mt-5">
        <div class="col-12">
            <form method="POST" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search_keyword" class="sr-only">Keyword</label>
                    <input type="text" name="search_keyword" id="search_keyword" class="form-control" placeholder="Search by keyword" value="<?php echo htmlspecialchars($search_keyword); ?>">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="search_date" class="sr-only">Date</label>
                    <input type="date" name="search_date" id="search_date" class="form-control" value="<?php echo htmlspecialchars($search_date); ?>">
                </div>
                <button type="button" id="reset_filters" class="btn btn-secondary mb-2 mr-2">Reset Filters</button>
                <button type="submit" class="btn btn-primary mb-2">Search</button>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="row">
        <div class="col-12 mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Posted By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $row['name'] . '</td>';
                            echo '<td>' . substr($row['description'], 0, 100) . '...</td>';
                            echo '<td>' . htmlspecialchars($row['first_name']) . '</td>';
                            echo '<td>' . date('F j, Y', strtotime($row['created_at'])) . '</td>';
                            echo '<td class="btn-container">';
                            echo '<a href="invite.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-2">Send Invite</a>';
                            echo '<a href="admin-readmore.php?id=' . $row['id'] . '" class="btn btn-secondary btn-sm">Read More</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No events found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
include 'footer.php';
?>
