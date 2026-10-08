<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.html");
    exit;
}

require_once "../config/database.php";

$userId = (int)$_SESSION["user_id"];
$userName = $_SESSION["name"] ?? "Student";


/* =========================================================
   MARK ONE ANNOUNCEMENT AS READ
   ========================================================= */

if (isset($_GET["mark_read"])) {

    $announcementId = (int)$_GET["mark_read"];

    if ($announcementId > 0) {

        /*
         * Check whether this announcement exists
         */

        $check = $conn->prepare(
            "SELECT id FROM announcements WHERE id = ? LIMIT 1"
        );

        $check->bind_param("i", $announcementId);

        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            /*
             * Save read status
             *
             * INSERT IGNORE prevents duplicate rows.
             */

            $insert = $conn->prepare(
                "INSERT IGNORE INTO announcement_reads
                (announcement_id, user_id)
                VALUES (?, ?)"
            );

            $insert->bind_param(
                "ii",
                $announcementId,
                $userId
            );

            $insert->execute();

            $insert->close();
        }

        $check->close();
    }

    /*
     * Reload the page after marking as read
     */

    header("Location: announcements.php");
    exit;
}


/* =========================================================
   MARK ALL AS READ
   ========================================================= */

if (isset($_GET["mark_all"])) {

    $all = $conn->prepare(
        "INSERT IGNORE INTO announcement_reads
        (announcement_id, user_id)
        SELECT id, ? FROM announcements"
    );

    $all->bind_param(
        "i",
        $userId
    );

    $all->execute();

    $all->close();

    header("Location: announcements.php");
    exit;
}


/* =========================================================
   GET ANNOUNCEMENTS
   ========================================================= */

$announcements = [];

$sql = "
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

    ORDER BY a.id DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $announcements[] = $row;
}

$stmt->close();


/* =========================================================
   COUNT UNREAD
   ========================================================= */

$unreadCount = 0;

foreach ($announcements as $announcement) {

    if ((int)$announcement["is_read"] === 0) {

        $unreadCount++;
    }
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

    background: linear-gradient(
        135deg,
        #667eea,
        #764ba2,
        #ec4899
    );

    padding: 30px 15px;
}

.container {

    max-width: 950px;

    margin: auto;
}


/* HEADER */

.header {

    background: white;

    padding: 28px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.20);

    margin-bottom: 25px;
}

.header h1 {

    color: #222;

    font-size: 30px;

    margin-bottom: 8px;
}

.header p {

    color: #777;

    font-size: 15px;
}


/* TOP BUTTONS */

.top-buttons {

    display: flex;

    justify-content: center;

    gap: 12px;

    flex-wrap: wrap;

    margin-bottom: 20px;
}

.top-button {

    text-decoration: none;

    color: white;

    padding: 12px 20px;

    border-radius: 10px;

    font-weight: bold;

    display: inline-block;
}

.dashboard-button {

    background: #2563eb;
}

.all-button {

    background: #16a34a;
}


/* COUNT BOX */

.count-box {

    background: white;

    padding: 15px;

    border-radius: 15px;

    text-align: center;

    margin-bottom: 20px;

    font-size: 16px;

    font-weight: bold;

    color: #333;
}


/* ANNOUNCEMENT */

.announcement {

    background: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 20px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.18);

    border-left: 7px solid #22c55e;
}

.announcement.unread {

    border-left-color: #ef4444;
}

.announcement.read {

    border-left-color: #22c55e;
}


/* BADGE */

.badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

    margin-bottom: 12px;
}

.new-badge {

    background: #fee2e2;

    color: #dc2626;
}

.read-badge {

    background: #dcfce7;

    color: #15803d;
}


/* TITLE */

.announcement h2 {

    color: #222;

    font-size: 21px;

    margin-bottom: 12px;
}


/* MESSAGE */

.message {

    color: #555;

    font-size: 15px;

    line-height: 1.7;

    margin-bottom: 12px;
}


/* DATE */

.date {

    color: #888;

    font-size: 13px;

    margin-bottom: 18px;
}


/* MARK READ BUTTON */

.mark-read-button {

    display: inline-block;

    background: #2563eb;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 9px;

    font-weight: bold;

    font-size: 14px;

    transition: 0.3s;
}

.mark-read-button:hover {

    background: #1d4ed8;

    transform: translateY(-2px);
}


/* READ STATUS */

.already-read {

    display: inline-block;

    background: #dcfce7;

    color: #15803d;

    padding: 11px 18px;

    border-radius: 9px;

    font-weight: bold;

    font-size: 14px;
}


/* EMPTY */

.empty {

    background: white;

    padding: 50px 20px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 10px 25px rgba(0,0,0,0.18);
}

.empty h2 {

    color: #333;

    margin-bottom: 10px;
}

.empty p {

    color: #777;
}


/* FOOTER */

.footer {

    text-align: center;

    color: white;

    margin-top: 30px;

    font-size: 14px;
}


/* MOBILE */

@media (max-width: 600px) {

    body {
        padding: 15px 10px;
    }

    .header h1 {
        font-size: 24px;
    }

    .announcement {
        padding: 20px;
    }

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
                <?php echo htmlspecialchars($userName); ?>
            </strong>
        </p>

    </div>


    <!-- BUTTONS -->

    <div class="top-buttons">

        <a
            href="dashboard.php"
            class="top-button dashboard-button"
        >
            ← Back to Dashboard
        </a>


        <?php if ($unreadCount > 0): ?>

            <a
                href="announcements.php?mark_all=1"
                class="top-button all-button"
                onclick="return confirm('Mark all announcements as read?');"
            >
                ✓ Mark All as Read
            </a>

        <?php endif; ?>

    </div>


    <!-- UNREAD COUNT -->

    <div class="count-box">

        <?php if ($unreadCount > 0): ?>

            🔔
            <?php echo $unreadCount; ?>

            Unread Announcement<?php echo ($unreadCount > 1) ? "s" : ""; ?>

        <?php else: ?>

            ✅ All Announcements Read

        <?php endif; ?>

    </div>


    <!-- ANNOUNCEMENT LIST -->

    <?php if (count($announcements) == 0): ?>

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

            <?php

            $announcementId =
                (int)$announcement["id"];

            $isRead =
                (int)$announcement["is_read"] === 1;

            ?>


            <div
                class="announcement
                <?php
                echo $isRead
                    ? "read"
                    : "unread";
                ?>"
            >


                <!-- STATUS -->

                <?php if ($isRead): ?>

                    <span class="badge read-badge">
                        ✓ READ
                    </span>

                <?php else: ?>

                    <span class="badge new-badge">
                        🔴 NEW
                    </span>

                <?php endif; ?>


                <!-- TITLE -->

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $announcement["title"]
                    );

                    ?>

                </h2>


                <!-- MESSAGE -->

                <div class="message">

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $announcement["message"]
                        )
                    );

                    ?>

                </div>


                <!-- DATE -->

                <div class="date">

                    🕒 Published:

                    <?php

                    echo htmlspecialchars(
                        $announcement["created_at"]
                    );

                    ?>

                </div>


                <!-- BUTTON -->

                <?php if (!$isRead): ?>

                    <a
                        href="announcements.php?mark_read=<?php echo $announcementId; ?>"
                        class="mark-read-button"
                    >
                        ✓ Mark as Read
                    </a>

                <?php else: ?>

                    <span class="already-read">
                        ✓ Already Read
                    </span>

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