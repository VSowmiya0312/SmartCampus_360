```php
<?php

session_start();

require_once "../config/database.php";

/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

/* =====================================================
   CHECK STAFF ROLE
===================================================== */

$user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($user_role !== "staff") {
    die("Access denied. Please login using a staff account.");
}

/* =====================================================
   GET STAFF NAME
===================================================== */

$staff_name = trim($_SESSION["name"] ?? "");

if ($staff_name === "") {
    die("Staff name is missing from the login session.");
}

/* =====================================================
   GET TRACK ID
===================================================== */

$track_id = "";

if (isset($_GET["track"])) {
    $track_id = trim($_GET["track"]);
}

if ($track_id === "" && isset($_POST["track_id"])) {
    $track_id = trim($_POST["track_id"]);
}

if ($track_id === "") {
    die(
        "Track ID is required. Please open Update Request from Assigned Requests."
    );
}

/* =====================================================
   GET REQUEST DETAILS
===================================================== */

$sql = "SELECT *
        FROM requests
        WHERE track_id = ?
        AND assigned_staff = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $track_id,
    $staff_name
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    die(
        "Request not found or this request is not assigned to you."
    );
}

$request = $result->fetch_assoc();

$stmt->close();

/* =====================================================
   UPDATE STATUS
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_status = trim($_POST["status"] ?? "");

    $allowed_statuses = [
        "Staff Assigned",
        "Work In Progress",
        "Completed"
    ];

    if (!in_array($new_status, $allowed_statuses, true)) {
        die("Invalid status selected.");
    }

    /* =================================================
       UPDATE REQUEST STATUS
    ================================================= */

    $updateSql = "UPDATE requests
                  SET status = ?
                  WHERE track_id = ?
                  AND assigned_staff = ?";

    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        die(
            "Unable to update request: "
            . $conn->error
        );
    }

    $updateStmt->bind_param(
        "sss",
        $new_status,
        $track_id,
        $staff_name
    );

    if (!$updateStmt->execute()) {

        $updateStmt->close();

        die(
            "Failed to update request: "
            . $updateStmt->error
        );
    }

    $updateStmt->close();

    /* =================================================
       CREATE STUDENT NOTIFICATION
    ================================================= */

    if ($new_status === "Work In Progress") {

        $message =
            "🔵 Your request "
            . $track_id
            . " is now Work In Progress.";

    } elseif ($new_status === "Completed") {

        $message =
            "✅ Your request "
            . $track_id
            . " has been Completed.";

    } else {

        $message =
            "👨‍🔧 Staff has been assigned to your request "
            . $track_id
            . ".";
    }

    $notificationSql =
        "INSERT INTO notifications
        (user_email, track_id, message)
        VALUES (?, ?, ?)";

    $notificationStmt =
        $conn->prepare($notificationSql);

    if ($notificationStmt) {

        $student_email = $request["student_email"];

        $notificationStmt->bind_param(
            "sss",
            $student_email,
            $track_id,
            $message
        );

        $notificationStmt->execute();

        $notificationStmt->close();
    }

    /* =================================================
       SUCCESS PAGE
    ================================================= */

    $conn->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Request Updated - SmartCampus360
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;

    font-family: Arial, Helvetica, sans-serif;

    background-image:
        linear-gradient(
            rgba(20, 40, 100, 0.60),
            rgba(25, 35, 80, 0.68)
        ),
        url("../images/college.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;
}

.success-box {

    width: 100%;

    max-width: 520px;

    background: rgba(255,255,255,0.97);

    padding: 40px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.30);
}

.icon {

    font-size: 60px;

    margin-bottom: 10px;
}

h1 {

    color: #222;

    margin: 10px 0 15px;
}

p {

    color: #555;

    line-height: 1.6;
}

.track {

    color: #2346a3;

    font-weight: bold;

    font-size: 20px;
}

.status {

    color: #2346a3;

    font-weight: bold;
}

.btn {

    display: inline-block;

    margin-top: 20px;

    padding: 13px 22px;

    background: #2346a3;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;
}

.btn:hover {

    background: #17347f;
}

</style>

</head>

<body>

<div class="success-box">

    <div class="icon">
        ✅
    </div>

    <h1>
        Request Updated Successfully
    </h1>

    <p>

        Request

        <span class="track">

            <?php
            echo htmlspecialchars($track_id);
            ?>

        </span>

        has been updated.

    </p>

    <p>

        Current Status:

        <span class="status">

            <?php
            echo htmlspecialchars($new_status);
            ?>

        </span>

    </p>

    <a
        href="assigned-requests.php"
        class="btn"
    >
        ← Back to Assigned Requests
    </a>

</div>

</body>

</html>

<?php

    exit;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Update Request - SmartCampus360
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    padding: 30px 15px;

    font-family: Arial, Helvetica, sans-serif;

    background-image:
        linear-gradient(
            rgba(20, 40, 100, 0.60),
            rgba(25, 35, 80, 0.68)
        ),
        url("../images/college.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;
}

.container {

    width: 100%;

    max-width: 720px;

    margin: 0 auto;
}

.card {

    background: rgba(255,255,255,0.97);

    padding: 30px;

    border-radius: 20px;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.25);
}

h1 {

    margin: 0;

    text-align: center;

    color: #222;

    font-size: 30px;
}

.track-id {

    margin: 15px 0 25px;

    text-align: center;

    font-size: 22px;

    font-weight: bold;

    color: #2346a3;
}

/* =====================================================
   REQUEST INFORMATION
===================================================== */

.info-box {

    background: #f4f6fb;

    border-left: 5px solid #2346a3;

    border-radius: 12px;

    padding: 18px;

    margin-bottom: 25px;
}

.info-row {

    margin-bottom: 12px;

    line-height: 1.5;
}

.info-row:last-child {

    margin-bottom: 0;
}

.info-label {

    font-weight: bold;

    color: #222;
}

.info-value {

    color: #555;
}

/* =====================================================
   FORM
===================================================== */

label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #333;
}

select {

    width: 100%;

    padding: 14px;

    border: 1px solid #ccc;

    border-radius: 8px;

    background: white;

    font-size: 16px;

    margin-bottom: 20px;

    cursor: pointer;
}

button {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #2346a3;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {

    background: #17347f;
}

.back {

    display: block;

    margin-top: 16px;

    text-align: center;

    color: #2346a3;

    text-decoration: none;

    font-weight: bold;
}

.back:hover {

    text-decoration: underline;
}

/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 600px) {

    body {

        padding: 20px 10px;
    }

    .card {

        padding: 22px;
    }

    h1 {

        font-size: 25px;
    }

    .track-id {

        font-size: 19px;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>
            🔧 Update Request
        </h1>

        <div class="track-id">

            🆔

            <?php
            echo htmlspecialchars(
                $request["track_id"]
            );
            ?>

        </div>

        <!-- =================================================
             REQUEST INFORMATION
        ================================================== -->

        <div class="info-box">

            <div class="info-row">

                <span class="info-label">
                    👤 Student:
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $request["student_name"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    📧 Student Email:
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $request["student_email"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    🏷️ Category:
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $request["category"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    📍 Location:
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $request["location"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    📝 Description:
                </span>

                <span class="info-value">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $request["description"]
                        )
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    📊 Current Status:
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $request["status"]
                    );
                    ?>

                </span>

            </div>

        </div>


        <!-- =================================================
             UPDATE FORM
        ================================================== -->

        <form method="POST">

            <!-- IMPORTANT:
                 Track ID is sent with the form
            -->

            <input
                type="hidden"
                name="track_id"
                value="<?php
                    echo htmlspecialchars(
                        $request["track_id"]
                    );
                ?>"
            >


            <label for="status">

                Select New Status

            </label>


            <select
                id="status"
                name="status"
                required
            >

                <option value="">
                    -- Select Status --
                </option>

                <option value="Staff Assigned">

                    👨‍🔧 Staff Assigned

                </option>

                <option value="Work In Progress">

                    🔵 Work In Progress

                </option>

                <option value="Completed">

                    ✅ Completed

                </option>

            </select>


            <button type="submit">

                🔄 Update Status

            </button>

        </form>


        <a
            href="assigned-requests.php"
            class="back"
        >

            ← Back to Assigned Requests

        </a>

    </div>

</div>

</body>

</html>
```