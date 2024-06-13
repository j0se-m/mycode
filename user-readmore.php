<?php
//require('config.php');
include 'user-nav.php';
?>

<style>
    .card {
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
        font-family: 'New Roman', serif; 
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-title {
        font-weight: bold;
        font-size: 18px; 
    }
    .card-text{
        font-family: 'Times New Roman', Times, serif;
        font-size: 16px;
        font-weight: 500;
    }

    .card-body {
        padding: 20px; 
        position: relative;
    }

    .related-events .card-img-top {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .read-more-btn {
        bottom: 10px;
        transform: translateX(100%);
        background-color: #1C1D3C;
    }
</style>

<?php
if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $sql = "SELECT * FROM events WHERE id = $event_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        ?>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <?php
                        if(!empty($event['image'])) {
                            $image_url = "uploads/" . $event['image'];
                        } else {
                            $image_url = "path_to_placeholder_image.jpg";
                        }
                        ?>
                        <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo $event['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $event['name']; ?></h5>
                            <p class="card-text"><?php echo nl2br($event['description']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 related-events">
                    <h2>Related Events</h2>
                    <div id="related-events-container">
                        <?php
                        $sql_related = "SELECT * FROM events WHERE id != $event_id LIMIT 4"; 
                        $result_related = $conn->query($sql_related);

                        if ($result_related->num_rows > 0) {
                            while($row_related = $result_related->fetch_assoc()) {
                                if(!empty($row_related['image'])) {
                                    $related_image_url = "uploads/" . $row_related['image'];
                                } else {
                                    $related_image_url = "path_to_placeholder_image.jpg"; 
                                }
                                ?>
                                <div class="card mb-3">
                                    <img src="<?php echo $related_image_url; ?>" class="card-img-top" alt="<?php echo $row_related['name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row_related['name']; ?></h5>
                                        <p class="card-text"><?php echo substr($row_related['description'], 0, 100); ?></p>
                                        <a href="event_details.php?id=<?php echo $row_related['id']; ?>" class="btn btn-primary read-more-btn">Read More</a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "No related events found";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "Event not found";
    }
} else {
    echo "Invalid request";
}

$conn->close();
include 'footer.php';
?>
