<?php
include 'headerr.php';

if (isset($_POST["submit"])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $email_parts = explode('@', $email);
    if (count($email_parts) != 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO crud (id, first_name, last_name, email, gender, username, password) 
            VALUES (NULL, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $gender, $username, $hashed_password);

    $result = $stmt->execute();

    if ($result) {
        header("Location: index.php?msg=New record created successfully");
    } else {
        echo "Failed: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
          crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>New User Registration</title>

    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            border-radius: 5px;
        }

        .card:hover {
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
        }

        .container {
            margin-left: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="card" style="width: 80%; margin-top: 40px; margin-bottom: 40px;">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 style="padding-top: 20px;">Add New User</h3>
                        <p class="text-muted">Complete the form below to add a new user</p>
                    </div>

                    <form action="" method="post">
                        <div class="row mb-3">
                            <div class="col mb-3">
                                <label class="form-label">First Name:</label>
                                <input type="text" class="form-control" name="first_name" placeholder="Albert" required>
                            </div>

                            <div class="col mb-3">
                                <label class="form-label">Last Name:</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Einstein" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username:</label>
                            <input type="text" class="form-control" name="username" placeholder="LEC/00/01" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password:</label>
                            <input type="password" class="form-control" name="password" placeholder="lec12@" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Gender:</label>
                            &nbsp;
                            <input type="radio" class="form-check-input" name="gender" id="male" value="male" required>
                            <label for="male" class="form-input-label">Male</label>
                            &nbsp;
                            <input type="radio" class="form-check-input" name="gender" id="female" value="female" required>
                            <label for="female" class="form-input-label">Female</label>
                        </div>

                        <div class="text-center" style="padding: 30px;">
                            <button type="submit" class="btn btn-success me-3" name="submit">Save</button>
                            <a href="index.php" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
            crossorigin="anonymous"></script>
</body>
</html>
