<?php
include 'user-nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id']) && isset($_POST['attend_text'])) {
    $event_id = $_POST['event_id'];
    $attend_text = $_POST['attend_text'];

    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        die("Connection error: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("INSERT INTO event_requests (user_id, event_id, attend_text) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $user_id, $event_id, $attend_text);
    if ($stmt->execute()) {
        $message = "Request to attend the event has been submitted.";
    } else {
        $error = "Error submitting request. Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Attend Event</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
            margin-top: 50px;
        }
        .footer {
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
            position: fixed;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Request to Attend Event</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $message; ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="attend_text">Enter Your Message:</label>
                <textarea class="form-control" id="attend_text" name="attend_text" rows="3" required></textarea>
            </div>
            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($_POST['event_id']); ?>">
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
    <div class="footer">
        <?php include 'footer.php'; ?>
    </div>
</body>
</html>
