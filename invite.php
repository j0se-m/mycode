<?php
require('config.php');
include 'headerr.php';

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $sql = "SELECT * FROM events WHERE id = $event_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        ?>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $event['name']; ?></h5>
                            <p class="card-text"><?php echo nl2br($event['description']); ?></p>
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger">Delete</a>
                            <?php if ($event['approved'] == 0): ?>
                                <a href="approve_event.php?id=<?php echo $event['id']; ?>" class="btn btn-success">Approve</a>
                            <?php else: ?>
                                <a href="disapprove_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">Disapprove</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h3>Users</h3>
                    <div class="form-group">
                        <label for="search_user">Search Username:</label>
                        <input type="text" name="search_user" id="search_user" class="form-control" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" id="select_all"> Select All</label>
                    </div>
                    <form method="POST" action="send_invite.php">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <button type="submit" id="send_invite" class="btn btn-primary mb-2">Send Invite</button>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="user_table_body">
                                <?php
                                // Fetch and display all usernames initially
                                $sql_users = "SELECT * FROM crud";
                                $result_users = $conn->query($sql_users);
                                if ($result_users->num_rows > 0) {
                                    while($row = $result_users->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['username'] . "</td>";
                                        echo "<td><input type='checkbox' class='user_checkbox' name='usernames[]' value='" . $row['username'] . "'></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No users found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "Event not found";
    }
} else {
    echo "Invalid request";
}

$conn->close();
include 'footer.php';
?>

<script>
    // Select all checkbox functionality
    document.getElementById('select_all').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.user_checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });

    // Function to fetch users matching the search keyword
    function fetchUsers() {
        var searchKeyword = document.getElementById('search_user').value.trim();
        var userTableBody = document.getElementById('user_table_body');

        // Fetch users matching the search keyword
        fetch('fetch_users.php?keyword=' + encodeURIComponent(searchKeyword))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(users => {
                // Populate table with search results
                var html = '';
                if (users.length > 0) {
                    users.forEach(user => {
                        html += '<tr>';
                        html += '<td>' + user.username + '</td>';
                        html += '<td><input type="checkbox" class="user_checkbox" name="usernames[]" value="' + user.username + '"></td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="2">No users found.</td></tr>';
                }
                userTableBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching users:', error);
            });
    }

    // Search input change event
    var debounceTimer;
    document.getElementById('search_user').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchUsers, 300);
    });
</script>
