<?php
require('config.php');
include 'HomeHeader.php';
?>

<style>
    .container{
        margin-bottom: 30px;
    }
    .card {
        height: 500px;
        border-radius: 5px; 
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        transition: box-shadow 0.3s ease; 
        margin-bottom: 30px;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); 
        
    }
    .btn-primary{
        bottom: 10px;
        transform: translateX(100%);
        margin-top: 12px;;
        background-color: #1C1D3C;

    }
       
</style>

<div class="container">
    <?php
    $sql = "SELECT * FROM events";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="row">';
        while($row = $result->fetch_assoc()) {
            echo '<div class="col-lg-4 col-md-6 mt-5">';
            echo '<div class="card h-100">';
            
            if(!empty($row['image'])) {
                $image_url = "uploads/" . $row['image'];
            } else {
                $image_url = "path_to_placeholder_image.jpg";
            }
            
            echo '<img src="' . $image_url . '" class="card-img-top" style="object-fit: cover; height: 150px;" alt="' . $row['name'] . '">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $row['name'] . '</h5>';
            echo '<p class="card-text">' . substr($row['description'], 0, 100) . '...</p>';
            echo '<a href="events-details-home.php?id=' . $row['id'] . '" class="btn btn-primary ">Read More</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo "No events found";
    }
    $conn->close();
    ?>
</div>

<?php
include 'footer.php';
?>
