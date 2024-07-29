<?php
include 'headerr.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $existing_image = $_POST['existing_image'];
    $image = $existing_image;

    // Check if a new file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $uploaded_file = $upload_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploaded_file)) {
            $image = $_FILES['image']['name']; // Update image with new filename
        }
    }

    $sql = "UPDATE events SET name = ?, description = ?, location = ?, date = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $description, $location, $date, $image, $event_id);

    if ($stmt->execute()) {
        echo "<div class='container mt-4'><div class='alert alert-success'>Event updated successfully.</div></div>";
    } else {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Error updating event: " . $conn->error . "</div></div>";
    }

    $stmt->close();
}

// Fetch event details for editing
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    $sql = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
?>
<style>
       .content {
            padding: 20px;
            margin-top: 6px; /* Adjusting for navbar height */
            margin-left: 180px; /* Adjusting for sidebar width */
        }
</style>
<div class="content">

<div class="container mt-4 mb-4">
    <div class="card">
        <div class="card-body p-4">
            <style>
                .label-bold {
                    font-weight: bold;
                    color: #333;
                }

                .input-normal {
                    font-weight: normal;
                    color: #777;
                }

                .file-input input[type=file] {
                    display: none;
                }
            </style>
            <h2 class="card-title mb-4">Edit Event</h2>
            <form action="edit-event.php?id=<?php echo $event_id; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="event_id" value="<?php echo $row['id']; ?>">
                <div class="form-group mb-4">
                    <label for="name" class="label-bold">Event Title:</label>
                    <input type="text" class="form-control input-normal" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                </div>
                <div class="form-group mb-4">
                    <label for="image" class="label-bold">Image:</label>
                    <div class="file-input">
                        <input type="file" class="form-control-file input-normal" id="image" name="image" onchange="updateFileName(this)">
                        <label for="image" class="input-group-text"><?php echo htmlspecialchars($row['image']); ?></label>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label for="description" class="label-bold">Description:</label>
                    <textarea class="form-control auto-height input-normal" id="description" name="description"><?php echo htmlspecialchars($row['description']); ?></textarea>
                </div>
                <div class="form-group mb-4">
                    <label for="location" class="label-bold">Event Location:</label>
                    <input type="text" class="form-control input-normal" id="location" name="location" value="<?php echo htmlspecialchars($row['location']); ?>">
                </div>
                <div class="form-group mb-4">
                    <label for="date" class="label-bold">Event Date:</label>
                    <input type="date" class="form-control input-normal" id="date" name="date" value="<?php echo htmlspecialchars($row['date']); ?>">
                </div>
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($row['image']); ?>">
                <button type="submit" class="btn btn-primary mt-4">Update Event</button>
            </form>
        </div>
    </div>
</div>
    
</div>
<script>
    function updateFileName(input) {
        var label = input.nextElementSibling;
        if (input.files.length > 0) {
            label.textContent = input.files[0].name;
        } else {
            label.textContent = "<?php echo htmlspecialchars($row['image']); ?>";
        }
    }
</script>
<?php
    } else {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Event not found.</div></div>";
    }
    $stmt->close();
} else {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Event ID not provided.</div></div>";
}

$conn->close();
include 'footer.php';
?>
