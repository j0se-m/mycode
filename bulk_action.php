<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_selected']) || isset($_POST['disapprove_selected'])) {
        $event_ids = $_POST['event_ids'] ?? [];
        
        if (!empty($event_ids)) {
            $approved = isset($_POST['approve_selected']) ? 1 : 0;
            $stmt = $conn->prepare("UPDATE events SET approved = ? WHERE id = ?");
            
            foreach ($event_ids as $event_id) {
                if (filter_var($event_id, FILTER_VALIDATE_INT) === false) {
                    continue; // Skip invalid IDs
                }
                $stmt->bind_param("ii", $approved, $event_id);
                $stmt->execute();
            }
            
            $stmt->close();
            
            $message = $approved ? "Selected events have been approved successfully." : "Selected events have been disapproved successfully.";
        } else {
            $message = "No events selected.";
        }
        
        $conn->close();
        echo "<script type='text/javascript'>
                alert('$message');
                window.location.href = 'events.php';
              </script>";
        exit;
    }
}
?>
