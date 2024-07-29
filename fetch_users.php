<?php
// Include your configuration file
require('config.php');

// Check if keyword is provided
if(isset($_GET['keyword'])) {
    // Get the keyword from the query string
    $keyword = $_GET['keyword'];

    // Prepare a SQL statement to search for users matching the keyword
    $sql = "SELECT * FROM crud WHERE username LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'";

    // Execute the SQL statement
    $result = $conn->query($sql);

    // Check if there are any results
    if ($result->num_rows > 0) {
        // Create an empty array to store the results
        $users = array();

        // Fetch rows one by one and add them to the array
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        // Convert the array to JSON and output it
        echo json_encode($users);
    } else {
        // If no results found, output an empty array as JSON
        echo json_encode(array());
    }
} else {
    // If no keyword provided, output an error message
    echo "No keyword provided";
}

// Close the database connection
$conn->close();
?>
