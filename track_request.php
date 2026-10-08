```php
<?php

require_once "config/database.php";

session_start();

/* =====================================================
   GET TRACK ID
===================================================== */

$track_id = "";

if (isset($_GET["track"])) {
    $track_id = trim($_GET["track"]);
}

if (isset($_POST["track_id"])) {
    $track_id = trim($_POST["track_id"]);
}

$track_id = trim($track_id);

if ($track_id === "") {
    die("Track ID is required.");
}


/* =====================================================
   FUNCTION: CREATE NOTIFICATION
===================================================== */

function createNotification($conn, $email, $track_id, $message)
{
    if (empty($email)) {
        return false;
    }

    $stmt = $conn->prepare(
        "INSERT INTO notifications
        (user_email, track_id, message)
        VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "sss",
        $email,
        $track_id,
        $message
    );

    $result = $stmt->execute();

    $stmt->close();

    return $result;
}


/* =====================================================
   GET REQUEST FUNCTION
   TRIM + LOWER MAKES TRACK ID SEARCH FLEXIBLE
===================================================== */

function getRequestByTrackId($conn, $track_id)
{
    $stmt = $conn->prepare(
        "SELECT
            id,
            track_id,
            student_name,
            student_email,
            category,
            location,
            description,
            status,
            assigned_staff,
            created_at
         FROM requests
         WHERE LOWER(TRIM(track_id)) = LOWER(TRIM(?))
         LIMIT 1"
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $track_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $request = null;

    if ($result->num_rows === 1) {
        $request = $result->fetch_assoc();
    }

    $stmt->close();

    return $request;
}


/* =====================================================
   GET CURRENT REQUEST
===================================================== */

$request = getRequestByTrackId(
    $conn,
    $track_id
);


/* =====================================================
   REQUEST NOT FOUND
===================================================== */

if (!$request) {

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>Track ID Not Found - SmartCampus360</title>

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

                font-family: Arial, sans-serif;

                background:
                linear-gradient(
                    135deg,
                    #667eea,
                    #764ba2
                );
            }

            .box {

                width: 100%;

                max-width: 500px;

                background: white;

                padding: 35px;

                border-radius: 20px;

                text-align: center;

                box-shadow:
                0 15px 40px
                rgba(0,0,0,0.2);
            }

            .icon {

                font-size: 60px;

                margin-bottom: 15px;
            }

            h1 {

                color: #333;

                margin-bottom: 10px;
            }

            p {

                color: #666;

                line-height: 1.6;
            }

            .track {

                display: inline-block;

                margin: 15px 0;

                padding: 10px 18px;

                background: #f1f3ff;

                color: #667eea;

                border-radius: 30px;

                font-weight: bold;
            }

            a {

                display: inline-block;

                margin-top: 15px;

                padding: 12px 22px;

                background: #667eea;

                color: white;

                text-decoration: none;

                border-radius: 8px;

                font-weight: bold;
            }

            a:hover {

                background: #5568d8;
            }

        </style>

    </head>

    <body>

        <div class="box">

            <div class="icon">
                ❌
            </div>

            <h1>
                Track ID Not Found
            </h1>

            <p>
                We could not find a request for:
            </p>

            <div class="track">
                <?php
                echo htmlspecialchars($track_id);
                ?>
            </div>

            <p>
                Please enter the exact Track ID
                generated when you submitted your request.
            </p>

            <a href="track.html">
                🔍 Try Again
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;
}


/* =====================================================
   HANDLE POST ACTIONS
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    /* =================================================
       ASSIGN STAFF
    ================================================= */

    if ($action === "assign") {

        $staff_id = intval(
            $_POST["staff_id"] ?? 0
        );

        if ($staff_id <= 0) {

            die("Please select a staff member.");
        }


        /* Get staff */

        $stmt = $conn->prepare(
            "SELECT
                id,
                name,
                email,
                department
             FROM staff
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "i",
            $staff_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {

            $stmt->close();

            die("Staff member not found.");
        }

        $staff = $result->fetch_assoc();

        $stmt->close();


        /* Update request */

        $newStatus = "Staff Assigned";

        $stmt = $conn->prepare(
            "UPDATE requests
             SET assigned_staff = ?,
                 status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssi",
            $staff["name"],
            $newStatus,
            $request["id"]
        );

        if (!$stmt->execute()) {

            $stmt->close();

            die("Unable to assign staff.");
        }

        $stmt->close();


        /* Student notification */

        $studentMessage =
            "👨‍🔧 Staff assigned: "
            . $staff["name"]
            . " | Track ID: "
            . $request["track_id"];


        createNotification(
            $conn,
            $request["student_email"],
            $request["track_id"],
            $studentMessage
        );


        /* Staff notification */

        $staffMessage =
            "📋 New request assigned to you. "
            . "Track ID: "
            . $request["track_id"]
            . " | Student: "
            . $request["student_name"]
            . " | Category: "
            . $request["category"];


        createNotification(
            $conn,
            $staff["email"],
            $request["track_id"],
            $staffMessage
        );


        header(
            "Location: track_request.php?track="
            . urlencode($request["track_id"])
        );

        exit;
    }


    /* =================================================
       START WORK / IN PROGRESS
    ================================================= */

    if ($action === "progress") {

        if (
            empty($request["assigned_staff"]) ||
            $request["assigned_staff"] === "Not Assigned"
        ) {

            die(
                "Please assign a staff member before starting work."
            );
        }


        $newStatus = "In Progress";


        $stmt = $conn->prepare(
            "UPDATE requests
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $newStatus,
            $request["id"]
        );

        if (!$stmt->execute()) {

            $stmt->close();

            die("Unable to update request.");
        }

        $stmt->close();


        /* Student notification */

        $studentMessage =
            "🔧 Work is now in progress. "
            . "Track ID: "
            . $request["track_id"]
            . " | Staff: "
            . $request["assigned_staff"];


        createNotification(
            $conn,
            $request["student_email"],
            $request["track_id"],
            $studentMessage
        );


        /* Staff notification */

        $stmt = $conn->prepare(
            "SELECT email
             FROM staff
             WHERE name = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "s",
            $request["assigned_staff"]
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $staff = $result->fetch_assoc();

            createNotification(
                $conn,
                $staff["email"],
                $request["track_id"],
                "🔧 Work started for Track ID: "
                . $request["track_id"]
            );
        }

        $stmt->close();


        header(
            "Location: track_request.php?track="
            . urlencode($request["track_id"])
        );

        exit;
    }


    /* =================================================
       COMPLETE REQUEST
    ================================================= */

    if ($action === "complete") {

        if ($request["status"] !== "In Progress") {

            die(
                "The request must be In Progress before completion."
            );
        }


        $newStatus = "Completed";


        $stmt = $conn->prepare(
            "UPDATE requests
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $newStatus,
            $request["id"]
        );

        if (!$stmt->execute()) {

            $stmt->close();

            die("Unable to complete request.");
        }

        $stmt->close();


        /* Student notification */

        $studentMessage =
            "🎉 Your request has been completed successfully. "
            . "Track ID: "
            . $request["track_id"]
            . " | Staff: "
            . $request["assigned_staff"];


        createNotification(
            $conn,
            $request["student_email"],
            $request["track_id"],
            $studentMessage
        );


        /* Staff notification */

        $stmt = $conn->prepare(
            "SELECT email
             FROM staff
             WHERE name = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "s",
            $request["assigned_staff"]
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $staff = $result->fetch_assoc();

            createNotification(
                $conn,
                $staff["email"],
                $request["track_id"],
                "✅ Request completed successfully. Track ID: "
                . $request["track_id"]
            );
        }

        $stmt->close();


        header(
            "Location: track_request.php?track="
            . urlencode($request["track_id"])
        );

        exit;
    }
}


/* =====================================================
   REFRESH REQUEST AFTER POST
===================================================== */

$request = getRequestByTrackId(
    $conn,
    $track_id
);


/* =====================================================
   STAFF LIST
===================================================== */

$staffList = [];

$result = $conn->query(
    "SELECT
        id,
        name,
        email,
        department
     FROM staff
     ORDER BY name ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $staffList[] = $row;
    }
}


/* =====================================================
   GET STUDENT NOTIFICATIONS
===================================================== */

$notifications = [];

$stmt = $conn->prepare(
    "SELECT
        message,
        created_at
     FROM notifications
     WHERE user_email = ?
     AND track_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param(
    "ss",
    $request["student_email"],
    $request["track_id"]
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $notifications[] = $row;
}

$stmt->close();


/* =====================================================
   STATUS
===================================================== */

$status = trim(
    $request["status"]
);


/* =====================================================
   PROGRESS
===================================================== */

$assignedDone =
    in_array(
        $status,
        [
            "Staff Assigned",
            "In Progress",
            "Completed"
        ]
    );

$progressDone =
    in_array(
        $status,
        [
            "In Progress",
            "Completed"
        ]
    );

$completedDone =
    ($status === "Completed");


/* =====================================================
   STAFF STATUS
===================================================== */

$hasStaff =
    !empty($request["assigned_staff"]) &&
    strtolower(
        trim($request["assigned_staff"])
    ) !== "not assigned";


$canStartWork =
    $hasStaff &&
    $status === "Staff Assigned";


$canComplete =
    $status === "In Progress";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Track Request - SmartCampus360
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background:
    linear-gradient(
        135deg,
        #667eea,
        #764ba2
    );

    min-height: 100vh;

    padding: 30px 15px;
}

.container {

    width: 100%;

    max-width: 950px;

    margin: auto;
}

.card {

    background: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 20px;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.15);
}

.header {

    text-align: center;
}

.header h1 {

    margin: 0;

    color: #333;
}

.track {

    display: inline-block;

    margin-top: 15px;

    padding: 10px 20px;

    border-radius: 30px;

    background: #eef0ff;

    color: #667eea;

    font-weight: bold;

    font-size: 18px;
}

.details {

    display: grid;

    grid-template-columns:
    repeat(2, 1fr);

    gap: 15px;
}

.detail {

    background: #f6f7fb;

    padding: 15px;

    border-radius: 10px;
}

.detail strong {

    display: block;

    color: #667eea;

    margin-bottom: 5px;
}

.progress {

    display: grid;

    grid-template-columns:
    repeat(4, 1fr);

    gap: 10px;
}

.step {

    text-align: center;

    padding: 18px 8px;

    background: #eee;

    border-radius: 10px;

    color: #777;

    font-weight: bold;
}

.step.done {

    background: #667eea;

    color: white;
}

select {

    width: 100%;

    padding: 13px;

    border: 1px solid #ddd;

    border-radius: 8px;

    margin-top: 8px;

    font-size: 15px;
}

button {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 8px;

    margin-top: 12px;

    background: #667eea;

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {

    opacity: 0.9;
}

.progress-btn {

    background: #f59e0b;
}

.complete-btn {

    background: #16a34a;
}

.info-message {

    background: #fff7ed;

    color: #9a3412;

    padding: 12px;

    border-radius: 8px;

    margin-top: 12px;

    font-weight: bold;
}

.success-message {

    background: #ecfdf5;

    color: #166534;

    padding: 12px;

    border-radius: 8px;

    margin-top: 12px;

    font-weight: bold;
}

.notification {

    padding: 15px;

    margin-bottom: 10px;

    border-left: 4px solid #667eea;

    background: #f7f7fb;

    border-radius: 8px;
}

.notification p {

    margin: 0 0 7px;

    color: #333;
}

.notification small {

    color: #777;
}

.home {

    display: inline-block;

    padding: 12px 20px;

    background: #333;

    color: white;

    text-decoration: none;

    border-radius: 8px;
}

.home:hover {

    background: #111;
}

@media(max-width:700px) {

    .details {

        grid-template-columns: 1fr;
    }

    .progress {

        grid-template-columns:
        repeat(2, 1fr);
    }
}

</style>

</head>

<body>

<div class="container">


<!-- =================================================
     HEADER
================================================= -->

<div class="card header">

    <h1>
        🔍 Track Your Request
    </h1>

    <div class="track">

        Track ID:
        <?php
        echo htmlspecialchars(
            $request["track_id"]
        );
        ?>

    </div>

</div>


<!-- =================================================
     REQUEST DETAILS
================================================= -->

<div class="card">

    <h2>
        📋 Request Details
    </h2>

    <div class="details">

        <div class="detail">

            <strong>
                Student Name
            </strong>

            <?php
            echo htmlspecialchars(
                $request["student_name"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Student Email
            </strong>

            <?php
            echo htmlspecialchars(
                $request["student_email"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Category
            </strong>

            <?php
            echo htmlspecialchars(
                $request["category"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Location
            </strong>

            <?php
            echo htmlspecialchars(
                $request["location"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Assigned Staff
            </strong>

            <?php

            if ($hasStaff) {

                echo htmlspecialchars(
                    $request["assigned_staff"]
                );

            } else {

                echo "Not Assigned";
            }

            ?>

        </div>


        <div class="detail">

            <strong>
                Current Status
            </strong>

            <?php
            echo htmlspecialchars(
                $request["status"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Created At
            </strong>

            <?php
            echo htmlspecialchars(
                $request["created_at"]
            );
            ?>

        </div>


        <div class="detail">

            <strong>
                Description
            </strong>

            <?php
            echo nl2br(
                htmlspecialchars(
                    $request["description"]
                )
            );
            ?>

        </div>

    </div>

</div>


<!-- =================================================
     PROGRESS
===================================================== -->

<div class="card">

    <h2>
        📊 Request Progress
    </h2>

    <div class="progress">

        <div class="step done">

            1️⃣<br>
            Submitted

        </div>


        <div class="step
        <?php
        echo $assignedDone
            ? "done"
            : "";
        ?>">

            2️⃣<br>
            Staff Assigned

        </div>


        <div class="step
        <?php
        echo $progressDone
            ? "done"
            : "";
        ?>">

            3️⃣<br>
            Work In Progress

        </div>


        <div class="step
        <?php
        echo $completedDone
            ? "done"
            : "";
        ?>">

            4️⃣<br>
            Completed

        </div>

    </div>

</div>


<!-- =================================================
     UPDATE REQUEST
===================================================== -->

<div class="card">

    <h2>
        ⚙️ Update Request
    </h2>


    <!-- ASSIGN STAFF -->

    <form method="POST">

        <input
            type="hidden"
            name="track_id"
            value="<?php
            echo htmlspecialchars(
                $request["track_id"]
            );
            ?>"
        >

        <input
            type="hidden"
            name="action"
            value="assign"
        >

        <strong>
            👨‍🔧 Assign Staff
        </strong>

        <select
            name="staff_id"
            required
        >

            <option value="">
                -- Select Staff --
            </option>

            <?php foreach (
                $staffList as $staff
            ): ?>

                <option
                    value="<?php
                    echo (int)$staff["id"];
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $staff["name"]
                    );
                    ?>

                    -

                    <?php
                    echo htmlspecialchars(
                        $staff["department"]
                    );
                    ?>

                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit">

            👨‍🔧 Assign Staff

        </button>

    </form>


    <!-- START WORK -->

    <?php if ($canStartWork): ?>

        <form method="POST">

            <input
                type="hidden"
                name="track_id"
                value="<?php
                echo htmlspecialchars(
                    $request["track_id"]
                );
                ?>"
            >

            <input
                type="hidden"
                name="action"
                value="progress"
            >

            <button
                type="submit"
                class="progress-btn"
            >

                🔧 Start Work / In Progress

            </button>

        </form>

    <?php elseif ($status === "In Progress"): ?>

        <div class="success-message">

            🔧 Work is currently In Progress.

        </div>

    <?php elseif ($status === "Completed"): ?>

        <div class="success-message">

            🎉 Request Completed Successfully.

        </div>

    <?php else: ?>

        <div class="info-message">

            ℹ️ Assign a staff member before starting work.

        </div>

    <?php endif; ?>


    <!-- COMPLETE -->

    <?php if ($canComplete): ?>

        <form method="POST">

            <input
                type="hidden"
                name="track_id"
                value="<?php
                echo htmlspecialchars(
                    $request["track_id"]
                );
                ?>"
            >

            <input
                type="hidden"
                name="action"
                value="complete"
            >

            <button
                type="submit"
                class="complete-btn"
            >

                ✅ Complete Request

            </button>

        </form>

    <?php endif; ?>

</div>


<!-- =================================================
     NOTIFICATIONS
===================================================== -->

<div class="card">

    <h2>

        🔔 Notifications for
        <?php
        echo htmlspecialchars(
            $request["track_id"]
        );
        ?>

    </h2>


    <?php if (
        count($notifications) > 0
    ): ?>

        <?php foreach (
            $notifications as $notification
        ): ?>

            <div class="notification">

                <p>

                    <?php
                    echo htmlspecialchars(
                        $notification["message"]
                    );
                    ?>

                </p>

                <small>

                    <?php
                    echo htmlspecialchars(
                        $notification["created_at"]
                    );
                    ?>

                </small>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>
            🔔 No notifications for this Track ID yet.
        </p>

    <?php endif; ?>

</div>


<!-- =================================================
     BACK HOME
===================================================== -->

<a
    href="index.html"
    class="home"
>
    🏠 Back to Home
</a>


</div>

</body>

</html>
```