```php
<?php

require_once "../config/database.php";

/*
    Admin Manage Staff Page

    This page displays staff members from the
    SmartCampus360 MySQL database.

    It does not depend on a PHP admin session because
    the current Admin Dashboard is dashboard.html.
*/

/* ---------------------------------
   GET STAFF DETAILS
--------------------------------- */

$sql = "SELECT * FROM staff ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

$total_staff = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Staff - SmartCampus360</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family: Arial, Helvetica, sans-serif;

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

            padding: 30px 20px;
        }


        .container {

            width: 95%;

            max-width: 1150px;

            margin: auto;
        }


        /* ---------------------------------
           HEADER
        --------------------------------- */

        .header {

            background: rgba(255, 255, 255, 0.96);

            border-radius: 20px;

            padding: 25px;

            text-align: center;

            margin-bottom: 25px;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.25);
        }


        .header h1 {

            color: #173b8f;

            font-size: 32px;

            margin-bottom: 8px;
        }


        .header p {

            color: #555;

            font-size: 16px;
        }


        /* ---------------------------------
           TOP BAR
        --------------------------------- */

        .top-bar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 25px;

            flex-wrap: wrap;
        }


        .count-box {

            background: rgba(255, 255, 255, 0.96);

            color: #173b8f;

            padding: 15px 22px;

            border-radius: 12px;

            font-weight: bold;

            box-shadow:
                0 6px 18px rgba(0, 0, 0, 0.20);
        }


        .back-btn {

            display: inline-block;

            background: #173b8f;

            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 10px;

            font-weight: bold;

            transition: 0.3s;
        }


        .back-btn:hover {

            background: #0d2868;

            transform: translateY(-2px);
        }


        /* ---------------------------------
           STAFF GRID
        --------------------------------- */

        .staff-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        /* ---------------------------------
           STAFF CARD
        --------------------------------- */

        .staff-card {

            background: rgba(255, 255, 255, 0.96);

            border-radius: 18px;

            padding: 25px;

            text-align: center;

            box-shadow:
                0 7px 22px rgba(0, 0, 0, 0.22);

            transition: 0.3s;

            border-top: 5px solid #173b8f;
        }


        .staff-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.28);
        }


        .staff-icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 15px;

            border-radius: 50%;

            background: #e8eefc;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 36px;
        }


        .staff-name {

            color: #173b8f;

            font-size: 21px;

            font-weight: bold;

            margin-bottom: 10px;
        }


        .staff-info {

            color: #555;

            font-size: 14px;

            line-height: 1.8;

            margin-bottom: 15px;
        }


        .staff-info strong {

            color: #333;
        }


        /* ---------------------------------
           STATUS
        --------------------------------- */

        .status {

            display: inline-block;

            padding: 7px 15px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;
        }


        .available {

            background: #d4edda;

            color: #155724;
        }


        .unavailable {

            background: #f8d7da;

            color: #721c24;
        }


        /* ---------------------------------
           EMPTY
        --------------------------------- */

        .empty-box {

            background: rgba(255, 255, 255, 0.96);

            border-radius: 18px;

            padding: 55px 25px;

            text-align: center;

            box-shadow:
                0 7px 22px rgba(0, 0, 0, 0.22);
        }


        .empty-icon {

            font-size: 55px;

            margin-bottom: 15px;
        }


        .empty-box h2 {

            color: #173b8f;

            margin-bottom: 10px;
        }


        .empty-box p {

            color: #666;

            line-height: 1.6;
        }


        /* ---------------------------------
           FOOTER
        --------------------------------- */

        .footer {

            color: white;

            text-align: center;

            margin-top: 30px;

            font-size: 14px;

            text-shadow:
                1px 2px 4px black;
        }


        /* ---------------------------------
           MOBILE
        --------------------------------- */

        @media (max-width: 900px) {

            .staff-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 600px) {

            body {

                padding: 20px 10px;
            }


            .container {

                width: 100%;
            }


            .header h1 {

                font-size: 26px;
            }


            .top-bar {

                flex-direction: column;

                align-items: stretch;
            }


            .count-box {

                text-align: center;
            }


            .back-btn {

                text-align: center;
            }


            .staff-grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- HEADER -->

    <div class="header">

        <h1>
            👨‍🔧 Manage Staff
        </h1>

        <p>
            SmartCampus360 Staff Management
        </p>

    </div>


    <!-- TOP BAR -->

    <div class="top-bar">


        <div class="count-box">

            👨‍🔧 Total Staff:
            <?php echo $total_staff; ?>

        </div>


        <a
            href="dashboard.html"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>


    </div>


    <?php if ($total_staff > 0): ?>


        <div class="staff-grid">


            <?php while ($staff = $result->fetch_assoc()): ?>


                <div class="staff-card">


                    <!-- STAFF ICON -->

                    <div class="staff-icon">
                        👨‍🔧
                    </div>


                    <!-- STAFF NAME -->

                    <div class="staff-name">

                        <?php

                        echo htmlspecialchars(
                            $staff["name"] ?? "Staff Member"
                        );

                        ?>

                    </div>


                    <!-- STAFF INFORMATION -->

                    <div class="staff-info">


                        <div>

                            <strong>
                                📧 Email
                            </strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $staff["email"] ?? "Not available"
                            );

                            ?>

                        </div>


                        <div>

                            <strong>
                                🛠 Department
                            </strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $staff["department"] ?? "Not available"
                            );

                            ?>

                        </div>


                    </div>


                    <!-- STATUS -->

                    <?php

                    $status =
                        strtolower(
                            trim(
                                $staff["status"] ?? ""
                            )
                        );

                    ?>


                    <?php if (
                        $status === "available" ||
                        $status === "active"
                    ): ?>


                        <span class="status available">

                            🟢 Available

                        </span>


                    <?php else: ?>


                        <span class="status unavailable">

                            🔴

                            <?php

                            echo htmlspecialchars(
                                $staff["status"] ?? "Unavailable"
                            );

                            ?>

                        </span>


                    <?php endif; ?>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty-box">


            <div class="empty-icon">
                👨‍🔧
            </div>


            <h2>
                No Staff Found
            </h2>


            <p>

                No staff members are currently available
                in the SmartCampus360 database.

            </p>


        </div>


    <?php endif; ?>


    <!-- FOOTER -->

    <div class="footer">

        SmartCampus360 © 2026

        <br>

        Admin Staff Management

    </div>


</div>


</body>

</html>


<?php

$conn->close();

?>
```