<?php
include "headerr.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Personnel Data</title>
</head>
<body>
    <style>
        .mb-3, .table-dark th {
            margin-top: 5px;
            background-color: #1C1D3C;
        }
        .content {
            padding: 20px;
            margin-top: 6px;
            margin-bottom: 20px; /* Adjusting for navbar height */
            margin-left: 180px; /* Adjusting for sidebar width */
        }
    </style>

    <div class="content">
        <div class="container">
            <?php
            if (isset($_GET["msg"])) {
                $msg = $_GET["msg"];
                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                ' . $msg . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
            ?>
            <a href="add-new.php" class="btn btn-dark mb-3 mt-3">Add New</a>

            <table id="userTable" class="table table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM crud";
                    $result = mysqli_query($conn, $sql);
                    $row_number = 1; // Initialize the row number
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr id="row_<?php echo $row["id"]; ?>">
                            <td><?php echo $row_number++; ?></td>
                            <td><?php echo $row["first_name"] ?></td>
                            <td><?php echo $row["last_name"] ?></td>
                            <td><?php echo $row["email"] ?></td>
                            <td><?php echo $row["gender"] ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row["id"] ?>" class="link-dark"><i class="fa-solid fa-pen-to-square fs-5 me-3"></i></a>
                                <a href="javascript:void(0);" onclick="changeUserStatus(<?php echo $row["id"] ?>, <?php echo $row["status"] ?>)" class="link-dark">
                                    <?php if ($row["status"] == 0) { ?>
                                        <i class="fa-solid fa-check-circle fs-5 text-success"></i>
                                    <?php } else { ?>
                                        <i class="fa-solid fa-times-circle fs-5 text-danger"></i>
                                    <?php } ?>
                                </a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
 
    <script>
function changeUserStatus(id, currentStatus) {
    var confirmMsg = currentStatus == 0 ? "Are you sure you want to deactivate this user?" : "Are you sure you want to activate this user?";
    if (confirm(confirmMsg)) {
        // Send AJAX request to change user status
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Reload the page upon successful update
                    location.reload();
                } else {
                    console.error('Error: ' + xhr.status);
                }
            }
        };
        xhr.open('POST', 'change-status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('id=' + id + '&status=' + (currentStatus == 0 ? 1 : 0)); // Toggle status
    }
}
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
