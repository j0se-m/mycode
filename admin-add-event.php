<?php
ob_start(); // Start output buffering

include 'headerr.php';

if (!isset($_SESSION['username'])) {
    header("Location: userLogin.php");
    exit();
}

$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are filled
    if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['date']) || empty($_POST['location'])) {
        $error = "Please fill all the required fields.";
    } else {
        // Initialize variables for optional file upload
        $newfilename = '';
        $uploadOk = 1;

        // Check if file is uploaded
        if (!empty($_FILES['image']['name'])) {
            // Process the uploaded image
            $target_dir = "uploads/";

            $newfilename = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $newfilename;
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
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        // If no errors, proceed with inserting event details into database
        if ($uploadOk == 1) {
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
                    header("Location: events.php");
                    exit();
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error preparing statement: " . $conn->error;
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/download.jpg" />
    <title>Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    /* Custom styles to display Date and Location inline */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .form-row .form-group {
        width: calc(50% - 10px); /* Adjust width and margin as needed */
        margin-bottom: 0;
    }
        .custom-navbar {
            background-color: #1C1D3C !important;
            font-family: "Times New Roman", Times, serif;
            font-size: 18px;
            line-height: 1.7em;
            color: #333;
            font-weight: normal;
            font-style: normal;
            z-index: 1000;
        }
        .custom-navbar .navbar-nav .nav-link {
            color: whitesmoke !important; 
            text-transform: uppercase;
            margin-right: 15px; 
        }
        .custom-navbar .navbar-brand {
            margin-left: 70px;
        }
        .content {
            padding: 20px;
            margin-top:1px; /* Adjusting for navbar height */
            margin-left:180px; 
            margin-right:30px;/* Adjusting for sidebar width */
        }
        .custom-footer {
            background-color: #1C1D3C !important;
            color: whitesmoke;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px); /* Adjusting for sidebar width */
            padding: 10px 0;
            margin-left: 250px; /* Adjusting for sidebar width */
        }
        

    </style>
</head>
<body>
   
    
<div class="content">


<div class="container" style="margin: 40px;">
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
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date" style="font-weight: 600;">Date:</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="location" style="font-weight: 600;">Location:</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="image" style="font-weight: 600;">Select Image (Optional):</label>
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

</div>
 


  

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
