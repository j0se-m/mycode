<?php
require('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id']) && isset($_POST['usernames'])) {
    $event_id = $_POST['event_id'];
    $usernames = $_POST['usernames'];

    foreach ($usernames as $username) {
        // Fetch the user ID from the username
        $user_sql = "SELECT id FROM crud WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'";
        $user_result = $conn->query($user_sql);

        if ($user_result->num_rows > 0) {
            $user_row = $user_result->fetch_assoc();
            $user_id = $user_row['id'];

            // Insert the invitation into the invitations table
            $invite_sql = "INSERT INTO invitations (event_id, user_id) VALUES ($event_id, $user_id)";
            if ($conn->query($invite_sql) === TRUE) {
                echo "Invite sent to $username<br>";
            } else {
                echo "Error sending invite to $username: " . $conn->error . "<br>";
            }
        } else {
            echo "User $username not found<br>";
        }
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>
