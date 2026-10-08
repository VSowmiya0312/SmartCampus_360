```php
<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| ADMIN LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$admin_name = $_SESSION["name"] ?? "Admin";
$admin_role = $_SESSION["role"] ?? "";

/*
|--------------------------------------------------------------------------
| ASSIGN STAFF
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $request_id = (int)($_POST["request_id"] ?? 0);
    $staff_name = trim($_POST["staff_name"] ?? "");

    if ($request_id <= 0 || $staff_name === "") {

        $message = "Please select a staff member.";
        $message_type = "error";

    } else {

        /*
        | Get request details
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                track_id,
                student_name,
                student_email,
                status,
                assigned_staff
            FROM requests
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $message = "Database error: " . $conn->error;
            $message_type = "error";

        } else {

            $stmt->bind_param("i", $request_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $request = $result->fetch_assoc();

            $stmt->close();

            if (!$request) {

                $message = "Request not found.";
                $message_type = "error";

            } else {

                /*
                | Update assigned staff
                */

                $update = $conn->prepare("
                    UPDATE requests
                    SET
                        assigned_staff = ?,
                        status = 'Staff Assigned'
                    WHERE id = ?
                ");

                if (!$update) {

                    $message = "Unable to update request.";
                    $message_type = "error";

                } else {

                    $update->bind_param(
                        "si",
                        $staff_name,
                        $request_id
                    );

                    if ($update->execute()) {

                        /*
                        |--------------------------------------------------------------------------
                        | STUDENT NOTIFICATION
                        |--------------------------------------------------------------------------
                        */

                        $student_email = $request["student_email"];
                        $track_id = $request["track_id"];

                        $student_message =
                            "Staff Assigned: " .
                            $staff_name .
                            " has been assigned to your request " .
                            $track_id .
                            ".";

                        $notification = $conn->prepare("
                            INSERT INTO notifications
                            (
                                user_email,
                                track_id,
                                message,
                                created_at,
                                is_read
                            )
                            VALUES
                            (?, ?, ?, NOW(), 0)
                        ");

                        if ($notification) {

                            $notification->bind_param(
                                "sss",
                                $student_email,
                                $track_id,
                                $student_message
                            );

                            $notification->execute();
                            $notification->close();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | STAFF EMAIL
                        |--------------------------------------------------------------------------
                        */

                        $staff_email = "";

                        $staff_stmt = $conn->prepare("
                            SELECT email
                            FROM staff
                            WHERE LOWER(TRIM(name)) =
                                  LOWER(TRIM(?))
                            LIMIT 1
                        ");

                        if ($staff_stmt) {

                            $staff_stmt->bind_param(
                                "s",
                                $staff_name
                            );

                            $staff_stmt->execute();

                            $staff_result =
                                $staff_stmt->get_result();

                            if ($staff_row =
                                $staff_result->fetch_assoc()) {

                                $staff_email =
                                    $staff_row["email"];
                            }

                            $staff_stmt->close();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | STAFF NOTIFICATION
                        |--------------------------------------------------------------------------
                        */

                        if ($staff_email !== "") {

                            $staff_message =
                                "New request assigned to you: " .
                                $track_id .
                                ".";

                            $staff_notification =
                                $conn->prepare("
                                    INSERT INTO notifications
                                    (
                                        user_email,
                                        track_id,
                                        message,
                                        created_at,
                                        is_read
                                    )
                                    VALUES
                                    (?, ?, ?, NOW(), 0)
                                ");

                            if ($staff_notification) {

                                $staff_notification->bind_param(
                                    "sss",
                                    $staff_email,
                                    $track_id,
                                    $staff_message
                                );

                                $staff_notification->execute();
                                $staff_notification->close();
                            }
                        }

                        $message =
                            "Staff successfully assigned to " .
                            $track_id .
                            ".";

                        $message_type = "success";

                    } else {

                        $message =
                            "Unable to assign staff: " .
                            $update->error;

                        $message_type = "error";
                    }

                    $update->close();
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET STAFF LIST
|--------------------------------------------------------------------------
*/

$staff_list = [];

$staff_result = $conn->query("
    SELECT
        id,
        name,
        email,
        department,
        status
    FROM staff
    ORDER BY name ASC
");

if ($staff_result) {

    while ($staff_row = $staff_result->fetch_assoc()) {

        $staff_list[] = $staff_row;
    }
}

/*
|--------------------------------------------------------------------------
| GET REQUESTS
|--------------------------------------------------------------------------
*/

$requests = [];

$request_result = $conn->query("
    SELECT
        id,
        track_id,
        student_name,
        student_email,
        category,
        location,
        description,
        status,
        assigned_staff,
        created_at
    FROM requests
    ORDER BY id DESC
");

if ($request_result) {

    while ($row = $request_result->fetch_assoc()) {

        $requests[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| SUMMARY COUNTS
|--------------------------------------------------------------------------
*/

$total_requests = count($requests);

$submitted_count = 0;
$assigned_count = 0;
$progress_count = 0;
$completed_count = 0;

foreach ($requests as $row) {

    $status = trim($row["status"]);

    if ($status === "Submitted") {

        $submitted_count++;

    } elseif ($status === "Staff Assigned") {

        $assigned_count++;

    } elseif ($status === "In Progress") {

        $progress_count++;

    } elseif ($status === "Completed") {

        $completed_count++;
    }
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

    <title>Manage Requests - SmartCampus360</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            /* ONLY BACKGROUND CHANGED */

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

            min-height: 100vh;

            color: #1e293b;

            padding: 25px;
        }

        .container {

            width: 100%;

            max-width: 1450px;

            margin: auto;
        }

        /* HEADER */

        .header {

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #7c3aed
                );

            color: white;

            padding: 25px;

            border-radius: 20px;

            margin-bottom: 25px;

            box-shadow:
                0 10px 30px
                rgba(37, 99, 235, 0.20);
        }

        .header h1 {

            font-size: 30px;

            margin-bottom: 8px;
        }

        .header p {

            opacity: 0.92;

            font-size: 15px;
        }

        .header-buttons {

            margin-top: 18px;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }

        .header-button {

            display: inline-block;

            padding: 10px 18px;

            background: white;

            color: #2563eb;

            text-decoration: none;

            border-radius: 10px;

            font-weight: bold;

            font-size: 14px;
        }

        .header-button:hover {

            background: #eff6ff;
        }

        /* MESSAGE */

        .message {

            padding: 15px 18px;

            border-radius: 12px;

            margin-bottom: 20px;

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

            grid-template-columns:
                repeat(5, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }

        .summary-card {

            background: white;

            border-radius: 16px;

            padding: 20px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);
        }

        .summary-icon {

            font-size: 28px;

            margin-bottom: 8px;
        }

        .summary-card h2 {

            font-size: 27px;

            color: #2563eb;

            margin-bottom: 5px;
        }

        .summary-card p {

            color: #64748b;

            font-size: 13px;

            font-weight: bold;
        }

        /* REQUEST BOX */

        .request-box {

            background: white;

            border-radius: 20px;

            padding: 20px;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.08);

            overflow-x: auto;
        }

        .request-box h2 {

            margin-bottom: 18px;

            color: #1e293b;
        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1250px;
        }

        th {

            background: #eef2ff;

            color: #334155;

            padding: 14px 10px;

            text-align: left;

            font-size: 13px;

            white-space: nowrap;
        }

        td {

            padding: 14px 10px;

            border-bottom:
                1px solid #e2e8f0;

            font-size: 13px;

            vertical-align: middle;
        }

        tr:hover {

            background: #f8fafc;
        }

        .track {

            color: #2563eb;

            font-weight: bold;
        }

        .student-name {

            font-weight: bold;

            color: #1e293b;
        }

        .student-email {

            color: #64748b;

            font-size: 11px;

            margin-top: 3px;
        }

        .description {

            max-width: 180px;

            line-height: 1.4;
        }

        /* STATUS */

        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            white-space: nowrap;
        }

        .status-submitted {

            background: #fef3c7;

            color: #92400e;
        }

        .status-assigned {

            background: #dbeafe;

            color: #1d4ed8;
        }

        .status-progress {

            background: #ede9fe;

            color: #6d28d9;
        }

        .status-completed {

            background: #dcfce7;

            color: #166534;
        }

        /* ASSIGN FORM */

        .assign-form {

            display: flex;

            gap: 7px;

            align-items: center;

            min-width: 240px;
        }

        .assign-form select {

            padding: 8px;

            border:
                1px solid #cbd5e1;

            border-radius: 8px;

            background: white;

            outline: none;

            min-width: 145px;
        }

        .assign-form select:focus {

            border-color: #2563eb;
        }

        .assign-button {

            padding: 8px 12px;

            border: none;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-weight: bold;

            cursor: pointer;

            white-space: nowrap;
        }

        .assign-button:hover {

            background: #1d4ed8;
        }

        .assigned-staff {

            color: #15803d;

            font-weight: bold;

            margin-bottom: 5px;
        }

        .reassign-button {

            padding: 5px 9px;

            border: none;

            border-radius: 7px;

            background: #f1f5f9;

            color: #475569;

            cursor: pointer;

            font-size: 11px;
        }

        .reassign-button:hover {

            background: #e2e8f0;
        }

        .view-button {

            display: inline-block;

            padding: 8px 12px;

            background: #7c3aed;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            font-size: 12px;

            font-weight: bold;

            white-space: nowrap;
        }

        .view-button:hover {

            background: #6d28d9;
        }

        .not-assigned {

            color: #dc2626;

            font-weight: bold;

            font-size: 12px;

        }

        .empty {

            text-align: center;

            padding: 60px 20px;

            color: #64748b;
        }

        .empty-icon {

            font-size: 50px;

            margin-bottom: 15px;
        }

        /* RESPONSIVE */

        @media (max-width: 1100px) {

            .summary {

                grid-template-columns:
                    repeat(3, 1fr);
            }
        }

        @media (max-width: 700px) {

            body {

                padding: 12px;
            }

            .summary {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .header h1 {

                font-size: 24px;
            }
        }

        @media (max-width: 450px) {

            .summary {

                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <h1>📋 Manage Student Requests</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($admin_name); ?>.
            Manage requests and assign staff members.
        </p>

        <div class="header-buttons">

            <a
                href="dashboard.html"
                class="header-button"
            >
                ← Back to Dashboard
            </a>

            <a
                href="../index.html"
                class="header-button"
            >
                🏠 Home
            </a>

        </div>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- SUMMARY -->

    <div class="summary">

        <div class="summary-card">

            <div class="summary-icon">
                📋
            </div>

            <h2>
                <?php echo $total_requests; ?>
            </h2>

            <p>Total Requests</p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                🆕
            </div>

            <h2>
                <?php echo $submitted_count; ?>
            </h2>

            <p>Submitted</p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                👨‍🔧
            </div>

            <h2>
                <?php echo $assigned_count; ?>
            </h2>

            <p>Staff Assigned</p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                🔧
            </div>

            <h2>
                <?php echo $progress_count; ?>
            </h2>

            <p>In Progress</p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                ✅
            </div>

            <h2>
                <?php echo $completed_count; ?>
            </h2>

            <p>Completed</p>

        </div>

    </div>


    <!-- REQUESTS -->

    <div class="request-box">

        <h2>
            📋 All Student Requests
        </h2>


        <?php if (count($requests) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Track ID</th>

                        <th>Student</th>

                        <th>Category</th>

                        <th>Location</th>

                        <th>Description</th>

                        <th>Status</th>

                        <th>Assigned Staff</th>

                        <th>Date</th>

                        <th>View</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($requests as $request): ?>

                    <?php

                    $status =
                        trim($request["status"]);

                    if ($status === "Completed") {

                        $status_class =
                            "status-completed";

                    } elseif ($status === "In Progress") {

                        $status_class =
                            "status-progress";

                    } elseif ($status === "Staff Assigned") {

                        $status_class =
                            "status-assigned";

                    } else {

                        $status_class =
                            "status-submitted";
                    }

                    $assigned =
                        trim($request["assigned_staff"]);

                    ?>

                    <tr>

                        <!-- ID -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $request["id"]
                            );
                            ?>

                        </td>


                        <!-- TRACK ID -->

                        <td class="track">

                            <?php
                            echo htmlspecialchars(
                                $request["track_id"]
                            );
                            ?>

                        </td>


                        <!-- STUDENT -->

                        <td>

                            <div class="student-name">

                                <?php
                                echo htmlspecialchars(
                                    $request["student_name"]
                                );
                                ?>

                            </div>

                            <div class="student-email">

                                <?php
                                echo htmlspecialchars(
                                    $request["student_email"]
                                );
                                ?>

                            </div>

                        </td>


                        <!-- CATEGORY -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $request["category"]
                            );
                            ?>

                        </td>


                        <!-- LOCATION -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $request["location"]
                            );
                            ?>

                        </td>


                        <!-- DESCRIPTION -->

                        <td class="description">

                            <?php
                            echo htmlspecialchars(
                                $request["description"]
                            );
                            ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="status
                                <?php echo $status_class; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $status
                                );
                                ?>

                            </span>

                        </td>


                        <!-- ASSIGN STAFF -->

                        <td>

                            <?php if (
                                $assigned === "" ||
                                strtolower($assigned)
                                === "not assigned"
                            ): ?>

                                <form
                                    method="POST"
                                    class="assign-form"
                                >

                                    <input
                                        type="hidden"
                                        name="request_id"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $request["id"]
                                        );
                                        ?>"
                                    >

                                    <select
                                        name="staff_name"
                                        required
                                    >

                                        <option value="">
                                            Select Staff
                                        </option>

                                        <?php
                                        foreach (
                                            $staff_list
                                            as $staff
                                        ):
                                        ?>

                                            <option
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $staff["name"]
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $staff["name"]
                                                );
                                                ?>

                                                <?php
                                                if (
                                                    !empty(
                                                        $staff["department"]
                                                    )
                                                ):
                                                ?>

                                                    -
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $staff[
                                                            "department"
                                                        ]
                                                    );
                                                    ?>

                                                <?php endif; ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>


                                    <button
                                        type="submit"
                                        class="assign-button"
                                    >
                                        Assign
                                    </button>

                                </form>


                            <?php else: ?>

                                <div class="assigned-staff">

                                    👨‍🔧
                                    <?php
                                    echo htmlspecialchars(
                                        $assigned
                                    );
                                    ?>

                                </div>

                                <?php if (
                                    $status !== "Completed"
                                ): ?>

                                    <form
                                        method="POST"
                                        class="assign-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="request_id"
                                            value="<?php
                                            echo htmlspecialchars(
                                                $request["id"]
                                            );
                                            ?>"
                                        >

                                        <select
                                            name="staff_name"
                                            required
                                        >

                                            <option value="">
                                                Change Staff
                                            </option>

                                            <?php
                                            foreach (
                                                $staff_list
                                                as $staff
                                            ):
                                            ?>

                                                <option
                                                    value="<?php
                                                    echo htmlspecialchars(
                                                        $staff["name"]
                                                    );
                                                    ?>"
                                                >

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $staff["name"]
                                                    );
                                                    ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <button
                                            type="submit"
                                            class="reassign-button"
                                        >
                                            Change
                                        </button>

                                    </form>

                                <?php endif; ?>

                            <?php endif; ?>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $request["created_at"]
                                )
                            );

                            ?>

                        </td>


                        <!-- VIEW -->

                        <td>

                            <a
                                href="../track_request.php?track=<?php
                                echo urlencode(
                                    $request["track_id"]
                                );
                                ?>"
                                target="_blank"
                                class="view-button"
                            >
                                🔍 View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>


        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    📭
                </div>

                <h2>
                    No Requests Found
                </h2>

                <p>
                    Student requests will appear here
                    when they are submitted.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
```