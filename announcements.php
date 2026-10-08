<?php

session_start();

require_once __DIR__ . "/config/database.php";

/* ==============================
   SESSION
============================== */

$userId = isset($_SESSION["user_id"])
    ? (int)$_SESSION["user_id"]
    : 0;

$role = isset($_SESSION["role"])
    ? strtolower(trim($_SESSION["role"]))
    : "";

/*
|--------------------------------------------------------------------------
| USER MUST BE LOGGED IN
|--------------------------------------------------------------------------
*/
if ($userId <= 0) {
    header("Location: login.html");
    exit;
}

/* ==============================
   CREATE READ TABLE
============================== */

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

$conn->query($createTable);

/* ==============================
   MARK ONE ANNOUNCEMENT AS READ
============================== */

if (isset($_GET["mark_read"])) {

    $announcementId = (int)$_GET["mark_read"];

    if ($announcementId > 0) {

        /*
        Check that announcement really exists
        */
        $checkAnnouncement = $conn->prepare("
            SELECT id
            FROM announcements
            WHERE id = ?
            LIMIT 1
        ");

        if ($checkAnnouncement) {

            $checkAnnouncement->bind_param(
                "i",
                $announcementId
            );

            $checkAnnouncement->execute();

            $checkResult =
                $checkAnnouncement->get_result();

            if ($checkResult->num_rows > 0) {

                /*
                INSERT IGNORE prevents duplicate
                read records.
                */

                $stmt = $conn->prepare("
                    INSERT IGNORE INTO announcement_reads
                    (announcement_id, user_id)
                    VALUES (?, ?)
                ");

                if ($stmt) {

                    $stmt->bind_param(
                        "ii",
                        $announcementId,
                        $userId
                    );

                    $stmt->execute();

                    $stmt->close();
                }
            }

            $checkAnnouncement->close();
        }
    }

    header("Location: announcements.php");
    exit;
}

/* ==============================
   MARK ALL ANNOUNCEMENTS AS READ
============================== */

if (isset($_GET["mark_all"])) {

    /*
    Get every announcement
    */

    $result = $conn->query("
        SELECT id
        FROM announcements
    ");

    if ($result) {

        $stmt = $conn->prepare("
            INSERT IGNORE INTO announcement_reads
            (announcement_id, user_id)
            VALUES (?, ?)
        ");

        if ($stmt) {

            while ($row = $result->fetch_assoc()) {

                $announcementId =
                    (int)$row["id"];

                $stmt->bind_param(
                    "ii",
                    $announcementId,
                    $userId
                );

                $stmt->execute();
            }

            $stmt->close();
        }
    }

    header("Location: announcements.php");
    exit;
}

/* ==============================
   GET ALL ANNOUNCEMENTS
============================== */

$announcements = [];

$result = $conn->query("
    SELECT
        id,
        title,
        message,
        created_at
    FROM announcements
    ORDER BY created_at DESC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $announcements[] = $row;
    }
}

/* ==============================
   COUNT UNREAD ANNOUNCEMENTS
============================== */

$unreadCount = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM announcements a
    LEFT JOIN announcement_reads ar
        ON a.id = ar.announcement_id
        AND ar.user_id = ?
    WHERE ar.id IS NULL
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $unreadCount =
            (int)$row["total"];
    }

    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

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

    background-image:
        linear-gradient(
            rgba(20, 40, 100, 0.60),
            rgba(25, 35, 80, 0.68)
        ),
        url("images/college.jpg");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    color: #1e293b;
}

/* NAVBAR */

.navbar {

    width: 100%;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    padding: 18px 40px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    color: white;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.15);
}

.logo {

    font-size: 23px;

    font-weight: bold;
}

.home {

    color: white;

    text-decoration: none;

    background:
        rgba(255,255,255,0.20);

    padding: 10px 18px;

    border-radius: 10px;

    font-weight: bold;
}

.home:hover {

    background:
        rgba(255,255,255,0.30);
}

/* CONTAINER */

.container {

    width: 90%;

    max-width: 1000px;

    margin: 45px auto;
}

/* HEADER */

.header {

    text-align: center;

    margin-bottom: 30px;
}

.header h1 {

    color: #ffffff;

    font-size: 35px;

    margin-bottom: 10px;

    text-shadow:
        0 2px 5px rgba(0,0,0,0.25);
}

.header p {

    color: #f1f5f9;

    font-size: 16px;
}

/* TOP BAR */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    flex-wrap: wrap;

    gap: 15px;

    margin-bottom: 25px;
}

.unread {

    background: white;

    padding: 13px 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);

    font-weight: bold;
}

.unread span {

    color: #dc2626;

    font-size: 18px;
}

