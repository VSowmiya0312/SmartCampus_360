```php
<?php
session_start();

require_once "../config/database.php";

/* ---------------------------------
   LOGIN CHECK
--------------------------------- */
if (!isset($_SESSION["user_id"])) {
    die("Access denied. Please login first.");
}

/* ---------------------------------
   ROLE CHECK
--------------------------------- */
$user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($user_role !== "staff") {
    die("Access denied. Staff members only.");
}

/* ---------------------------------
   STAFF NAME
--------------------------------- */
$staff_name = $_SESSION["name"] ?? "";

/* ---------------------------------
   GET COMPLETED REQUESTS
--------------------------------- */
$sql = "
    SELECT *
    FROM requests
    WHERE assigned_staff = ?
    AND status = 'Completed'
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $staff_name);
$stmt->execute();

$result = $stmt->get_result();

$total_completed = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Work History - SmartCampus360</title>

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

            padding: 35px 20px;
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
            background: rgba(255, 255, 255, 0.95);

            border-radius: 18px;

            padding: 25px 30px;

            margin-bottom: 25px;

            text-align: center;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
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
           TOP SECTION
        --------------------------------- */

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;

            margin-bottom: 25px;
        }

        .count-box {
            background: rgba(255, 255, 255, 0.95);

            padding: 18px 25px;

            border-radius: 15px;

            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.20);

            color: #173b8f;

            font-size: 18px;

            font-weight: bold;
        }

        .back-btn {
            display: inline-block;

            background: #173b8f;

            color: white;

            text-decoration: none;

            padding: 12px 22px;

            border-radius: 10px;

            font-size: 15px;

            font-weight: bold;

            transition: 0.3s;
        }

        .back-btn:hover {
            background: #0d2868;

            transform: translateY(-2px);
        }

        /* ---------------------------------
           REQUEST CARD
        --------------------------------- */

        .request-card {
            background: rgba(255, 255, 255, 0.96);

            border-radius: 18px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.20);

            border-left: 6px solid #28a745;
        }

        .request-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 20px;

            flex-wrap: wrap;
        }

        .track-id {
            color: #173b8f;

            font-size: 21px;

            font-weight: bold;
        }

        .completed {
            background: #28a745;

            color: white;

            padding: 8px 15px;

            border-radius: 20px;

            font-size: 14px;

            font-weight: bold;
        }

        /* ---------------------------------
           DETAILS
        --------------------------------- */

        .details {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 18px;
        }

        .detail-box {
            background: #f4f7fc;

            padding: 13px 15px;

            border-radius: 10px;

            border: 1px solid #e0e5ef;
        }

        .detail-box strong {
            display: block;

            color: #173b8f;

            font-size: 13px;

            margin-bottom: 5px;
        }

        .detail-box span {
            color: #333;

            font-size: 15px;

            word-break: break-word;
        }

        .description {
            background: #f8f9fa;

            padding: 15px;

            border-radius: 10px;

            border: 1px solid #e2e2e2;

            margin-top: 5px;
        }

        .description strong {
            display: block;

            color: #173b8f;

            margin-bottom: 7px;
        }

        .description p {
            color: #444;

            line-height: 1.6;

            font-size: 15px;
        }

        /* ---------------------------------
           EMPTY MESSAGE
        --------------------------------- */

        .empty-box {
            background: rgba(255, 255, 255, 0.96);

            border-radius: 18px;

            padding: 55px 25px;

            text-align: center;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.20);
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

            font-size: 16px;

            line-height: 1.6;
        }

        /* ---------------------------------
           FOOTER
        --------------------------------- */

        .footer {
            text-align: center;

            color: white;

            margin-top: 30px;

            font-size: 14px;

            text-shadow: 1px 1px 3px #000;
        }

        /* ---------------------------------
           MOBILE
        --------------------------------- */

        @media (max-width: 700px) {

            body {
                padding: 20px 10px;
            }

            .container {
                width: 100%;
            }

            .header h1 {
                font-size: 25px;
            }

            .top-section {
                flex-direction: column;

                align-items: stretch;
            }

            .count-box {
                text-align: center;
            }

            .back-btn {
                text-align: center;
            }

            .details {
                grid-template-columns: 1fr;
            }

            .request-card {
                padding: 18px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">

        <h1>📋 Work History</h1>

        <p>
            View all completed campus requests handled by you.
        </p>

    </div>


    <!-- TOP SECTION -->
    <div class="top-section">

        <div class="count-box">
            ✅ Completed Requests: <?php echo $total_completed; ?>
        </div>

        <a href="dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>

    </div>


    <?php if ($total_completed > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <div class="request-card">

                <!-- REQUEST HEADER -->
                <div class="request-header">

                    <div class="track-id">

                        🎫 Track ID:
                        <?php
                        echo htmlspecialchars($row["track_id"] ?? "N/A");
                        ?>

                    </div>

                    <div class="completed">
                        ✅ Completed
                    </div>

                </div>


                <!-- REQUEST DETAILS -->
                <div class="details">

                    <div class="detail-box">

                        <strong>👤 Student Name</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["student_name"] ?? "N/A");
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>📧 Student Email</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["student_email"] ?? "N/A");
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>📂 Category</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["category"] ?? "N/A");
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>📍 Location</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["location"] ?? "N/A");
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>👨‍🔧 Assigned Staff</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["assigned_staff"] ?? "N/A");
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>📅 Request Date</strong>

                        <span>
                            <?php
                            echo htmlspecialchars($row["created_at"] ?? "N/A");
                            ?>
                        </span>

                    </div>

                </div>


                <!-- DESCRIPTION -->
                <div class="description">

                    <strong>📝 Request Description</strong>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $row["description"] ?? "No description available."
                            )
                        );
                        ?>
                    </p>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <!-- NO HISTORY -->
        <div class="empty-box">

            <div class="empty-icon">
                📋
            </div>

            <h2>
                No Completed Requests
            </h2>

            <p>
                You have not completed any campus requests yet.
                <br>
                Completed requests will appear here automatically.
            </p>

        </div>

    <?php endif; ?>


    <div class="footer">

        SmartCampus360 © 2026
        <br>
        Staff Work History

    </div>

</div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
```