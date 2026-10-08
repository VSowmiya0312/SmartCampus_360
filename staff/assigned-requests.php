```php
<?php

session_start();

require_once "../config/database.php";

/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

/* =========================
   CHECK STAFF ROLE
========================= */

$user_role = strtolower(
    trim($_SESSION["role"] ?? "")
);

if ($user_role !== "staff") {
    die("Access denied. Please login using a staff account.");
}

/* =========================
   GET STAFF DETAILS
========================= */

$staff_name = $_SESSION["name"] ?? "";

if ($staff_name === "") {
    die("Staff name is missing from the login session.");
}

/* =========================
   GET ASSIGNED REQUESTS
========================= */

$sql = "SELECT *
        FROM requests
        WHERE assigned_staff = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "s",
    $staff_name
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Assigned Requests - SmartCampus360
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, Helvetica, sans-serif;

    min-height: 100vh;

    padding: 30px 15px;

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
}

/* =========================
   MAIN CONTAINER
========================= */

.container {

    max-width: 1150px;

    margin: auto;
}

/* =========================
   HEADER
========================= */

.header {

    background: rgba(255, 255, 255, 0.96);

    padding: 25px;

    border-radius: 18px;

    margin-bottom: 25px;

    box-shadow:
        0 12px 35px
        rgba(0, 0, 0, 0.25);

    text-align: center;
}

.header h1 {

    margin: 0;

    color: #222;

    font-size: 30px;
}

.header p {

    margin: 8px 0 0;

    color: #666;

    font-size: 16px;
}

.staff-name {

    color: #2346a3;

    font-weight: bold;
}

/* =========================
   REQUEST CARD
========================= */

.request-card {

    background: rgba(255, 255, 255, 0.96);

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 20px;

    box-shadow:
        0 12px 35px
        rgba(0, 0, 0, 0.22);
}

/* =========================
   TOP SECTION
========================= */

.request-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    flex-wrap: wrap;

    margin-bottom: 20px;

    border-bottom: 1px solid #eee;

    padding-bottom: 15px;
}

.track-id {

    color: #2346a3;

    font-size: 21px;

    font-weight: bold;
}

/* =========================
   STATUS
========================= */

.status {

    padding: 8px 14px;

    border-radius: 20px;

    font-size: 14px;

    font-weight: bold;

    display: inline-block;
}

.status-assigned {

    background: #fff3cd;

    color: #856404;
}

.status-progress {

    background: #dbeafe;

    color: #1e40af;
}

.status-completed {

    background: #d1fae5;

    color: #065f46;
}

.status-default {

    background: #eeeeee;

    color: #555;
}

/* =========================
   DETAILS
========================= */

.details {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

    margin-bottom: 20px;
}

.detail {

    background: #f5f7fb;

    padding: 15px;

    border-radius: 10px;
}

.detail strong {

    display: block;

    color: #333;

    margin-bottom: 5px;
}

.detail span {

    color: #666;

    line-height: 1.5;
}

.description {

    grid-column: 1 / -1;
}

/* =========================
   BUTTON
========================= */

.update-btn {

    display: inline-block;

    padding: 12px 20px;

    background: #2346a3;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    transition: 0.2s;
}

.update-btn:hover {

    background: #17347f;

    transform: translateY(-1px);
}

/* =========================
   EMPTY STATE
========================= */

.empty {

    background: rgba(255, 255, 255, 0.96);

    padding: 50px 25px;

    border-radius: 18px;

    text-align: center;

    box-shadow:
        0 12px 35px
        rgba(0, 0, 0, 0.22);
}

.empty-icon {

    font-size: 55px;

    margin-bottom: 10px;
}

.empty h2 {

    color: #333;

    margin-bottom: 8px;
}

.empty p {

    color: #666;
}

/* =========================
   BACK BUTTON
========================= */

.back {

    display: inline-block;

    margin-top: 10px;

    padding: 12px 20px;

    background: white;

    color: #2346a3;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    box-shadow:
        0 6px 20px
        rgba(0, 0, 0, 0.20);
}

.back:hover {

    background: #f2f4f8;
}

/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    body {

        padding: 20px 10px;
    }

    .header h1 {

        font-size: 25px;
    }

    .details {

        grid-template-columns: 1fr;
    }

    .description {

        grid-column: auto;
    }

    .request-top {

        align-items: flex-start;

        flex-direction: column;
    }

}

</style>

</head>

<body>

<div class="container">

    <!-- =========================
         HEADER
    ========================= -->

    <div class="header">

        <h1>
            📋 Assigned Requests
        </h1>

        <p>
            Requests assigned to
            <span class="staff-name">
                <?php
                echo htmlspecialchars($staff_name);
                ?>
            </span>
        </p>

    </div>


    <?php

    /* =========================
       DISPLAY REQUESTS
    ========================= */

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            /* =========================
               STATUS CLASS
            ========================= */

            $status = $row["status"] ?? "";

            if ($status === "Staff Assigned") {

                $status_class = "status-assigned";

            } elseif ($status === "Work In Progress") {

                $status_class = "status-progress";

            } elseif ($status === "Completed") {

                $status_class = "status-completed";

            } else {

                $status_class = "status-default";
            }

    ?>

        <div class="request-card">

            <div class="request-top">

                <div class="track-id">

                    🆔

                    <?php
                    echo htmlspecialchars(
                        $row["track_id"]
                    );
                    ?>

                </div>

                <div
                    class="status <?php
                        echo $status_class;
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $status
                    );
                    ?>

                </div>

            </div>


            <div class="details">

                <div class="detail">

                    <strong>
                        👤 Student
                    </strong>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $row["student_name"]
                        );
                        ?>

                    </span>

                </div>


                <div class="detail">

                    <strong>
                        📧 Student Email
                    </strong>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $row["student_email"]
                        );
                        ?>

                    </span>

                </div>


                <div class="detail">

                    <strong>
                        🏷️ Category
                    </strong>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $row["category"]
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
                            $row["location"]
                        );
                        ?>

                    </span>

                </div>


                <div class="detail description">

                    <strong>
                        📝 Description
                    </strong>

                    <span>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $row["description"]
                            )
                        );
                        ?>

                    </span>

                </div>

            </div>


            <!-- =========================
                 IMPORTANT:
                 TRACK ID IS SENT HERE
            ========================= -->

            <a
                href="update-request.php?track=<?php
                    echo urlencode(
                        $row["track_id"]
                    );
                ?>"
                class="update-btn"
            >

                🔧 Update Request

            </a>

        </div>

    <?php

        }

    } else {

    ?>

        <div class="empty">

            <div class="empty-icon">
                📭
            </div>

            <h2>
                No Assigned Requests
            </h2>

            <p>
                There are currently no requests
                assigned to you.
            </p>

        </div>

    <?php

    }

    $stmt->close();

    $conn->close();

    ?>


    <!-- =========================
         BACK TO DASHBOARD
    ========================= -->

    <div style="text-align:center;">

        <a
            href="dashboard.php"
            class="back"
        >
            ← Back to Staff Dashboard
        </a>

    </div>

</div>

</body>

</html>
```