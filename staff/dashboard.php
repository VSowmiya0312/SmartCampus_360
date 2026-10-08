```php
<?php

require_once "../config/database.php";

/* =========================
   STAFF DETAILS
========================= */

$staff_name = "Arun Kumar";
$staff_email = "arunkumar@gmail.com";


/* =========================
   DASHBOARD STATISTICS
========================= */

$assigned_count = 0;
$progress_count = 0;
$completed_count = 0;


/* Assigned Requests */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM requests
     WHERE assigned_staff = ?
     AND status = 'Staff Assigned'"
);

$stmt->bind_param(
    "s",
    $staff_name
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $assigned_count = $row["total"];

}

$stmt->close();


/* Work In Progress */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM requests
     WHERE assigned_staff = ?
     AND status = 'Work In Progress'"
);

$stmt->bind_param(
    "s",
    $staff_name
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $progress_count = $row["total"];

}

$stmt->close();


/* Completed */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM requests
     WHERE assigned_staff = ?
     AND status = 'Completed'"
);

$stmt->bind_param(
    "s",
    $staff_name
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $completed_count = $row["total"];

}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Dashboard - SmartCampus360</title>


    <style>

        /* =========================
           RESET
        ========================= */

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

        }


        /* =========================
           BODY
        ========================= */

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

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


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            width: 100%;

            background: white;

            padding:
                15px 5%;

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            box-shadow:
                0 3px 15px
                rgba(0, 0, 0, 0.15);

        }


        .logo {

            font-size: 22px;

            font-weight: bold;

            color: #172554;

        }


        .home-link {

            text-decoration: none;

            background: #4f46e5;

            color: white;

            padding:
                9px 18px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;

        }


        .home-link:hover {

            background: #3730a3;

        }


        /* =========================
           MAIN CONTAINER
        ========================= */

        .container {

            width: 90%;

            max-width: 1100px;

            margin:
                45px auto;

        }


        /* =========================
           WELCOME BOX
        ========================= */

        .welcome-box {

            background:
                rgba(255, 255, 255, 0.95);

            border-radius: 18px;

            padding:
                30px;

            margin-bottom: 25px;

            box-shadow:
                0 12px 30px
                rgba(0, 0, 0, 0.20);

        }


        .welcome-box h1 {

            color: #172554;

            font-size: 30px;

            margin-bottom: 8px;

        }


        .welcome-box p {

            color: #64748b;

            font-size: 15px;

            line-height: 1.6;

        }


        .staff-email {

            margin-top: 8px;

            color: #4f46e5 !important;

            font-weight: bold;

        }


        /* =========================
           STATISTICS
        ========================= */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .stat-card {

            background:
                rgba(255, 255, 255, 0.95);

            border-radius: 16px;

            padding:
                25px;

            text-align: center;

            box-shadow:
                0 10px 25px
                rgba(0, 0, 0, 0.18);

            transition: 0.3s;

        }


        .stat-card:hover {

            transform:
                translateY(-5px);

        }


        .stat-icon {

            font-size: 35px;

            margin-bottom: 10px;

        }


        .stat-number {

            font-size: 32px;

            font-weight: bold;

            color: #4f46e5;

            margin-bottom: 5px;

        }


        .stat-title {

            color: #64748b;

            font-size: 14px;

            font-weight: bold;

        }


        /* =========================
           FEATURE SECTION
        ========================= */

        .section-title {

            background:
                rgba(255, 255, 255, 0.95);

            border-radius: 16px;

            padding:
                20px 25px;

            margin-bottom: 20px;

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, 0.15);

        }


        .section-title h2 {

            color: #172554;

            font-size: 23px;

        }


        .section-title p {

            color: #64748b;

            margin-top: 6px;

            font-size: 14px;

        }


        /* =========================
           FEATURE CARDS
        ========================= */

        .feature-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

        }


        .feature-card {

            background:
                rgba(255, 255, 255, 0.96);

            border-radius: 16px;

            padding:
                28px 22px;

            text-align: center;

            box-shadow:
                0 10px 25px
                rgba(0, 0, 0, 0.18);

            transition: 0.3s;

        }


        .feature-card:hover {

            transform:
                translateY(-6px);

            box-shadow:
                0 15px 30px
                rgba(0, 0, 0, 0.25);

        }


        .feature-icon {

            font-size: 42px;

            margin-bottom: 12px;

        }


        .feature-card h3 {

            color: #172554;

            font-size: 18px;

            margin-bottom: 8px;

        }


        .feature-card p {

            color: #64748b;

            font-size: 13px;

            line-height: 1.6;

            margin-bottom: 18px;

        }


        .feature-button {

            display: inline-block;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            color: white;

            padding:
                10px 20px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: bold;

            transition: 0.3s;

        }


        .feature-button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 6px 15px
                rgba(79, 70, 229, 0.30);

        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            margin-top: 45px;

            background:
                rgba(15, 23, 42, 0.95);

            color: white;

            text-align: center;

            padding:
                25px 20px;

        }


        footer p {

            color: #cbd5e1;

            font-size: 13px;

            margin-top: 5px;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .stats-grid {

                grid-template-columns:
                    1fr;

            }


            .feature-grid {

                grid-template-columns:
                    1fr;

            }


            .welcome-box h1 {

                font-size: 25px;

            }

        }


        @media (max-width: 600px) {

            .navbar {

                flex-direction:
                    column;

                gap: 12px;

                text-align: center;

            }


            .container {

                width: 94%;

                margin-top: 25px;

            }


            .welcome-box {

                padding: 22px;

            }

        }

    </style>

</head>


<body>


    <!-- =========================
         NAVBAR
    ========================= -->

    <header class="navbar">


        <div class="logo">

            🏫 SmartCampus360
            - Staff

        </div>


        <a
            href="../index.html"
            class="home-link"
        >

            🏠 Home

        </a>


    </header>



    <!-- =========================
         MAIN
    ========================= -->

    <main class="container">


        <!-- WELCOME -->

        <section class="welcome-box">


            <h1>

                👋 Welcome,
                <?php echo htmlspecialchars($staff_name); ?>

            </h1>


            <p>

                Manage your assigned campus requests
                and update their progress from this dashboard.

            </p>


            <p class="staff-email">

                📧
                <?php echo htmlspecialchars($staff_email); ?>

            </p>


        </section>



        <!-- =========================
             STATISTICS
        ========================= -->

        <section class="stats-grid">


            <!-- ASSIGNED -->

            <div class="stat-card">


                <div class="stat-icon">
                    📋
                </div>


                <div class="stat-number">

                    <?php
                    echo $assigned_count;
                    ?>

                </div>


                <div class="stat-title">

                    Assigned Requests

                </div>


            </div>



            <!-- IN PROGRESS -->

            <div class="stat-card">


                <div class="stat-icon">
                    🔧
                </div>


                <div class="stat-number">

                    <?php
                    echo $progress_count;
                    ?>

                </div>


                <div class="stat-title">

                    Work In Progress

                </div>


            </div>



            <!-- COMPLETED -->

            <div class="stat-card">


                <div class="stat-icon">
                    ✅
                </div>


                <div class="stat-number">

                    <?php
                    echo $completed_count;
                    ?>

                </div>


                <div class="stat-title">

                    Completed Requests

                </div>


            </div>


        </section>



        <!-- =========================
             SECTION TITLE
        ========================= -->

        <section class="section-title">


            <h2>

                🛠️ Staff Operations

            </h2>


            <p>

                Select an option to manage your assigned
                campus maintenance requests.

            </p>


        </section>



        <!-- =========================
             FEATURE CARDS
        ========================= -->

        <section class="feature-grid">


            <!-- ASSIGNED REQUESTS -->

            <div class="feature-card">


                <div class="feature-icon">
                    📋
                </div>


                <h3>

                    Assigned Requests

                </h3>


                <p>

                    View the campus issues assigned
                    to you by the administrator.

                </p>


                <a
                    href="assigned-requests.php"
                    class="feature-button"
                >

                    View Requests

                </a>


            </div>



            <!-- UPDATE REQUEST -->

            <div class="feature-card">


                <div class="feature-icon">
                    🔧
                </div>


                <h3>

                    Update Request

                </h3>


                <p>

                    Update the progress of your assigned
                    requests and mark completed work.

                </p>


                <a
                    href="update-request.php"
                    class="feature-button"
                >

                    Update Request

                </a>


            </div>



            <!-- WORK HISTORY -->

            <div class="feature-card">


                <div class="feature-icon">
                    📊
                </div>


                <h3>

                    Work History

                </h3>


                <p>

                    View the history of requests that
                    you have completed.

                </p>


                <a
                    href="work-history.php"
                    class="feature-button"
                >

                    View History

                </a>


            </div>


        </section>


    </main>



    <!-- =========================
         FOOTER
    ========================= -->

    <footer>


        <strong>

            🏫 SmartCampus360

        </strong>


        <p>

            Staff Management Dashboard

        </p>


        <p>

            © 2026 SmartCampus360. All Rights Reserved.

        </p>


    </footer>


</body>

</html>
```