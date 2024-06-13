<?php
require 'config.php';
include 'headerr.php';

// Establish database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to fetch event requests
$sql = "SELECT r.id, e.name AS event_name, u.username, r.status 
        FROM event_requests r 
        JOIN events e ON r.event_id = e.id 
        JOIN crud u ON r.user_id = u.id 
        WHERE r.status = 'pending'";
$result = mysqli_query($conn, $sql);

// Check if query executed successfully
if (!$result) {
    die("Error fetching data: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Event Requests</title>
</head>
<body>
    <div class="container">
        <h2>Approve Event Requests</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['event_name']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <form action="approve-request.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="approve" class="btn btn-success">Approve</button>
                        </form>
                        <form action="approve-request.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
