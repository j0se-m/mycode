<?php
// Include necessary files and initialize connection
include 'user-nav.php';
// include 'config.php'; // Assuming this file contains database connection details

// Check if event ID is set in URL parameter
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Query to fetch event details using prepared statement
    $sql_event = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql_event);

    if ($stmt) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result_event = $stmt->get_result();

        // Check if event exists
        if ($result_event->num_rows > 0) {
            $event = $result_event->fetch_assoc();

            // Check if the current user is the creator of the event
            if (isset($_SESSION['user_id'])) {
                $current_user_id = $_SESSION['user_id']; // Assuming session contains user ID
                $event_creator_id = $event['user_id']; // Assuming user_id column in events table

                $is_event_creator = ($current_user_id == $event_creator_id);
            } else {
                // Redirect to login page or handle unauthorized access
                header("Location: login.php");
                exit();
            }
?>

            <style>
                /* CSS styles for cards and related elements */
                .card {
                    border-radius: 5px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    transition: box-shadow 0.3s ease;
                    font-family: 'New Roman', serif;
                    margin-bottom: 20px;
                    width: 95%;
                    max-width: 95%; /* Ensure cards do not exceed container width */
                }

                .card:hover {
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                }

                .card-title {
                    font-weight: bold;
                    font-size: 16px; /* Reduce font size */
                }

                .card-text {
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 14px; /* Reduce font size */
                    font-weight: 500;
                }

                .card-body {
                    padding: 15px; /* Reduce padding */
                }

                .related-events .card-img-top {
                    width: 100%;
                    height: 120px; /* Reduce image height */
                    object-fit: cover;
                }

                .read-more-btn {
                    background-color: #1C1D3C;
                    padding: 5px 10px;
                    font-size: 14px; 
                }

                .container {
                    margin-bottom: 1px;
                    margin-left: 205px; 
                }

                .related-events .card {
                    width: calc(100% - 20px); /* Ensure related cards fit without overflow */
                    margin-left: 10px;
                    margin-right: 10px;
                }

                @media (max-width: 768px) {
                    /* .container {
                        margin-left: 200px;
                    } */

                    .related-events .card {
                        width: 100%;
                        margin-left: 0;
                        margin-right: 0;
                    }
                }
            </style>

            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Display Event Details -->
                        <div class="card">
                            <?php
                            // Check if event has an image
                            if (!empty($event['image'])) {
                                $image_url = "uploads/" . $event['image'];
                            } else {
                                $image_url = "path_to_placeholder_image.jpg";
                            }
                            ?>
                            <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo $event['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $event['name']; ?></h5>
                                <p class="card-text"><?php echo nl2br($event['description']); ?></p>
                                <?php if (!$is_event_creator): ?>
                                    <!-- Add join event button here for non-creators -->
                                    <a href="join-event.php?id=<?php echo $event_id; ?>" class="btn btn-success">Join Event</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Display Users Invited, Accepted, and Approved (if current user is the event creator) -->
                        <?php if ($is_event_creator): ?>
                            <div class="mt-4">
                                <!-- <h3>Manage Invitations</h3> -->
                                <!-- Add CRUD operations for event invitations -->
                                <!-- <p>Here you can manage invitations:</p> -->
                                <!-- Add buttons or links for CRUD operations (Create, Read, Update, Delete) -->
                                <a href="invite-users.php?id=<?php echo $event_id; ?>" class="btn btn-primary">Invite Users</a>
                                <!-- <a href="view-invitations.php?id=<?php echo $event_id; ?>" class="btn btn-info">View Invitations</a> -->
                                <!-- <a href="manage-invitations.php?id=<?php echo $event_id; ?>" class="btn btn-warning">Manage Invitations</a> -->
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 related-events">
                        <h2>Related Events</h2>
                        <div id="related-events-container">
                            <?php
                            // Query to fetch related events (excluding current event)
                            $sql_related = "SELECT * FROM events WHERE id != ? LIMIT 4";
                            $stmt_related = $conn->prepare($sql_related);

                            if ($stmt_related) {
                                $stmt_related->bind_param("i", $event_id);
                                $stmt_related->execute();
                                $result_related = $stmt_related->get_result();

                                // Check if related events exist
                                if ($result_related->num_rows > 0) {
                                    while ($row_related = $result_related->fetch_assoc()) {
                                        if (!empty($row_related['image'])) {
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
                                                <!-- Add read more button for related events -->
                                                <a href="event_details.php?id=<?php echo $row_related['id']; ?>" class="btn btn-primary read-more-btn">Read More</a>
                                                <!-- Add join event button for related events -->
                                                <a href="join-event.php?id=<?php echo $row_related['id']; ?>" class="btn btn-success">Join Event</a>
                                            </div>
                                        </div>
                            <?php
                                    }
                                } else {
                                    echo "No related events found";
                                }

                                // Close the statement
                                $stmt_related->close();
                            } else {
                                echo "Error preparing related events query";
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

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing event query";
    }
} else {
    echo "Invalid request";
}

// Close database connection
$conn->close();
// include 'footer.php';
?>
