<?php
require('config.php');
include 'headerr.php';
$id = $_GET["id"];

if (isset($_POST["submit"])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $usertype = $_POST['usertype']; 
    

    $sql = "UPDATE `crud` SET `first_name`='$first_name', `last_name`='$last_name', `email`='$email', `gender`='$gender', `username`='$username', `usertype`='$usertype' WHERE id = $id"; // Updated SQL query

    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: index.php?msg=Data updated successfully");
        exit();
    } else {
        echo "Failed: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <style>
        .card-container {
            max-width: 80%;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #1C1D3C;
            color: white;
            padding: 10px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-weight: bold;
        }
    </style>

    <title>Edit User</title>
</head>

<body>
    <div class="card-container">
        <div class="card-header">
            <h3 class="text-center">Edit User Information</h3>
        </div>
        <div class="card-body">
            <?php
            $sql = "SELECT * FROM `crud` WHERE id = $id LIMIT 1";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            ?>

            <form action="" method="post">
                <div class="mb-3">
                    <label class="form-label">Username:</label>
                    <input type="text" class="form-control" name="username"
                        value="<?php echo $row['username'] ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">User Type:</label>
                    <select class="form-select" name="usertype">
                        <option value="user" <?php echo ($row['usertype'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($row['usertype'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">First Name:</label>
                    <input type="text" class="form-control" name="first_name"
                        value="<?php echo $row['first_name'] ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Name:</label>
                    <input type="text" class="form-control" name="last_name"
                        value="<?php echo $row['last_name'] ?>">
                </div>

                <!-- <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="password" class="form-control" name="password"
                        value="
                   /* <?php echo $row['password'] ?>*
                </div> -->

                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $row['email'] ?>">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Gender:</label>
                    <div>
                        <input type="radio" class="form-check-input" name="gender" id="male" value="male"
                            <?php echo ($row["gender"] == 'male') ? "checked" : ""; ?>>
                        <label for="male" class="form-input-label">Male</label>
                    </div>
                    <div>
                        <input type="radio" class="form-check-input" name="gender" id="female" value="female"
                            <?php echo ($row["gender"] == 'female') ? "checked" : ""; ?>>
                        <label for="female" class="form-input-label">Female</label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success" name="submit">Update</button>
                    <a href="index.php" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>

</body>

</html>
