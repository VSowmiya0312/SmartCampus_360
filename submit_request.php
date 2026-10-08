<?php

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: report.html");
    exit;
}

/* =========================
   GET FORM DATA
========================= */

$student_name  = trim($_POST["studentName"] ?? "");
$student_email = trim($_POST["studentEmail"] ?? "");
$category      = trim($_POST["category"] ?? "");
$location      = trim($_POST["location"] ?? "");
$description   = trim($_POST["description"] ?? "");

/* =========================
   VALIDATION
========================= */

if (
    $student_name === "" ||
    $student_email === "" ||
    $category === "" ||
    $location === "" ||
    $description === ""
) {
    die("Please fill all required fields.");
}

if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

/* =========================
   GENERATE UNIQUE TRACK ID
========================= */

do {
    $track_id = "SCR" . rand(100000, 999999);

    $check = $conn->prepare(
        "SELECT id FROM requests WHERE track_id = ? LIMIT 1"
    );

    $check->bind_param("s", $track_id);
    $check->execute();

    $result = $check->get_result();

    $exists = ($result->num_rows > 0);

    $check->close();

} while ($exists);

/* =========================
   INSERT REQUEST
========================= */

$status = "Submitted";
$assigned_staff = "Not Assigned";

$sql = "INSERT INTO requests
        (
            track_id,
            student_name,
            student_email,
            category,
            location,
            description,
            status,
            assigned_staff
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "ssssssss",
    $track_id,
    $student_name,
    $student_email,
    $category,
    $location,
    $description,
    $status,
    $assigned_staff
);

if (!$stmt->execute()) {
    die("Request could not be submitted: " . $stmt->error);
}

$stmt->close();

/* =========================
   NOTIFICATION 1
   REQUEST SUBMITTED
========================= */

$message1 =
    "📝 Your campus issue has been reported successfully. Track ID: "
    . $track_id;

$sqlNotification = "INSERT INTO notifications
                    (user_email, track_id, message)
                    VALUES (?, ?, ?)";

$notificationStmt = $conn->prepare($sqlNotification);

if ($notificationStmt) {

    $notificationStmt->bind_param(
        "sss",
        $student_email,
        $track_id,
        $message1
    );

    $notificationStmt->execute();

    $notificationStmt->close();
}

/* =========================
   NOTIFICATION 2
   TRACK ID GENERATED
========================= */

$message2 =
    "🔍 Your Track ID has been generated successfully: "
    . $track_id
    . ". You can now track your request.";

$notificationStmt = $conn->prepare($sqlNotification);

if ($notificationStmt) {

    $notificationStmt->bind_param(
        "sss",
        $student_email,
        $track_id,
        $message2
    );

    $notificationStmt->execute();

    $notificationStmt->close();
}

$conn->close();

/* =========================
   SUCCESS PAGE
========================= */

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Request Submitted - SmartCampus360</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;

    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2
        );
}

.success-box {

    width: 90%;
    max-width: 550px;

    background: white;

    padding: 40px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 15px 40px rgba(0,0,0,0.25);
}

.icon {
    font-size: 60px;
    margin-bottom: 15px;
}

h1 {
    color: #222;
    margin-bottom: 10px;
}

p {
    color: #666;
    font-size: 16px;
}

.track-box {

    margin: 25px 0;

    padding: 20px;

    background: #f3f4ff;

    border-radius: 12px;
}

.track-label {
    font-size: 14px;
    color: #666;
}

.track-id {

    margin-top: 8px;

    font-size: 30px;

    font-weight: bold;

    color: #667eea;

    letter-spacing: 2px;
}

.buttons {
    margin-top: 25px;
}

.btn {

    display: inline-block;

    padding: 12px 20px;

    margin: 5px;

    border-radius: 8px;

    text-decoration: none;

    font-weight: bold;

    color: white;

    background: #667eea;
}

.btn:hover {
    opacity: 0.9;
}

.home {
    background: #555;
}

</style>

</head>

<body>

<div class="success-box">

    <div class="icon">✅</div>

    <h1>Request Submitted Successfully!</h1>

    <p>
        Your campus issue has been registered successfully.
    </p>

    <div class="track-box">

        <div class="track-label">
            Your Track ID
        </div>

        <div class="track-id">
            <?php echo htmlspecialchars($track_id); ?>
        </div>

    </div>

    <p>
        Save this Track ID to check your request status.
    </p>

    <div class="buttons">

        <a
            class="btn"
            href="track_request.php?track=<?php echo urlencode($track_id); ?>"
        >
            🔍 Track Request
        </a>

        <a
            class="btn home"
            href="index.html"
        >
            🏠 Home
        </a>

    </div>

</div>

</body>

</html>