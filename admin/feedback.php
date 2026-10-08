```php
<?php

session_start();

require_once "../config/database.php";

/* Check login */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

$adminName = $_SESSION["name"] ?? "Admin";

$message = "";
$messageType = "";

/* Check whether feedback table exists */
$tableCheck = $conn->query("SHOW TABLES LIKE 'feedback'");

if (!$tableCheck || $tableCheck->num_rows === 0) {
    die("
        <div style='
            font-family: Arial;
            text-align:center;
            padding:50px;
        '>
            <h2>Feedback table not found</h2>
            <p>Please create the feedback table first.</p>
        </div>
    ");
}

/* ---------------------------------------------------
   SUBMIT FEEDBACK
--------------------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $feedbackText = trim($_POST["feedback"] ?? "");

    if ($name === "" || $email === "" || $feedbackText === "") {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        /*
         * Insert into feedback table.
         *
         * This automatically detects common column names:
         * name / student_name
         * email / student_email
         * feedback / message / comments
         */

        $fieldResult = $conn->query("SHOW COLUMNS FROM feedback");

        $feedbackColumns = [];

        while ($field = $fieldResult->fetch_assoc()) {
            $feedbackColumns[] = $field["Field"];
        }

        /* Find name column */
        $nameColumn = null;

        foreach (["name", "student_name", "user_name", "full_name"] as $possible) {
            if (in_array($possible, $feedbackColumns)) {
                $nameColumn = $possible;
                break;
            }
        }

        /* Find email column */
        $emailColumn = null;

        foreach (["email", "student_email", "user_email"] as $possible) {
            if (in_array($possible, $feedbackColumns)) {
                $emailColumn = $possible;
                break;
            }
        }

        /* Find feedback/message column */
        $feedbackColumn = null;

        foreach (["feedback", "message", "comments", "comment", "feedback_text"] as $possible) {
            if (in_array($possible, $feedbackColumns)) {
                $feedbackColumn = $possible;
                break;
            }
        }

        if ($nameColumn && $emailColumn && $feedbackColumn) {

            $sql = "INSERT INTO feedback
                    (`$nameColumn`, `$emailColumn`, `$feedbackColumn`)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "sss",
                    $name,
                    $email,
                    $feedbackText
                );

                if ($stmt->execute()) {

                    $message = "✅ Feedback submitted successfully!";
                    $messageType = "success";

                } else {

                    $message = "Unable to submit feedback: " . $stmt->error;
                    $messageType = "error";
                }

                $stmt->close();

            } else {

                $message = "Unable to prepare feedback query: " . $conn->error;
                $messageType = "error";
            }

        } else {

            $message = "Your feedback table does not contain the required name, email and feedback columns.";
            $messageType = "error";
        }
    }
}


/* ---------------------------------------------------
   GET FEEDBACK DATA
--------------------------------------------------- */

$result = $conn->query("SELECT * FROM feedback ORDER BY 1 DESC");

if (!$result) {
    die("Unable to load feedback: " . $conn->error);
}


/* Get column names */
$columns = [];

$fieldResult = $conn->query("SHOW COLUMNS FROM feedback");

while ($field = $fieldResult->fetch_assoc()) {
    $columns[] = $field["Field"];
}


/* Count feedback */
$totalFeedback = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Feedback - SmartCampus360</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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

            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            background: white;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }

        .header h1 {
            color: #4f46e5;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
        }

        .summary {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            width: 250px;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .card .number {
            font-size: 36px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 8px;
        }

        .card p {
            color: #666;
            font-size: 16px;
        }

        /* Submit Feedback */

        .submit-box {
            background: white;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .submit-box h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            color: #444;
            margin-bottom: 7px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            font-family: Arial, sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            border: none;
            background: #4f46e5;
            color: white;
            padding: 13px 25px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .message {
            padding: 13px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
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

        /* Feedback list */

        .feedback-box {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow-x: auto;
        }

        .feedback-box h2 {
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            background: #4f46e5;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            color: #444;
        }

        tr:hover {
            background: #f5f5ff;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 17px;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 12px 22px;
            border-radius: 10px;
            margin: 5px;
            font-weight: bold;
            transition: 0.3s;
        }

        .home {
            background: #4f46e5;
            color: white;
        }

        .dashboard {
            background: #16a34a;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        @media (max-width: 600px) {

            body {
                padding: 15px;
            }

            .header {
                padding: 20px;
            }

            .card {
                width: 100%;
            }

            .submit-box,
            .feedback-box {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>💬 Student Feedback</h1>

        <p>
            SmartCampus360 Admin Panel
        </p>

        <p style="margin-top:8px;">
            Welcome, <strong>
                <?php echo htmlspecialchars($adminName); ?>
            </strong>
        </p>

    </div>


    <div class="summary">

        <div class="card">

            <div class="number">
                <?php echo $totalFeedback; ?>
            </div>

            <p>Total Feedback</p>

        </div>

    </div>


    <!-- SUBMIT FEEDBACK -->

    <div class="submit-box">

        <h2>📝 Submit Feedback</h2>

        <?php if ($message !== ""): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="feedback.php">

            <div class="form-group">

                <label for="name">
                    Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your name"
                    value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="feedback">
                    Feedback
                </label>

                <textarea
                    id="feedback"
                    name="feedback"
                    placeholder="Enter your feedback..."
                    required
                ><?php echo htmlspecialchars($_POST["feedback"] ?? ""); ?></textarea>

            </div>


            <button
                type="submit"
                class="submit-btn"
            >
                📤 Submit Feedback
            </button>

        </form>

    </div>


    <!-- VIEW FEEDBACK -->

    <div class="feedback-box">

        <h2>📋 Feedback Details</h2>

        <?php if ($totalFeedback > 0): ?>

            <table>

                <thead>

                    <tr>

                        <?php foreach ($columns as $column): ?>

                            <th>
                                <?php echo htmlspecialchars($column); ?>
                            </th>

                        <?php endforeach; ?>

                    </tr>

                </thead>

                <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <?php foreach ($columns as $column): ?>

                                <td>

                                    <?php

                                    $value = $row[$column] ?? "";

                                    if ($value === null || $value === "") {

                                        echo "-";

                                    } else {

                                        echo nl2br(
                                            htmlspecialchars($value)
                                        );

                                    }

                                    ?>

                                </td>

                            <?php endforeach; ?>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                📭 No feedback submitted yet.

            </div>

        <?php endif; ?>

    </div>


    <div class="buttons">

        <a
            href="dashboard.html"
            class="btn dashboard"
        >
            🏠 Admin Dashboard
        </a>

        <a
            href="../index.html"
            class="btn home"
        >
            🌐 Home
        </a>

    </div>

</div>

</body>

</html>
```