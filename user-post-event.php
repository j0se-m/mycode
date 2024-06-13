<?php
// Start the session
// require 'config.php';
include 'user-nav.php';

// Check if the user is logged in and is of user type 'user'
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'user') {
    header("Location: user-home.php");
    exit();
}

// Define a variable to store the error message
$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are filled
    if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['date']) || empty($_POST['location']) || empty($_FILES['image']['name'])) {
        $error = "Please fill all the required fields.";
    } else {
        // Process the uploaded image
        $target_dir = "uploads/";

        $newfilename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $newfilename;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $error = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // If everything is ok, try to upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // File uploaded successfully, now insert event details into database
                $name = $_POST['name'];
                $description = $_POST['description'];
                $date = $_POST['date'];
                $location = $_POST['location'];
                $image = $newfilename;
                $user_id = $_SESSION['user_id']; // Get the user ID from the session

                // Insert the event into the database
                $sql = "INSERT INTO events (name, description, date, location, image, user_id, approved) VALUES (?, ?, ?, ?, ?, ?, 0)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sssssi", $name, $description, $date, $location, $image, $user_id);
                    if ($stmt->execute()) {
                        header("Location: user-home.php");
                        exit();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
                $conn->close();
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>

<style>
    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        margin-bottom: 16px;
    }
    .submit-btn-container {
        text-align: center;
        padding-bottom: 22px;
    }
    .submit-btn {
        background-color: #1C1D3C;
        color: white;
        border: none;
    }
    .submit-btn:hover,
    .submit-btn:focus {
        background-color: #1C1D3C;
        color: white;
    }
</style>

<div class="container" style="margin:40px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg" style="background-color: #f8f9fa;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Post New Event</h2>
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                    <?php } ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name" style="font-weight: 600;">Event Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description" style="font-weight: 600;">Description:</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="date" style="font-weight: 600;">Date:</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="location" style="font-weight: 600;">Location:</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="form-group">
                            <label for="image" style="font-weight: 600;">Select Image:</label>
                            <input type="file" class="form-control-file" id="image" name="image" required>
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

<?php
include 'footer.php';
?>
