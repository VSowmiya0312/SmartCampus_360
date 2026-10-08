<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

if ($_SESSION["role"] !== "student") {
    header("Location: ../login.html");
    exit;
}

require_once "../config/database.php";

$studentId = (int)$_SESSION["user_id"];
$studentName = $_SESSION["name"] ?? "Student";

/*
|--------------------------------------------------------------------------
| Mark announcement as read
|--------------------------------------------------------------------------
*/

if (isset($_GET["read"])) {

    $announcementId = (int)$_GET["read"];

    if ($announcementId > 0) {

        $stmt = $conn->prepare("
            INSERT IGNORE INTO announcement_reads
            (announcement_id, user_id)
            VALUES (?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $announcementId,
                $studentId
            );

            $stmt->execute();

            $stmt->close();
        }
    }

    header("Location: announcements.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Mark ALL announcements as read
|--------------------------------------------------------------------------
*/

if (isset($_GET["read_all"])) {

    $stmt = $conn->prepare("
        INSERT IGNORE INTO announcement_reads
        (announcement_id, user_id)

        SELECT
            a.id,
            ?

        FROM announcements a
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $studentId
        );

        $stmt->execute();

        $stmt->close();
    }

    header("Location: announcements.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get announcements
|--------------------------------------------------------------------------
*/

$announcements = [];

$stmt = $conn->prepare("
    SELECT
        a.id,
        a.title,
        a.message,
        a.created_at,

        CASE
            WHEN ar.id IS NULL THEN 0
            ELSE 1
        END AS is_read

    FROM announcements a

    LEFT JOIN announcement_reads ar
        ON a.id = ar.announcement_id
        AND ar.user_id = ?

    ORDER BY a.created_at DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $studentId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $announcements[] = $row;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Announcements - SmartCampus360</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {

    font-family: Arial, sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2,
            #ec4899
        );

    padding: 30px;
}

.container {

    max-width: 950px;

    margin: auto;
}

/* HEADER */

.header {

    background: white;

    padding: 25px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.20);

    margin-bottom: 25px;
}

.header h1 {

    color: #333;

    margin-bottom: 8px;
}

.header p {

    color: #777;

    font-size: 14px;
}

/* BUTTONS */

.buttons {

    display: flex;

    justify-content: center;

    gap: 12px;

    flex-wrap: wrap;

    margin-bottom: 25px;
}

.btn {

    text-decoration: none;

    padding: 12px 18px;

    border-radius: 10px;

    font-weight: bold;

    color: white;

    transition: 0.3s;
}

.btn:hover {

    transform: translateY(-2px);

}

.home {

    background: #3b82f6;
}

.read-all {

    background: #10b981;
}

/* ANNOUNCEMENT */

.announcement {

    background: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 18px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.18);

    position: relative;

    border-left: 6px solid #10b981;
}

.announcement.unread {

    border-left-color: #ef4444;

    background: #fff;
}

.announcement.read {

    opacity: 0.75;
}

.badge {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

    margin-bottom: 12px;
}

.new {

    background: #fee2e2;

    color: #dc2626;
}

.read-badge {

    background: #dcfce7;

    color: #16a34a;
}

.announcement h2 {

    color: #333;

    margin-bottom: 10px;

    font-size: 21px;
}

.message {

    color: #555;

    line-height: 1.6;

    margin-bottom: 15px;
}

.date {

    color: #888;

    font-size: 13px;

    margin-bottom: 15px;
}

.read-button {

    display: inline-block;

    text-decoration: none;

    background: #2563eb;

    color: white;

    padding: 9px 15px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;
}

.read-button:hover {

    background: #1d4ed8;
}

/* EMPTY */

.empty {

    background: white;

    padding: 50px 20px;

    border-radius: 20px;

    text-align: center;

    color: #777;

    box-shadow:
        0 10px 25px rgba(0,0,0,0.18);
}

/* FOOTER */

.footer {

    text-align: center;

    color: white;

    margin-top: 30px;

    font-size: 14px;
}

</style>

</head>

<body>

<div class="container">


    <!-- HEADER -->

    <div class="header">

        <h1>
            📢 Campus Announcements
        </h1>

        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($studentName); ?>
            </strong>
        </p>

    </div>


    <!-- BUTTONS -->

    <div class="buttons">

        <a
            href="dashboard.php"
            class="btn home"
        >
            ← Back to Dashboard
        </a>


        <?php if (!empty($announcements)): ?>

            <a
                href="announcements.php?read_all=1"
                class="btn read-all"
                onclick="return confirm('Mark all announcements as read?');"
            >
                ✓ Mark All as Read
            </a>

        <?php endif; ?>

    </div>


    <!-- ANNOUNCEMENTS -->

    <?php if (empty($announcements)): ?>

        <div class="empty">

            <h2>
                📢 No Announcements
            </h2>

            <p>
                There are currently no campus announcements.
            </p>

        </div>

    <?php else: ?>


        <?php foreach ($announcements as $announcement): ?>

            <div
                class="announcement
                <?php
                echo ($announcement["is_read"] == 0)
                    ? "unread"
                    : "read";
                ?>"
            >


                <?php if ($announcement["is_read"] == 0): ?>

                    <span class="badge new">
                        🔴 NEW
                    </span>

                <?php else: ?>

                    <span class="badge read-badge">
                        ✓ READ
                    </span>

                <?php endif; ?>


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $announcement["title"]
                    );
                    ?>

                </h2>


                <div class="message">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $announcement["message"]
                        )
                    );
                    ?>

                </div>


                <div class="date">

                    🕒 Published:
                    <?php
                    echo htmlspecialchars(
                        $announcement["created_at"]
                    );
                    ?>

                </div>


                <?php if ($announcement["is_read"] == 0): ?>

                    <a
                        href="announcements.php?read=<?php echo (int)$announcement["id"]; ?>"
                        class="read-button"
                    >
                        ✓ Mark as Read
                    </a>

                <?php endif; ?>


            </div>

        <?php endforeach; ?>


    <?php endif; ?>


    <div class="footer">

        SmartCampus360 © 2026

    </div>


</div>

</body>

</html>