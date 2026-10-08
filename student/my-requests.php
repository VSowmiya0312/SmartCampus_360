```php
<?php

session_start();

require_once "../config/database.php";

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

if ($_SESSION["role"] !== "student") {
    header("Location: ../login.html");
    exit;
}

$studentName = $_SESSION["name"];
$studentEmail = $_SESSION["email"];

/* --------------------------------
   GET STUDENT REQUESTS
-------------------------------- */

$sql = "SELECT *
        FROM requests
        WHERE student_email = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $studentEmail);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Requests - SmartCampus360</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {

    font-family: Arial, sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2,
            #f093fb
        );

    padding: 25px;
}


/* HEADER */

.header {

    max-width: 1100px;

    margin: auto;

    background: white;

    padding: 20px 25px;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,0.20);

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;
}

.header h1 {

    color: #333;

    font-size: 25px;

}

.header p {

    color: #777;

    margin-top: 5px;

    font-size: 14px;
}

.back {

    text-decoration: none;

    background: #667eea;

    color: white;

    padding: 10px 16px;

    border-radius: 10px;

    font-weight: bold;
}

.back:hover {

    background: #5568d8;
}


/* MAIN */

.container {

    max-width: 1100px;

    margin: auto;
}

.page-title {

    color: white;

    text-align: center;

    margin-bottom: 25px;

    font-size: 28px;
}


/* REQUEST CARD */

.request-card {

    background: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 20px;

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.18);
}

.request-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;

    gap: 15px;
}

.track-id {

    font-size: 20px;

    font-weight: bold;

    color: #667eea;
}

.status {

    padding: 8px 14px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

    background: #eee;

    color: #333;
}


/* DETAILS */

.details {

    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(220px, 1fr));

    gap: 15px;

    margin-bottom: 18px;
}

.detail {

    background: #f7f7ff;

    padding: 15px;

    border-radius: 10px;
}

.detail strong {

    display: block;

    color: #555;

    margin-bottom: 5px;

    font-size: 13px;
}

.detail span {

    color: #222;

    font-size: 15px;
}


/* DESCRIPTION */

.description {

    background: #fafafa;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 18px;
}

.description strong {

    display: block;

    margin-bottom: 7px;

    color: #555;
}


/* TRACK BUTTON */

.track-button {

    display: inline-block;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2
        );

    color: white;

    padding: 11px 18px;

    border-radius: 10px;

    font-weight: bold;
}

.track-button:hover {

    opacity: 0.9;
}


/* NO REQUESTS */

.no-requests {

    background: white;

    padding: 50px 25px;

    border-radius: 18px;

    text-align: center;

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.18);
}

.no-requests .icon {

    font-size: 55px;

    margin-bottom: 15px;
}

.no-requests h2 {

    color: #333;

    margin-bottom: 10px;
}

.no-requests p {

    color: #777;

    margin-bottom: 20px;
}

.report-button {

    display: inline-block;

    background: #20c997;

    color: white;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 10px;

    font-weight: bold;
}


/* MOBILE */

@media (max-width: 600px) {

    body {
        padding: 15px;
    }

    .header {

        flex-direction: column;

        text-align: center;

        gap: 15px;
    }

    .request-top {

        flex-direction: column;

        align-items: flex-start;
    }

}

</style>

</head>

<body>


<!-- HEADER -->

<div class="header">

    <div>

        <h1>
            📋 My Requests
        </h1>

        <p>
            👤 <?php echo htmlspecialchars($studentName); ?>
            |
            📧 <?php echo htmlspecialchars($studentEmail); ?>
        </p>

    </div>

    <a
        href="dashboard.php"
        class="back"
    >
        ← Dashboard
    </a>

</div>


<!-- MAIN -->

<div class="container">

    <h2 class="page-title">
        Your Submitted Requests
    </h2>


<?php if ($result->num_rows > 0): ?>


    <?php while ($request = $result->fetch_assoc()): ?>


        <div class="request-card">


            <!-- TOP -->

            <div class="request-top">

                <div class="track-id">

                    🔍
                    <?php
                    echo htmlspecialchars(
                        $request["track_id"]
                    );
                    ?>

                </div>


                <div class="status">

                    <?php
                    echo htmlspecialchars(
                        $request["status"]
                    );
                    ?>

                </div>

            </div>


            <!-- DETAILS -->

            <div class="details">


                <div class="detail">

                    <strong>
                        📂 Category
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $request["category"]
                        );
                        ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>
                        📍 Location
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $request["location"]
                        );
                        ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>
                        👨‍🔧 Assigned Staff
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $request["assigned_staff"]
                        );
                        ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>
                        📅 Submitted
                    </strong>

                    <span>
                        <?php
                        echo date(
                            "d M Y, h:i A",
                            strtotime(
                                $request["created_at"]
                            )
                        );
                        ?>
                    </span>

                </div>


            </div>


            <!-- DESCRIPTION -->

            <div class="description">

                <strong>
                    📝 Description
                </strong>

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $request["description"]
                    )
                );
                ?>

            </div>


            <!-- TRACK BUTTON -->

            <a
                href="../track_request.php?track=<?php
                    echo urlencode(
                        $request["track_id"]
                    );
                ?>"
                class="track-button"
            >
                🔍 Track This Request
            </a>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <!-- NO REQUESTS -->

    <div class="no-requests">

        <div class="icon">
            📋
        </div>

        <h2>
            No Requests Yet
        </h2>

        <p>
            You have not submitted any
            campus issue requests.
        </p>

        <a
            href="../report.html"
            class="report-button"
        >
            📝 Report an Issue
        </a>

    </div>


<?php endif; ?>


</div>


</body>

</html>

<?php

$stmt->close();

$conn->close();

?>
```