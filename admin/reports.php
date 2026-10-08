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


$adminName = $_SESSION["name"] ?? "Admin";


/* =========================================
   REQUEST COUNTS
========================================= */

$totalRequests = 0;
$submittedRequests = 0;
$assignedRequests = 0;
$inProgressRequests = 0;
$completedRequests = 0;


/* Total Requests */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
");

if ($result && $row = $result->fetch_assoc()) {

    $totalRequests = (int)$row["total"];

}


/* Submitted */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(status)) = 'submitted'
");

if ($result && $row = $result->fetch_assoc()) {

    $submittedRequests = (int)$row["total"];

}


/* Staff Assigned */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(status)) = 'staff assigned'
");

if ($result && $row = $result->fetch_assoc()) {

    $assignedRequests = (int)$row["total"];

}


/* In Progress */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(status)) = 'in progress'
");

if ($result && $row = $result->fetch_assoc()) {

    $inProgressRequests = (int)$row["total"];

}


/* Completed */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE LOWER(TRIM(status)) = 'completed'
");

if ($result && $row = $result->fetch_assoc()) {

    $completedRequests = (int)$row["total"];

}


/* =========================================
   USER COUNTS
========================================= */

$totalStudents = 0;
$totalStaff = 0;
$totalAdmins = 0;


/* Students */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE LOWER(TRIM(role)) = 'student'
");

if ($result && $row = $result->fetch_assoc()) {

    $totalStudents = (int)$row["total"];

}


/* Staff */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE LOWER(TRIM(role)) = 'staff'
");

if ($result && $row = $result->fetch_assoc()) {

    $totalStaff = (int)$row["total"];

}


/* Admins */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE LOWER(TRIM(role)) = 'admin'
");

if ($result && $row = $result->fetch_assoc()) {

    $totalAdmins = (int)$row["total"];

}


/* =========================================
   CATEGORY REPORT
========================================= */

