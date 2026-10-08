<?php

session_start();

require_once "../config/database.php";

/* Check whether student is logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

/* Student details */
$studentName = $_SESSION["name"] ?? "Student";
$studentEmail = $_SESSION["email"] ?? "";

/* Get student request counts */
$totalRequests = 0;
$pendingRequests = 0;
$inProgressRequests = 0;
$completedRequests = 0;

/* Total requests */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
");

if ($stmt) {

    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalRequests = (int)$row["total"];
    }

    $stmt->close();
}


/* Pending / Submitted requests */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
    AND LOWER(TRIM(status)) = 'submitted'
");

if ($stmt) {

    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $pendingRequests = (int)$row["total"];
    }

    $stmt->close();
}


/* In Progress requests */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
    AND LOWER(TRIM(status)) = 'in progress'
");

if ($stmt) {

    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $inProgressRequests = (int)$row["total"];
    }

    $stmt->close();
}


/* Completed requests */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
    AND LOWER(TRIM(status)) = 'completed'
");

if ($stmt) {

    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $completedRequests = (int)$row["total"];
    }

    $stmt->close();
}


/* Unread notifications */
$unreadNotifications = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE LOWER(TRIM(user_email)) = LOWER(TRIM(?))
    AND (is_read = 0 OR is_read IS NULL)
");

if ($stmt) {

    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $unreadNotifications = (int)$row["total"];
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

    <title>Student Dashboard - SmartCampus360</title>

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
                url("../images/college.jpg");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;

            color: #222;
        }


        /* NAVBAR */

        .navbar {
            width: 100%;
            background: rgba(255, 255, 255, 0.96);

            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 16px 35px;

            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.15);

            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 23px;
            font-weight: bold;
            color: #4f46e5;
        }

        .welcome {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .welcome span {
            font-weight: bold;
            color: #333;
        }

        .logout {
            text-decoration: none;
            background: #ef4444;
            color: white;

            padding: 9px 17px;

            border-radius: 8px;

            font-weight: bold;

            transition: 0.3s;
        }

        .logout:hover {
            background: #dc2626;
        }


        /* MAIN */

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 40px 25px;
        }


        .welcome-box {
            background: rgba(255, 255, 255, 0.96);

            border-radius: 20px;

            padding: 30px;

            margin-bottom: 30px;

            text-align: center;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.20);
        }

        .welcome-box h1 {
            color: #4f46e5;
            margin-bottom: 10px;
        }

        .welcome-box p {
            color: #555;
            font-size: 16px;
        }


        /* STAT CARDS */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }

        .stat-card {
            background: white;

            border-radius: 18px;

            padding: 25px;

            text-align: center;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.15);

            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #4f46e5;
        }

        .stat-title {
            margin-top: 8px;
            color: #666;
            font-weight: bold;
        }


        /* MENU */

        .menu-title {
            color: white;

            text-align: center;

            margin-bottom: 20px;

            font-size: 25px;
        }

        .menu-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }

        .menu-card {
            background: rgba(255, 255, 255, 0.97);

            border-radius: 18px;

            padding: 25px;

            text-align: center;

            text-decoration: none;

            color: #222;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.15);

            transition: 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-6px);

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.22);
        }

        .menu-icon {
            font-size: 42px;

            margin-bottom: 12px;
        }

        .menu-card h3 {
            color: #4f46e5;

            margin-bottom: 8px;
        }

        .menu-card p {
            color: #666;

            font-size: 14px;
        }


        /* NOTIFICATION BADGE */

        .notification-wrapper {
            position: relative;
            display: inline-block;
        }

        .badge {
            position: absolute;

            top: -8px;
            right: -8px;

            background: #ef4444;

            color: white;

            min-width: 22px;
            height: 22px;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 12px;

            font-weight: bold;

            padding: 3px;
        }


        /* RESPONSIVE */

        @media (max-width: 900px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .menu-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            .navbar {
                padding: 15px;
            }

            .welcome {
                gap: 8px;
            }

            .welcome span {
                display: none;
            }

            .container {
                padding: 25px 15px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        🏫 SmartCampus360
    </div>

    <div class="welcome">

        <span>
            Welcome,
            <?php echo htmlspecialchars($studentName); ?>
        </span>

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">


    <!-- WELCOME -->

    <div class="welcome-box">

        <h1>
            👋 Welcome,
            <?php echo htmlspecialchars($studentName); ?>!
        </h1>

        <p>
            Manage your campus requests and track their progress easily.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                📋
            </div>

            <div class="stat-number">
                <?php echo $totalRequests; ?>
            </div>

            <div class="stat-title">
                Total Requests
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⏳
            </div>

            <div class="stat-number">
                <?php echo $pendingRequests; ?>
            </div>

            <div class="stat-title">
                Submitted
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🔧
            </div>

            <div class="stat-number">
                <?php echo $inProgressRequests; ?>
            </div>

            <div class="stat-title">
                In Progress
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <div class="stat-number">
                <?php echo $completedRequests; ?>
            </div>

            <div class="stat-title">
                Completed
            </div>

        </div>

    </div>


    <!-- MENU -->

    <h2 class="menu-title">
        Student Services
    </h2>


    <div class="menu-grid">


        <!-- Report Issue -->

        <a
            href="../report.html"
            class="menu-card"
        >

            <div class="menu-icon">
                📝
            </div>

            <h3>
                Report Issue
            </h3>

            <p>
                Submit a new campus maintenance request.
            </p>

        </a>


        <!-- My Requests -->

        <a
            href="my-requests.php"
            class="menu-card"
        >

            <div class="menu-icon">
                📋
            </div>

            <h3>
                My Requests
            </h3>

            <p>
                View all your submitted requests.
            </p>

        </a>


        <!-- Track Request -->

        <a
            href="../track.html"
            class="menu-card"
        >

            <div class="menu-icon">
                🔍
            </div>

            <h3>
                Track Request
            </h3>

            <p>
                Check the current status of your request.
            </p>

        </a>


        <!-- Notifications -->

        <a
            href="notifications.php"
            class="menu-card notification-wrapper"
        >

            <div class="menu-icon">
                🔔
            </div>

            <h3>
                Notifications
            </h3>

            <p>
                View updates about your requests.
            </p>

            <?php if ($unreadNotifications > 0): ?>

                <span class="badge">
                    <?php echo $unreadNotifications; ?>
                </span>

            <?php endif; ?>

        </a>


        <!-- Feedback -->

        <a
            href="feedback.html"
            class="menu-card"
        >

            <div class="menu-icon">
                💬
            </div>

            <h3>
                Feedback
            </h3>

            <p>
                Share your feedback about SmartCampus360.
            </p>

        </a>


        <!-- Announcements -->

        <a
            href="../announcements.php"
            class="menu-card"
        >

            <div class="menu-icon">
                📢
            </div>

            <h3>
                Announcements
            </h3>

            <p>
                View the latest campus announcements.
            </p>

        </a>


        <!-- Home -->

        <a
            href="../index.html"
            class="menu-card"
        >

            <div class="menu-icon">
                🏠
            </div>

            <h3>
                Home
            </h3>

            <p>
                Return to the SmartCampus360 home page.
            </p>

        </a>


    </div>

</div>


</body>

</html>