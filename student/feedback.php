```php
<?php

session_start();

require_once "../config/database.php";

/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}


/* =========================================
   ONLY POST REQUEST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: feedback.html");
    exit;
}


/* =========================================
   GET STUDENT DETAILS
========================================= */

$userId = (int)($_SESSION["user_id"] ?? 0);

$studentName = $_SESSION["name"] ?? "";

$studentEmail = $_SESSION["email"] ?? "";


/* =========================================
   GET FORM DATA
========================================= */

$trackId = trim($_POST["track_id"] ?? "");

$rating = (int)($_POST["rating"] ?? 0);

$comments = trim($_POST["comments"] ?? "");


/* =========================================
   VALIDATION
========================================= */

if ($trackId === "") {
    showMessage(
        "Track ID Required",
        "Please enter your Track ID.",
        "feedback.html",
        "❌"
    );
    exit;
}


if ($rating < 1 || $rating > 5) {
    showMessage(
        "Rating Required",
        "Please select a rating from 1 to 5 stars.",
        "feedback.html",
        "❌"
    );
    exit;
}


if ($comments === "") {
    showMessage(
        "Comments Required",
        "Please enter your feedback comments.",
        "feedback.html",
        "❌"
    );
    exit;
}


/* =========================================
   FIND REQUEST
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        track_id,
        student_name,
        student_email,
        status
    FROM requests
    WHERE LOWER(TRIM(track_id)) = LOWER(TRIM(?))
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $trackId);

$stmt->execute();

$result = $stmt->get_result();

$request = $result->fetch_assoc();

$stmt->close();


/* =========================================
   TRACK ID NOT FOUND
========================================= */

if (!$request) {

    showMessage(
        "Track ID Not Found",
        "The Track ID you entered does not exist. Please check your Track ID and try again.",
        "feedback.html",
        "❌"
    );

    exit;
}


/* =========================================
   CHECK REQUEST STATUS
========================================= */

$status = strtolower(trim($request["status"] ?? ""));

if ($status !== "completed") {

    $currentStatus = htmlspecialchars(
        $request["status"] ?? "Unknown",
        ENT_QUOTES,
        "UTF-8"
    );

    showMessage(
        "Request Not Completed",
        "Feedback can be submitted only after your request is completed.<br><br>
        <strong>Current Status:</strong> " . $currentStatus,
        "feedback.html",
        "⏳"
    );

    exit;
}


/* =========================================
   USE REQUEST DETAILS IF SESSION EMPTY
========================================= */

if ($studentName === "") {
    $studentName = $request["student_name"] ?? "";
}

if ($studentEmail === "") {
    $studentEmail = $request["student_email"] ?? "";
}


/* =========================================
   CREATE FEEDBACK TABLE
========================================= */

$createTable = $conn->query("
    CREATE TABLE IF NOT EXISTS feedback (

        id INT AUTO_INCREMENT PRIMARY KEY,

        user_id INT NULL,

        student_name VARCHAR(150) NULL,

        student_email VARCHAR(150) NULL,

        track_id VARCHAR(100) NOT NULL,

        rating INT NOT NULL,

        comments TEXT NOT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )
");

if (!$createTable) {
    die(
        "Unable to create feedback table: "
        . $conn->error
    );
}


/* =========================================
   CHECK IF FEEDBACK ALREADY EXISTS
========================================= */

$stmt = $conn->prepare("
    SELECT id
    FROM feedback
    WHERE LOWER(TRIM(track_id)) = LOWER(TRIM(?))
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param("s", $trackId);

    $stmt->execute();

    $existingResult = $stmt->get_result();

    $existingFeedback = $existingResult->fetch_assoc();

    $stmt->close();

    if ($existingFeedback) {

        showMessage(
            "Feedback Already Submitted",
            "You have already submitted feedback for this Track ID.",
            "dashboard.php",
            "ℹ️"
        );

        exit;
    }
}


/* =========================================
   INSERT FEEDBACK
========================================= */

$stmt = $conn->prepare("
    INSERT INTO feedback
    (
        user_id,
        student_name,
        student_email,
        track_id,
        rating,
        comments
    )
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die(
        "Unable to prepare feedback: "
        . $conn->error
    );
}


$stmt->bind_param(
    "isssis",
    $userId,
    $studentName,
    $studentEmail,
    $trackId,
    $rating,
    $comments
);


if (!$stmt->execute()) {

    die(
        "Unable to save feedback: "
        . $stmt->error
    );
}


$stmt->close();


/* =========================================
   SUCCESS PAGE
========================================= */

$safeTrackId = htmlspecialchars(
    $trackId,
    ENT_QUOTES,
    "UTF-8"
);

$safeComments = htmlspecialchars(
    $comments,
    ENT_QUOTES,
    "UTF-8"
);

$stars = str_repeat("⭐", $rating);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Feedback Submitted - SmartCampus360
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #e0e7ff
                );

            padding: 20px;
        }

        .success-box {

            width: 100%;

            max-width: 520px;

            background: white;

            padding: 35px;

            border-radius: 20px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.15);
        }

        .success-icon {

            font-size: 65px;

            margin-bottom: 10px;
        }

        h1 {

            color: #16a34a;

            margin: 10px 0;

            font-size: 30px;
        }

        .message {

            color: #555;

            line-height: 1.6;

            margin-bottom: 25px;
        }

        .details {

            background: #f8f9ff;

            border-radius: 12px;

            padding: 18px;

            text-align: left;

            margin-bottom: 20px;
        }

        .details p {

            margin: 10px 0;

            color: #333;

        }

        .stars {

            color: #f59e0b;

            font-size: 22px;

            letter-spacing: 2px;
        }

        .button {

            display: inline-block;

            padding: 12px 22px;

            background: #4f46e5;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            font-weight: bold;

            margin-top: 10px;
        }

        .button:hover {

            background: #4338ca;
        }

    </style>

</head>


<body>


<div class="success-box">


    <div class="success-icon">
        ✅
    </div>


    <h1>
        Feedback Submitted!
    </h1>


    <p class="message">

        Thank you for sharing your feedback.

        <br>

        Your feedback has been successfully
        submitted to SmartCampus360.

    </p>


    <div class="details">

        <p>
            <strong>Track ID:</strong>
            <?php echo $safeTrackId; ?>
        </p>


        <p>
            <strong>Rating:</strong>

            <span class="stars">
                <?php echo $stars; ?>
            </span>
        </p>


        <p>
            <strong>Comments:</strong><br>

            <?php echo nl2br($safeComments); ?>

        </p>

    </div>


    <a
        href="dashboard.php"
        class="button"
    >
        ← Student Dashboard
    </a>


</div>


</body>

</html>


<?php

/* =========================================
   MESSAGE PAGE FUNCTION
========================================= */

function showMessage(
    $title,
    $message,
    $backLink,
    $icon
) {

    $safeTitle = htmlspecialchars(
        $title,
        ENT_QUOTES,
        "UTF-8"
    );

    $safeLink = htmlspecialchars(
        $backLink,
        ENT_QUOTES,
        "UTF-8"
    );

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            <?php echo $safeTitle; ?>
        </title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {

                margin: 0;

                min-height: 100vh;

                display: flex;

                align-items: center;

                justify-content: center;

                font-family: Arial, sans-serif;

                background:
                    linear-gradient(
                        135deg,
                        #eef2ff,
                        #e0e7ff
                    );

                padding: 20px;
            }

            .box {

                width: 100%;

                max-width: 500px;

                background: white;

                padding: 35px;

                border-radius: 18px;

                text-align: center;

                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,0.12);
            }

            .icon {

                font-size: 55px;

                margin-bottom: 10px;
            }

            h1 {

                color: #4f46e5;

                margin-bottom: 15px;
            }

            p {

                color: #555;

                line-height: 1.6;
            }

            a {

                display: inline-block;

                margin-top: 20px;

                padding: 12px 22px;

                background: #4f46e5;

                color: white;

                text-decoration: none;

                border-radius: 8px;

                font-weight: bold;
            }

            a:hover {

                background: #4338ca;
            }

        </style>

    </head>


    <body>


        <div class="box">


            <div class="icon">

                <?php echo $icon; ?>

            </div>


            <h1>

                <?php echo $safeTitle; ?>

            </h1>


            <p>

                <?php echo $message; ?>

            </p>


            <a href="<?php echo $safeLink; ?>">

                ← Back

            </a>


        </div>


    </body>

    </html>

    <?php
}

?>
```