.mark-all {

    background:
        linear-gradient(
            135deg,
            #059669,
            #10b981
        );

    color: white;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 10px;

    font-weight: bold;
}

.mark-all:hover {

    transform: translateY(-2px);
}

/* CARD */

.card {

    background: white;

    padding: 25px;

    border-radius: 18px;

    margin-bottom: 20px;

    border-left: 6px solid #ef4444;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.10);
}

/* TITLE */

.card-title {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;
}

.card h2 {

    color: #312e81;

    font-size: 22px;

    margin-bottom: 8px;
}

.date {

    color: #64748b;

    font-size: 13px;
}

/* BADGES */

.new {

    background: #ef4444;

    color: white;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

    white-space: nowrap;
}

.read {

    background: #dcfce7;

    color: #166534;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

    white-space: nowrap;
}

/* MESSAGE */

.message {

    margin-top: 18px;

    color: #475569;

    line-height: 1.7;

    font-size: 16px;
}

/* MARK BUTTON */

.mark-button {

    display: inline-block;

    margin-top: 20px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 10px;

    font-weight: bold;

    box-shadow:
        0 4px 12px rgba(37,99,235,0.25);

    transition: 0.2s;
}

.mark-button:hover {

    transform: translateY(-2px);
}

/* EMPTY */

.empty {

    background: white;

    padding: 50px;

    text-align: center;

    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);
}

.empty-icon {

    font-size: 55px;

    margin-bottom: 15px;
}

.empty h2 {

    color: #334155;

    margin-bottom: 10px;
}

.empty p {

    color: #64748b;
}

/* MOBILE */

@media(max-width:600px) {

    .navbar {

        padding: 15px 20px;
    }

    .logo {

        font-size: 18px;
    }

    .container {

        width: 94%;

        margin: 30px auto;
    }

    .header h1 {

        font-size: 28px;
    }

    .card-title {

        flex-direction: column;
    }
}

</style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        🏫 SmartCampus360
    </div>

    <a
        href="index.html"
        class="home"
    >
        🏠 Home
    </a>

</div>


<!-- MAIN -->

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <h1>
            📢 Campus Announcements
        </h1>

        <p>
            Stay updated with the latest campus information.
        </p>

    </div>


    <!-- UNREAD COUNT -->

    <div class="topbar">

        <div class="unread">

            🔔 Unread Announcements:

            <span>
                <?php echo $unreadCount; ?>
            </span>

        </div>


        <?php if ($unreadCount > 0): ?>

            <a
                href="announcements.php?mark_all=1"
                class="mark-all"
            >
                ✓ Mark All as Read
            </a>

        <?php endif; ?>

    </div>


    <!-- ANNOUNCEMENTS -->

    <?php if (count($announcements) > 0): ?>

        <?php foreach ($announcements as $announcement): ?>

            <?php

            $announcementId =
                (int)$announcement["id"];

            /*
            |--------------------------------------------------------------------------
            | CHECK WHETHER THIS ANNOUNCEMENT IS READ
            |--------------------------------------------------------------------------
            */

            $isRead = false;

            $check = $conn->prepare("
                SELECT id
                FROM announcement_reads
                WHERE announcement_id = ?
                AND user_id = ?
                LIMIT 1
            ");

            if ($check) {

                $check->bind_param(
                    "ii",
                    $announcementId,
                    $userId
                );

                $check->execute();

                $checkResult =
                    $check->get_result();

                if ($checkResult->num_rows > 0) {

                    $isRead = true;
                }

                $check->close();
            }

            ?>

            <div class="card">

                <!-- TITLE AND STATUS -->

                <div class="card-title">

                    <div>

                        <h2>

                            📌

                            <?php

                            echo htmlspecialchars(
                                $announcement["title"]
                            );

                            ?>

                        </h2>

                        <div class="date">

                            📅

                            <?php

                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $announcement["created_at"]
                                )
                            );

                            ?>

                        </div>

                    </div>


                    <?php if ($isRead): ?>

                        <span class="read">
                            ✓ READ
                        </span>

                    <?php else: ?>

                        <span class="new">
                            NEW
                        </span>

                    <?php endif; ?>

                </div>


                <!-- ANNOUNCEMENT MESSAGE -->

                <div class="message">

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $announcement["message"]
                        )
                    );

                    ?>

                </div>


                <!-- MARK AS READ -->

                <?php if (!$isRead): ?>

                    <a
                        href="announcements.php?mark_read=<?php echo $announcementId; ?>"
                        class="mark-button"
                    >
                        ✓ Mark as Read
                    </a>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">

            <div class="empty-icon">
                📢
            </div>

            <h2>
                No Announcements
            </h2>

            <p>
                There are currently no campus announcements.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>

</html>

<?php

$conn->close();

?>