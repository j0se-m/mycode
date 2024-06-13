<?php
require('config.php');



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT id, username, first_name, usertype FROM `crud` WHERE username=? AND password=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['first_name'] = $row['first_name'];
        $_SESSION['usertype'] = $row['usertype'];
        
        $update_last_login_sql = "UPDATE crud SET last_login = NOW() WHERE username = ?";
        $stmt_update = mysqli_prepare($conn, $update_last_login_sql);
        mysqli_stmt_bind_param($stmt_update, "s", $username);
        mysqli_stmt_execute($stmt_update);

        if ($row["usertype"] == "user") {
            header("location: welcome.php");
            exit();
        } elseif ($row["usertype"] == "admin") {
            header("location:admin-home.php");
            exit();
        }
    } else {
        $error_message = "Invalid username or password";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>

    .btn-block{
        background-color: #1C1D3C;
    }
</style>
<body>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-6 text-center " >
            <img src="uploads/events/logo1.png" alt="">
           <h2 style="padding-top:20px;"><b>Events System</b> <br><br> </h2>
           <p> <i>Enter your details below to login.</i></p>
        </div>

        <div class="col-5">
            <div class="card">
                <div class="card-header bg-dark text-light">
                    <h4 class="text-center">Login</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($error_message)) { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php } ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" placeholder="LEC/00/01" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" placeholder="123" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="forgot.php">Forgot password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
