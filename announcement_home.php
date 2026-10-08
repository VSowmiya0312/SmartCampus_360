<?php

require_once "config/database.php";

header("Content-Type: application/json; charset=UTF-8");


/* ==========================================
   PUBLISH ANNOUNCEMENT
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");


    /* Check empty fields */

    if ($title === "" || $message === "") {

        echo json_encode([
            "success" => false,
            "message" => "Please enter announcement title and message."
        ]);

        exit;
    }


    /* Insert announcement */

    $sql = "INSERT INTO announcements (title, message)
            VALUES (?, ?)";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $conn->error
        ]);

        $conn->close();

        exit;
    }


    $stmt->bind_param(
        "ss",
        $title,
        $message
    );


    if ($stmt->execute()) {

        echo json_encode([
            "success" => true,
            "message" => "Announcement published successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Announcement could not be saved: " . $stmt->error
        ]);
    }


    $stmt->close();

    $conn->close();

    exit;
}



/* ==========================================
   GET ALL ANNOUNCEMENTS
========================================== */

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


echo json_encode([

    "success" => true,

    "announcements" => $announcements

]);


$conn->close();

?>