```php
<?php

session_start();

require_once "../config/database.php";

/* -----------------------------------------
   CHECK ADMIN LOGIN
----------------------------------------- */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

$adminName = $_SESSION["name"] ?? "Admin";

$message = "";
$messageType = "";


/* -----------------------------------------
   DELETE ANNOUNCEMENT
----------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {

    $deleteId = (int)$_POST["delete_id"];

    if ($deleteId > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM announcements WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $deleteId);

            if ($stmt->execute()) {
                $message = "Announcement deleted successfully.";
                $messageType = "success";
            } else {
                $message = "Unable to delete announcement.";
                $messageType = "error";
            }

            $stmt->close();

        } else {

            $message = "Database error.";
            $messageType = "error";
        }
    }
}


/* -----------------------------------------
   ADD ANNOUNCEMENT
----------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_announcement"])) {

    $title = trim($_POST["title"] ?? "");
    $announcementMessage = trim($_POST["message"] ?? "");

    if ($title === "" || $announcementMessage === "") {

        $message = "Please enter both title and announcement message.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO announcements (title, message)
             VALUES (?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $title,
                $announcementMessage
            );

            if ($stmt->execute()) {

                $message = "Announcement added successfully.";
                $messageType = "success";

            } else {

                $message = "Unable to add announcement.";
                $messageType = "error";
            }

            $stmt->close();

        } else {

            $message = "Database error while adding announcement.";
            $messageType = "error";
        }
    }
}


/* -----------------------------------------
   GET ANNOUNCEMENTS
----------------------------------------- */

$announcements = [];

$sql = "
    SELECT
        id,
        title,
        message,
        created_at
    FROM announcements
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}


/* -----------------------------------------
   TOTAL ANNOUNCEMENTS
----------------------------------------- */

$totalAnnouncements = count($announcements);

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
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;

            /* SAME COLLEGE BACKGROUND */

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

        /* HEADER */

        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .header-left h1 {
            font-size: 28px;
        }

        .header-left p {
            margin-top: 6px;
            opacity: 0.9;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .header-btn {
            background: white;
            color: #4f46e5;
            padding: 11px 18px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
        }

        .header-btn:hover {
            background: #f3f4f6;
        }

        /* MAIN */

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 35px auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h2 {
            color: #ffffff;
            font-size: 30px;
        }

        .page-title p {
            color: #f1f5f9;
            margin-top: 8px;
        }

        /* MESSAGE */

        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* SUMMARY */

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .summary-card .icon {
            font-size: 35px;
        }

        .summary-card h3 {
            margin-top: 10px;
            color: #555;
        }

        .summary-card p {
            margin-top: 8px;
            font-size: 30px;
            font-weight: bold;
            color: #4f46e5;
        }

        /* ADD ANNOUNCEMENT */

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            margin-bottom: 35px;
        }

        .form-card h2 {
            color: #312e81;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .add-btn {
            border: none;
            padding: 13px 24px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-radius: 25px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .add-btn:hover {
            opacity: 0.9;
        }

        /* ANNOUNCEMENTS */

        .section-title {
            color: #ffffff;
            font-size: 25px;
            margin-bottom: 20px;
        }

        .announcement-list {
            display: grid;
            gap: 20px;
        }

        .announcement-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border-left: 6px solid #4f46e5;
        }

        .announcement-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .announcement-title {
            color: #312e81;
            font-size: 21px;
            font-weight: bold;
        }

        .announcement-date {
            color: #777;
            font-size: 13px;
            white-space: nowrap;
        }

        .announcement-message {
            margin-top: 15px;
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }

        .delete-form {
            margin-top: 20px;
        }

        .delete-btn {
            border: none;
            padding: 9px 18px;
            background: #ef4444;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #dc2626;
        }

        /* EMPTY */

        .empty {
            background: white;
            padding: 45px;
            text-align: center;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .empty .icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: #444;
            margin-bottom: 8px;
        }

        .empty p {
            color: #777;
        }

        /* FOOTER */

        footer {
            margin-top: 50px;
            padding: 20px;
            text-align: center;
            background: #312e81;
            color: white;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .summary {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .announcement-top {
                flex-direction: column;
            }

        }

        @media (max-width: 500px) {

            .container {
                width: 94%;
            }

            .form-card {
                padding: 20px;
            }

            .announcement-card {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }

        }

    </style>

</head>

<body>


    <!-- HEADER -->

    <div class="header">

        <div class="header-left">

            <h1>🏫 SmartCampus360</h1>

            <p>Admin Management Panel</p>

        </div>

        <div class="header-buttons">

            <a href="dashboard.html" class="header-btn">
                🏠 Dashboard
            </a>

            <a href="../logout.php" class="header-btn">
                🚪 Logout
            </a>

        </div>

    </div>


    <!-- MAIN -->

    <div class="container">


        <!-- PAGE TITLE -->

        <div class="page-title">

            <h2>📢 Announcement Management</h2>

            <p>
                Create and manage important campus announcements.
            </p>

        </div>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div class="message <?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- SUMMARY -->

        <div class="summary">

            <div class="summary-card">

                <div class="icon">📢</div>

                <h3>Total Announcements</h3>

                <p>
                    <?php echo $totalAnnouncements; ?>
                </p>

            </div>


            <div class="summary-card">

                <div class="icon">📅</div>

                <h3>Latest Announcement</h3>

                <p style="font-size:18px;">

                    <?php

                    if ($totalAnnouncements > 0) {

                        echo htmlspecialchars(
                            $announcements[0]["created_at"]
                        );

                    } else {

                        echo "None";

                    }

                    ?>

                </p>

            </div>


            <div class="summary-card">

                <div class="icon">👤</div>

                <h3>Admin</h3>

                <p style="font-size:20px;">

                    <?php echo htmlspecialchars($adminName); ?>

                </p>

            </div>

        </div>


        <!-- ADD ANNOUNCEMENT -->

        <div class="form-card">

            <h2>➕ Add New Announcement</h2>

            <form method="POST" action="announcements.php">

                <div class="form-group">

                    <label for="title">
                        Announcement Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Example: College Holiday"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="message">
                        Announcement Message
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Enter the announcement details..."
                        required
                    ></textarea>

                </div>


                <button
                    type="submit"
                    name="add_announcement"
                    class="add-btn"
                >
                    📢 Publish Announcement
                </button>

            </form>

        </div>


        <!-- EXISTING ANNOUNCEMENTS -->

        <h2 class="section-title">
            📋 Existing Announcements
        </h2>


        <?php if ($totalAnnouncements > 0): ?>

            <div class="announcement-list">

                <?php foreach ($announcements as $announcement): ?>

                    <div class="announcement-card">

                        <div class="announcement-top">

                            <div class="announcement-title">

                                📢
                                <?php
                                echo htmlspecialchars(
                                    $announcement["title"]
                                );
                                ?>

                            </div>

                            <div class="announcement-date">

                                📅
                                <?php
                                echo htmlspecialchars(
                                    $announcement["created_at"]
                                );
                                ?>

                            </div>

                        </div>


                        <div class="announcement-message">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $announcement["message"]
                                )
                            );
                            ?>

                        </div>


                        <form
                            method="POST"
                            action="announcements.php"
                            class="delete-form"
                            onsubmit="return confirm('Are you sure you want to delete this announcement?');"
                        >

                            <input
                                type="hidden"
                                name="delete_id"
                                value="<?php
                                echo (int)$announcement["id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                class="delete-btn"
                            >
                                🗑️ Delete
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                <div class="icon">📢</div>

                <h3>No Announcements Yet</h3>

                <p>
                    Add your first campus announcement using the form above.
                </p>

            </div>

        <?php endif; ?>


    </div>


    <!-- FOOTER -->

    <footer>

        © 2026 SmartCampus360 | Admin Announcement Management

    </footer>


</body>

</html>

<?php

$conn->close();

?>
```