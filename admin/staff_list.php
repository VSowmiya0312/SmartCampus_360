<?php

require_once "../config/database.php";

$sql = "SELECT
            id,
            name,
            email,
            department,
            phone,
            status,
            created_at
        FROM staff
        ORDER BY name ASC";

$result = $conn->query($sql);

$staff = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $staff[] = $row;

    }
}

header("Content-Type: application/json");

echo json_encode($staff);

$conn->close();

?>