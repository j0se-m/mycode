<?php
require 'config.php';
include 'headerr.php';

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: view-event.php");
    exit();
}

$error = '';
$target_dir = "uploads/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['date']) || empty($_POST['location']) || empty($_FILES['image']['name'])) {
        $error = "Please fill all the required fields.";
    } else {
        $newfilename = time().'_'.basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $newfilename;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            $error = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        if ($_FILES["image"]["size"] > 500000) {
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $date = $_POST['date'];
                $location = $_POST['location'];
                $image = $newfilename;

                $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                // Check if the user is an admin
                if ($_SESSION['usertype'] === 'admin') {
                    // Directly insert the event without approval
                    $sql = "INSERT INTO events (name, description, date, location, image, approved) VALUES ('$name', '$description', '$date', '$location', '$image', 1)";
                } else {
                    // For non-admin users, insert with approval pending
                    $sql = "INSERT INTO events (name, description, date, location, image) VALUES ('$name', '$description', '$date', '$location', '$image')";
                }

                if (mysqli_query($conn, $sql)) {
                    header("Location: events.php");
                    exit();
                } else {
                    $error = "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
                mysqli_close($conn);
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Event</title>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.css">
</head>
<body>
    <div class="container" style="margin:40px;">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-lg" style="background-color: #f8f9fa;">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Post Event</h2>
                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name" style="font-weight: 600;padding-bottom: 8px;">Event Name:</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="form-group">
                                <label for="description" style="font-weight: 600;">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="date" style="font-weight: 600;">Date:</label>
                                <input type="date" class="form-control" id="date" name="date">
                            </div>
                            <div class="form-group">
                                <label for="location" style="font-weight: 600;">Location:</label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>
                            <div class="form-group">
                                <label for="image" style="font-weight: 600;">Select Image:</label>
                                <input type="file" class="form-control-file" id="image" name="image">
                            </div>
                            <div class="submit-btn-container">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>                        
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>