$categoryResult = $conn->query("
    SELECT
        category,
        COUNT(*) AS total
    FROM requests
    WHERE category IS NOT NULL
    AND TRIM(category) <> ''
    GROUP BY category
    ORDER BY total DESC
");


/* =========================================
   LOCATION REPORT
========================================= */

$locationResult = $conn->query("
    SELECT
        location,
        COUNT(*) AS total
    FROM requests
    WHERE location IS NOT NULL
    AND TRIM(location) <> ''
    GROUP BY location
    ORDER BY total DESC
    LIMIT 10
");


/* =========================================
   STAFF PERFORMANCE
========================================= */

$staffResult = $conn->query("
    SELECT
        assigned_staff,
        COUNT(*) AS total_requests,
        SUM(
            CASE
                WHEN LOWER(TRIM(status)) = 'completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_requests
    FROM requests
    WHERE assigned_staff IS NOT NULL
    AND TRIM(assigned_staff) <> ''
    AND LOWER(TRIM(assigned_staff)) <> 'not assigned'
    GROUP BY assigned_staff
    ORDER BY completed_requests DESC, total_requests DESC
");


/* =========================================
   RECENT REQUESTS
========================================= */

$recentResult = $conn->query("
    SELECT
        track_id,
        student_name,
        category,
        location,
        status,
        assigned_staff,
        created_at
    FROM requests
    ORDER BY id DESC
    LIMIT 10
");


/* =========================================
   COMPLETION PERCENTAGE
========================================= */

$completionPercentage = 0;

if ($totalRequests > 0) {

    $completionPercentage = round(
        ($completedRequests / $totalRequests) * 100
    );

}


/* =========================================
   PENDING COUNT
========================================= */

$pendingRequests =
    $submittedRequests +
    $assignedRequests +
    $inProgressRequests;

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
        Admin Reports - SmartCampus360
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
                    #eef2ff,
                    #f8fafc
                );

            color: #1f2937;
        }


        /* =================================
           HEADER
        ================================= */

        .header {

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #6366f1
                );

            color: white;

            padding: 20px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,0.12);
        }


        .header-left h1 {

            margin: 0;

            font-size: 25px;
        }


        .header-left p {

            margin: 6px 0 0;

            opacity: 0.9;

            font-size: 14px;
        }


        .home-btn {

            background: white;

            color: #4f46e5;

            text-decoration: none;

            padding: 10px 16px;

            border-radius: 8px;

            font-weight: bold;

            font-size: 14px;
        }


        .home-btn:hover {

            background: #eef2ff;
        }


        /* =================================
           MAIN
        ================================= */

        .container {

            width: 92%;

            max-width: 1250px;

            margin: 30px auto;
        }


        .welcome {

            background: white;

            padding: 20px;

            border-radius: 14px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 18px
                rgba(0,0,0,0.08);
        }


        .welcome h2 {

            margin: 0 0 6px;

            color: #4f46e5;

            font-size: 22px;
        }


        .welcome p {

            margin: 0;

            color: #666;
        }


        /* =================================
           SUMMARY CARDS
        ================================= */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(190px, 1fr)
                );

            gap: 18px;

            margin-bottom: 30px;
        }


        .card {

            background: white;

            padding: 22px;

            border-radius: 15px;

            box-shadow:
                0 5px 18px
                rgba(0,0,0,0.08);

            border-left: 5px solid #4f46e5;
        }


        .card-icon {

            font-size: 30px;

            margin-bottom: 8px;
        }


        .card-title {

            color: #666;

            font-size: 14px;

            margin-bottom: 7px;
        }


        .card-value {

            font-size: 30px;

            font-weight: bold;

            color: #111827;
        }


        /* =================================
           SECTION
        ================================= */

        .section {

            background: white;

            padding: 22px;

            border-radius: 15px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 18px
                rgba(0,0,0,0.08);
        }


        .section h2 {

            margin: 0 0 18px;

            color: #4f46e5;

            font-size: 20px;
        }


        /* =================================
           PROGRESS
        ================================= */

        .progress-wrapper {

            background: #f3f4f6;

            height: 25px;

            border-radius: 20px;

            overflow: hidden;

            margin: 15px 0;
        }


        .progress-bar {

            height: 100%;

            width: <?php echo $completionPercentage; ?>%;

            background:
                linear-gradient(
                    90deg,
                    #22c55e,
                    #16a34a
                );

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 12px;

            font-weight: bold;

            min-width: <?php echo $completionPercentage > 0 ? '35px' : '0'; ?>;
        }


        .status-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(180px, 1fr)
                );

            gap: 15px;
        }


        .status-box {

            padding: 17px;

            border-radius: 10px;

            background: #f8fafc;

            border: 1px solid #e5e7eb;
        }


        .status-box strong {

            display: block;

            font-size: 24px;

            margin-top: 5px;
        }


        /* =================================
           TABLE
        ================================= */

        .table-wrapper {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 650px;
        }


        th {

            background: #4f46e5;

            color: white;

            padding: 12px;

            text-align: left;

            font-size: 14px;
        }


        td {

            padding: 12px;

            border-bottom: 1px solid #e5e7eb;

            font-size: 14px;
        }


        tr:hover td {

            background: #f8fafc;
        }


        /* =================================
           BADGES
        ================================= */

        .badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .submitted {

            background: #fef3c7;

            color: #92400e;
        }


        .assigned {

            background: #dbeafe;

            color: #1d4ed8;
        }


        .progress {

            background: #ede9fe;

            color: #6d28d9;
        }


        .completed {

            background: #dcfce7;

            color: #166534;
        }


        .default {

            background: #e5e7eb;

            color: #374151;
        }


        /* =================================
           TWO COLUMN
        ================================= */

        .two-column {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 25px;
        }


        /* =================================
           EMPTY
        ================================= */

        .empty {

            text-align: center;

            color: #777;

            padding: 20px;
        }


        /* =================================
           FOOTER
        ================================= */

        .footer {

            text-align: center;

            color: #777;

            padding: 20px;

            font-size: 13px;
        }


        /* =================================
           MOBILE
        ================================= */

        @media (max-width: 800px) {

            .header {

                flex-direction: column;

                gap: 15px;

                text-align: center;
            }


            .two-column {

                grid-template-columns: 1fr;
            }


            .container {

                width: 94%;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     HEADER
===================================== -->

<header class="header">


    <div class="header-left">

        <h1>
            📊 SmartCampus360 Reports
        </h1>

        <p>
            Admin Dashboard &nbsp;|&nbsp;
            Campus Service Reports
        </p>

    </div>


    <a
        href="dashboard.html"
        class="home-btn"
    >
        ← Admin Dashboard
    </a>


</header>



<main class="container">


    <!-- =================================
         WELCOME
    ================================= -->

    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($adminName); ?> 👋
        </h2>

        <p>
            View campus request statistics,
            staff performance and service reports.
        </p>

    </div>



    <!-- =================================
         SUMMARY CARDS
    ================================= -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                📋
            </div>

            <div class="card-title">
                Total Requests
            </div>

            <div class="card-value">
                <?php echo $totalRequests; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ⏳
            </div>

            <div class="card-title">
                Pending Requests
            </div>

            <div class="card-value">
                <?php echo $pendingRequests; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🔧
            </div>

            <div class="card-title">
                In Progress
            </div>

            <div class="card-value">
                <?php echo $inProgressRequests; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ✅
            </div>

            <div class="card-title">
                Completed
            </div>

            <div class="card-value">
                <?php echo $completedRequests; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🎓
            </div>

            <div class="card-title">
                Students
            </div>

            <div class="card-value">
                <?php echo $totalStudents; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                👷
            </div>

            <div class="card-title">
                Staff
            </div>

            <div class="card-value">
                <?php echo $totalStaff; ?>
            </div>

        </div>


    </div>



    <!-- =================================
         REQUEST STATUS
    ================================= -->

    <div class="section">

        <h2>
            📈 Request Status Overview
        </h2>


        <div class="status-grid">


            <div class="status-box">

                📝 Submitted

                <strong>
                    <?php echo $submittedRequests; ?>
                </strong>

            </div>


            <div class="status-box">

                👤 Staff Assigned

                <strong>
                    <?php echo $assignedRequests; ?>
                </strong>

            </div>


            <div class="status-box">

                🔧 In Progress

                <strong>
                    <?php echo $inProgressRequests; ?>
                </strong>

            </div>


            <div class="status-box">

                ✅ Completed

                <strong>
                    <?php echo $completedRequests; ?>
                </strong>

            </div>


        </div>


        <p>
            <strong>
                Completion Rate:
                <?php echo $completionPercentage; ?>%
            </strong>
        </p>


        <div class="progress-wrapper">

            <div class="progress-bar">

                <?php echo $completionPercentage; ?>%

            </div>

        </div>

    </div>



    <!-- =================================
         CATEGORY + LOCATION
    ================================= -->

    <div class="two-column">


        <!-- CATEGORY -->

        <div class="section">

            <h2>
                🛠️ Requests by Category
            </h2>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Category
                            </th>

                            <th>
                                Requests
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if ($categoryResult &&
                        $categoryResult->num_rows > 0) {

                        while (
                            $row =
                            $categoryResult->fetch_assoc()
                        ) {

                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["category"]
                                    );
                                    ?>
                                </td>

                                <td>

                                    <strong>
                                        <?php
                                        echo (int)$row["total"];
                                        ?>
                                    </strong>

                                </td>

                            </tr>

                            <?php

                        }

                    } else {

                        ?>

                        <tr>

                            <td
                                colspan="2"
                                class="empty"
                            >
                                No category data available.
                            </td>

                        </tr>

                        <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>

        </div>



        <!-- LOCATION -->

        <div class="section">

            <h2>
                📍 Top Request Locations
            </h2>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Location
                            </th>

                            <th>
                                Requests
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if ($locationResult &&
                        $locationResult->num_rows > 0) {

                        while (
                            $row =
                            $locationResult->fetch_assoc()
                        ) {

                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["location"]
                                    );
                                    ?>
                                </td>

                                <td>

                                    <strong>
                                        <?php
                                        echo (int)$row["total"];
                                        ?>
                                    </strong>

                                </td>

                            </tr>

                            <?php

                        }

                    } else {

                        ?>

                        <tr>

                            <td
                                colspan="2"
                                class="empty"
                            >
                                No location data available.
                            </td>

                        </tr>

                        <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>

        </div>


    </div>



    <!-- =================================
         STAFF PERFORMANCE
    ================================= -->

    <div class="section">

        <h2>
            👷 Staff Performance
        </h2>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            Staff Name
                        </th>

                        <th>
                            Assigned Requests
                        </th>

                        <th>
                            Completed
                        </th>

                        <th>
                            Completion Rate
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($staffResult &&
                    $staffResult->num_rows > 0) {

                    while (
                        $row =
                        $staffResult->fetch_assoc()
                    ) {

                        $staffTotal =
                            (int)$row["total_requests"];

                        $staffCompleted =
                            (int)$row["completed_requests"];

                        $staffRate = 0;

                        if ($staffTotal > 0) {

                            $staffRate = round(
                                (
                                    $staffCompleted /
                                    $staffTotal
                                ) * 100
                            );

                        }

                        ?>

                        <tr>

                            <td>

                                <strong>
                                    <?php

                                    echo htmlspecialchars(
                                        $row["assigned_staff"]
                                    );

                                    ?>
                                </strong>

                            </td>


                            <td>

                                <?php
                                echo $staffTotal;
                                ?>

                            </td>


                            <td>

                                <?php
                                echo $staffCompleted;
                                ?>

                            </td>


                            <td>

                                <?php
                                echo $staffRate;
                                ?>%

                            </td>

                        </tr>

                        <?php

                    }

                } else {

                    ?>

                    <tr>

                        <td
                            colspan="4"
                            class="empty"
                        >
                            No staff performance data available.
                        </td>

                    </tr>

                    <?php

                }

                ?>


                </tbody>

            </table>

        </div>

    </div>



    <!-- =================================
         RECENT REQUESTS
    ================================= -->

    <div class="section">

        <h2>
            📋 Recent Requests
        </h2>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            Track ID
                        </th>

                        <th>
                            Student
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Location
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Assigned Staff
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($recentResult &&
                    $recentResult->num_rows > 0) {

                    while (
                        $row =
                        $recentResult->fetch_assoc()
                    ) {


                        $status =
                            trim($row["status"] ?? "");


                        $statusClass = "default";


                        if (
                            strtolower($status)
                            === "submitted"
                        ) {

                            $statusClass = "submitted";

                        } elseif (
                            strtolower($status)
                            === "staff assigned"
                        ) {

                            $statusClass = "assigned";

                        } elseif (
                            strtolower($status)
                            === "in progress"
                        ) {

                            $statusClass = "progress";

                        } elseif (
                            strtolower($status)
                            === "completed"
                        ) {

                            $statusClass = "completed";

                        }

                        ?>

                        <tr>


                            <td>

                                <strong>
                                    <?php

                                    echo htmlspecialchars(
                                        $row["track_id"]
                                    );

                                    ?>
                                </strong>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["student_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["category"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["location"]
                                );

                                ?>

                            </td>


                            <td>

                                <span
                                    class="badge
                                    <?php
                                    echo $statusClass;
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $status
                                    );

                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["assigned_staff"]
                                    ?: "Not Assigned"
                                );

                                ?>

                            </td>


                        </tr>

                        <?php

                    }

                } else {

                    ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >
                            No requests available.
                        </td>

                    </tr>

                    <?php

                }

                ?>


                </tbody>

            </table>

        </div>

    </div>



    <!-- =================================
         USER SUMMARY
    ================================= -->

    <div class="section">

        <h2>
            👥 User Summary
        </h2>


        <div class="status-grid">


            <div class="status-box">

                🎓 Students

                <strong>
                    <?php echo $totalStudents; ?>
                </strong>

            </div>


            <div class="status-box">

                👷 Staff

                <strong>
                    <?php echo $totalStaff; ?>
                </strong>

            </div>


            <div class="status-box">

                🛡️ Admins

                <strong>
                    <?php echo $totalAdmins; ?>
                </strong>

            </div>


        </div>

    </div>


</main>



<footer class="footer">

    SmartCampus360 © <?php echo date("Y"); ?>

</footer>


</body>

</html>
```