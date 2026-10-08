<?php

session_start();


/* --------------------------------
   CHECK LOGIN
-------------------------------- */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.html");

    exit;
}


/* --------------------------------
   CHECK STUDENT ROLE
-------------------------------- */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
) {

    header("Location: ../login.html");

    exit;
}


/* --------------------------------
   GET STUDENT DETAILS
-------------------------------- */

$studentEmail =
    $_SESSION["email"] ?? "";

$studentName =
    $_SESSION["name"] ?? "Student";


/* --------------------------------
   DATABASE
-------------------------------- */

require_once "../config/database.php";


/* --------------------------------
   GET NOTIFICATIONS
-------------------------------- */

$notifications = [];


$stmt = $conn->prepare(
    "SELECT *
     FROM notifications
     WHERE user_email = ?
     ORDER BY created_at DESC"
);


if ($stmt) {

    $stmt->bind_param(
        "s",
        $studentEmail
    );

    $stmt->execute();

    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {

        $notifications[] = $row;
    }


    $stmt->close();
}

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
        Notifications - SmartCampus360
    </title>


    <style>

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;
        }


        body {

            font-family: Arial, sans-serif;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #e0f2fe,
                    #f0fdf4,
                    #fef3c7
                );

            padding: 40px 20px;
        }


        .container {

            max-width: 900px;

            margin: auto;
        }


        .header {

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #7c3aed
                );

            color: white;

            padding: 30px;

            border-radius: 20px;

            text-align: center;

            margin-bottom: 25px;

            box-shadow:
                0 10px 25px
                rgba(0,0,0,0.15);
        }


        .header h1 {

            font-size: 32px;

            margin-bottom: 10px;
        }


        .header p {

            font-size: 16px;

            opacity: 0.95;
        }


        .email-box {

            background: white;

            padding: 15px 20px;

            border-radius: 12px;

            margin-bottom: 20px;

            text-align: center;

            color: #374151;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.08);
        }


        .notification {

            background: white;

            padding: 20px;

            border-radius: 16px;

            margin-bottom: 15px;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.08);

            border-left: 6px solid #2563eb;

            transition: 0.2s;
        }


        .notification:hover {

            transform: translateY(-2px);
        }


        .notification.new {

            border-left-color: #16a34a;

            background: #f0fdf4;
        }


        .notification-message {

            font-size: 17px;

            color: #1f2937;

            line-height: 1.5;

            margin-bottom: 8px;
        }


        .notification-date {

            font-size: 13px;

            color: #6b7280;
        }


        .new-badge {

            display: inline-block;

            background: #16a34a;

            color: white;

            padding: 4px 9px;

            border-radius: 20px;

            font-size: 11px;

            margin-bottom: 8px;
        }


        .empty {

            background: white;

            padding: 50px 20px;

            border-radius: 20px;

            text-align: center;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.08);
        }


        .empty-icon {

            font-size: 60px;

            margin-bottom: 15px;
        }


        .empty h2 {

            color: #374151;

            margin-bottom: 10px;
        }


        .empty p {

            color: #6b7280;
        }


        .buttons {

            text-align: center;

            margin-top: 25px;
        }


        .btn {

            display: inline-block;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 10px;

            margin: 5px;

            color: white;

            font-weight: bold;
        }


        .dashboard-btn {

            background: #2563eb;
        }


        .track-btn {

            background: #7c3aed;
        }


        .announcement-btn {

            background: #16a34a;
        }


        .btn:hover {

            opacity: 0.9;
        }

    </style>

</head>


<body>


<div class="container">


    <div class="header">

        <h1>
            🔔 Notifications
        </h1>

        <p>
            Stay updated about your campus requests
        </p>

    </div>


    <div class="email-box">

        👤 Student:

        <strong>

            <?php

            echo htmlspecialchars(
                $studentName
            );

            ?>

        </strong>

        &nbsp; | &nbsp;

        📧

        <strong>

            <?php

            echo htmlspecialchars(
                $studentEmail
            );

            ?>

        </strong>

    </div>


    <?php if (count($notifications) === 0): ?>


        <div class="empty">

            <div class="empty-icon">

                🔔

            </div>


            <h2>

                No Notifications

            </h2>


            <p>

                You don't have any notifications yet.

            </p>

        </div>


    <?php else: ?>


        <?php foreach (
            $notifications
            as $notification
        ): ?>


            <div
                class="notification
                <?php

                if (
                    isset($notification["is_read"]) &&
                    $notification["is_read"] == 0
                ) {

                    echo "new";
                }

                ?>"
            >


                <?php if (
                    isset($notification["is_read"]) &&
                    $notification["is_read"] == 0
                ): ?>

                    <span class="new-badge">

                        NEW

                    </span>

                <?php endif; ?>


                <div class="notification-message">

                    <?php

                    echo htmlspecialchars(
                        $notification["message"]
                    );

                    ?>

                </div>


                <div class="notification-date">

                    🕒

                    <?php

                    echo htmlspecialchars(
                        $notification["created_at"]
                    );

                    ?>

                </div>


            </div>


        <?php endforeach; ?>


    <?php endif; ?>


    <div class="buttons">


        <a
            href="dashboard.php"
            class="btn dashboard-btn"
        >

            ← Back to Dashboard

        </a>


        <a
            href="../announcements.php"
            class="btn announcement-btn"
        >

            📢 Announcements

        </a>


        <a
            href="../track_request.php"
            class="btn track-btn"
        >

            🔍 Track Request

        </a>


    </div>


</div>


</body>

</html>


<?php

$conn->close();

?>