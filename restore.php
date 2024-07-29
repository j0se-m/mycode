<?php
include "headerr.php";

if (isset($_GET['id'])) 
    $id = $_GET['id'];

    // Check if user confirmed the restore action
    if (isset($_POST['confirm_restore'])) {
        // Update the status of the user to indicate restored (status = 0 means restored)
        $sql = "UPDATE crud SET status = 0 WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            // Redirect back to index.php after successful restore
            header("Location: index.php");
            exit;
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } 
        // Display confirmation form
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Confirm personal data</title> 
</head>
<body>
    <style>
        html, body {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .confirmation-container {
            width: 400px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
            text-align: center;
        }
    </style>

    <div class="confirmation-container">
        <h2>Confirm Restore</h2>
        <p>Are you sure you want to restore this user?</p>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button type="submit" class="btn btn-primary" name="confirm_restore">Yes, Restore</button>
            <a href="javascript:history.go(-1)" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

</body>
</html>

