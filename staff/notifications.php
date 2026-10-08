```php
<?php

include '../config/database.php';

/*
|--------------------------------------------------------------------------
| Current Staff
|--------------------------------------------------------------------------
|
| Your current staff dashboard is logged in as:
| Arun Kumar
| arunkumar@gmail.com
|
*/

$staff_name = "Arun Kumar";
$staff_email = "arunkumar@gmail.com";


/*
|--------------------------------------------------------------------------
| Get notifications for this staff member
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            track_id,
            message,
            is_read,
            created_at
        FROM notifications
        WHERE staff_email = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Notification query error: " . $conn->error);
}

$stmt->bind_param("s", $staff_email);

if (!$stmt->execute()) {
    die("Notification execution error: " . $stmt->error);
}

$result = $stmt->get_result();

$notification_count = $result->num_rows;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Staff Notifications - SmartCampus360</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 40px 15px;

            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #667eea,
                    #764ba2
                );
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: auto;
        }

        .notification-box {
            background: white;
            border-radius: 20px;
            padding: 30px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.20);
        }

        h1 {
            text-align: center;
            color: #333;
            margin: 0;
            font-size: 30px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-top: 8px;
            margin-bottom: 25px;
        }

        .staff-info {
            background: #f1f4ff;

            border-left:
                5px solid #667eea;

            padding: 16px;

            border-radius: 10px;

            margin-bottom: 25px;

            color: #333;
        }

        .count {
            background: #e8f8ed;

            border: 1px solid #b8e5c4;

            color: #218838;

            padding: 13px;

            border-radius: 10px;

            text-align: center;

            font-weight: bold;

            margin-bottom: 20px;
        }

        .notification {
            background: #f8f9ff;

            border: 1px solid #e1e4ff;

            border-left:
                5px solid #667eea;

            border-radius: 12px;

            padding: 18px;

            margin-bottom: 15px;
        }

        .notification-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;

            margin-bottom: 10px;
        }

        .notification-icon {
            font-size: 25px;
        }

        .track {
            font-weight: bold;
            color: #667eea;
        }

        .message {
            color: #333;

            font-size: 16px;

            line-height: 1.6;
        }

        .date {
            color: #888;

            font-size: 13px;

            margin-top: 10px;
        }

        .status {
            display: inline-block;

            margin-top: 10px;

            padding: 5px 10px;

            border-radius: 15px;

            font-size: 12px;

            font-weight: bold;
        }

        .unread {
            background: #fff3cd;
            color: #856404;
        }

        .read {
            background: #e2e3e5;
            color: #555;
        }

        .no-notifications {
            text-align: center;

            padding: 50px 20px;
        }

        .no-icon {
            font-size: 60px;

            margin-bottom: 15px;
        }

        .no-notifications h2 {
            color: #444;

            margin-bottom: 8px;
        }

        .no-notifications p {
            color: #777;
        }

        .buttons {
            display: flex;

            justify-content: center;

            gap: 15px;

            flex-wrap: wrap;

            margin-top: 30px;
        }

        .button {
            display: inline-block;

            padding: 12px 22px;

            border-radius: 8px;

            color: white;

            text-decoration: none;

            font-weight: bold;
        }

        .dashboard {
            background: #667eea;
        }

        .requests {
            background: #28a745;
        }

        .button:hover {
            opacity: 0.85;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="notification-box">

        <h1>
            🔔 Staff Notifications
        </h1>

        <div class="subtitle">
            View and manage your request notifications
        </div>


        <!-- Staff Information -->

        <div class="staff-info">

            👨‍🔧 Staff:

            <strong>
                <?php
                echo htmlspecialchars($staff_name);
                ?>
            </strong>

            &nbsp; | &nbsp;

            📧

            <?php
            echo htmlspecialchars($staff_email);
            ?>

        </div>


        <?php if ($notification_count > 0): ?>

            <!-- Notification Count -->

            <div class="count">

                🔔 You have

                <?php
                echo $notification_count;
                ?>

                notification<?php
                echo ($notification_count != 1)
                    ? 's'
                    : '';
                ?>.

            </div>


            <!-- Notifications -->

            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="notification">

                    <div class="notification-header">

                        <span class="notification-icon">
                            🔔
                        </span>


                        <?php if (!empty($row['track_id'])): ?>

                            <span class="track">

                                Track ID:

                                <?php
                                echo htmlspecialchars(
                                    $row['track_id']
                                );
                                ?>

                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="message">

                        <?php
                        echo htmlspecialchars(
                            $row['message']
                        );
                        ?>

                    </div>


                    <?php if ((int)$row['is_read'] === 0): ?>

                        <span class="status unread">
                            NEW
                        </span>

                    <?php else: ?>

                        <span class="status read">
                            READ
                        </span>

                    <?php endif; ?>


                    <div class="date">

                        📅

                        <?php
                        echo htmlspecialchars(
                            $row['created_at']
                        );
                        ?>

                    </div>

                </div>

            <?php endwhile; ?>


        <?php else: ?>

            <!-- No Notifications -->

            <div class="no-notifications">

                <div class="no-icon">
                    🔔
                </div>

                <h2>
                    No Notifications Yet
                </h2>

                <p>
                    New request notifications will
                    appear here when a request is
                    assigned to you.
                </p>

            </div>

        <?php endif; ?>


        <!-- Buttons -->

        <div class="buttons">

            <a
                href="dashboard.php"
                class="button dashboard">

                ← Back to Dashboard

            </a>


            <a
                href="assigned-requests.php"
                class="button requests">

                📋 Assigned Requests

            </a>

        </div>

    </div>

</div>

</body>

</html>

<?php

$stmt->close();

$conn->close();

?>
```