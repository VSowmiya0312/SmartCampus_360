<?php

require_once "../config/database.php";


/* =========================
   TOTAL REQUESTS
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM requests";

$result = $conn->query($sql);

$total = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $total = (int)$row["total"];
}


/* =========================
   SUBMITTED
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM requests
        WHERE status = 'Submitted'";

$result = $conn->query($sql);

$submitted = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $submitted = (int)$row["total"];
}


/* =========================
   WORK IN PROGRESS
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM requests
        WHERE status = 'Work In Progress'";

$result = $conn->query($sql);

$progress = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $progress = (int)$row["total"];
}


/* =========================
   COMPLETED
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM requests
        WHERE status = 'Completed'";

$result = $conn->query($sql);

$completed = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $completed = (int)$row["total"];
}


/* =========================
   JSON RESPONSE
========================= */

header("Content-Type: application/json");

echo json_encode([
    "total" => $total,
    "submitted" => $submitted,
    "progress" => $progress,
    "completed" => $completed
]);


$conn->close();

?>