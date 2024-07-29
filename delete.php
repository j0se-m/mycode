<?php
require('config.php');
$id = $_GET["id"];
$sql = "UPDATE `crud` SET `status` = 1 WHERE id = $id";
$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: index.php?msg=User archived successfully");
} else {
    echo "Failed: " . mysqli_error($conn);
}
?>
