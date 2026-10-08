<?php

require_once "../config/database.php";

$sql = "SELECT
            id,
            title,
            message,
            created_at
        FROM announcements
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$announcements = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $announcements[] = $row;

    }
}

header("Content-Type: application/json");

echo json_encode($announcements);

$conn->close();

?>