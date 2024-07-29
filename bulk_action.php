<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_ids'])) {
    $eventIds = $_POST['event_ids'];
    $action = isset($_POST['approve_selected']) ? 'approve' : (isset($_POST['disapprove_selected']) ? 'disapprove' : '');

    if (!empty($action) && !empty($eventIds)) {
        $status = ($action === 'approve') ? 1 : 0;
        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
        $types = str_repeat('i', count($eventIds));

        $sql = "UPDATE events SET approved = ? WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i$types", $status, ...$eventIds);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: events.php');
exit;
?>
