<?php

session_start();

require_once __DIR__ . "/config/database.php";

header("Content-Type: application/json");

/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => true,
        "count" => 0
    ]);
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| CREATE ANNOUNCEMENT READ TABLE
|--------------------------------------------------------------------------
*/
$createTable = "
    CREATE TABLE IF NOT EXISTS announcement_reads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        announcement_id INT NOT NULL,
        user_id INT NOT NULL,
        read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_announcement_user
        (announcement_id, user_id)
    )
";

if (!$conn->query($createTable)) {
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| COUNT ONLY UNREAD ANNOUNCEMENTS
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT COUNT(*) AS total
    FROM announcements a
    LEFT JOIN announcement_reads ar
        ON a.id = ar.announcement_id
        AND ar.user_id = ?
    WHERE ar.id IS NULL
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit;
}

$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    $stmt->close();

    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit;
}

$result = $stmt->get_result();

$count = 0;

if ($row = $result->fetch_assoc()) {
    $count = (int) $row["total"];
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| RETURN COUNT
|--------------------------------------------------------------------------
*/
echo json_encode([
    "success" => true,
    "count" => $count
]);

?>