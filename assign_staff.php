```php
<?php

include 'config/database.php';

/*
|--------------------------------------------------------------------------
| Check POST request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}


/*
|--------------------------------------------------------------------------
| Get submitted values
|--------------------------------------------------------------------------
*/

$request_id = isset($_POST['request_id'])
    ? trim($_POST['request_id'])
    : '';

$staff_name = isset($_POST['staff_name'])
    ? trim($_POST['staff_name'])
    : '';


if (empty($request_id)) {
    die("Request ID is missing.");
}

if (empty($staff_name)) {
    die("Staff name is missing.");
}


/*
|--------------------------------------------------------------------------
| Get staff information
|--------------------------------------------------------------------------
*/

$staff_sql = "SELECT name, email
              FROM staff
              WHERE name = ?
              LIMIT 1";

$staff_stmt = $conn->prepare($staff_sql);

if (!$staff_stmt) {
    die("Staff query error: " . $conn->error);
}

$staff_stmt->bind_param("s", $staff_name);

if (!$staff_stmt->execute()) {
    die("Staff query failed: " . $staff_stmt->error);
}

$staff_result = $staff_stmt->get_result();


if ($staff_result->num_rows === 0) {
    $staff_stmt->close();
    die("Staff member not found: " . htmlspecialchars($staff_name));
}


$staff = $staff_result->fetch_assoc();

$selected_staff_name = $staff['name'];
$staff_email = $staff['email'];

$staff_stmt->close();


/*
|--------------------------------------------------------------------------
| Get request information
|--------------------------------------------------------------------------
*/

$request_sql = "SELECT id, track_id, student_email
                FROM requests
                WHERE id = ?
                LIMIT 1";

$request_stmt = $conn->prepare($request_sql);

if (!$request_stmt) {
    die("Request query error: " . $conn->error);
}

$request_stmt->bind_param("i", $request_id);

if (!$request_stmt->execute()) {
    die("Request query failed: " . $request_stmt->error);
}

$request_result = $request_stmt->get_result();


if ($request_result->num_rows === 0) {
    $request_stmt->close();
    die("Request not found.");
}


$request = $request_result->fetch_assoc();

$track_id = $request['track_id'];
$student_email = $request['student_email'];

$request_stmt->close();


/*
|--------------------------------------------------------------------------
| Update request with assigned staff
|--------------------------------------------------------------------------
*/

$update_sql = "UPDATE requests
               SET assigned_staff = ?,
                   status = 'Staff Assigned'
               WHERE id = ?";

$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    die("Request update error: " . $conn->error);
}

$update_stmt->bind_param(
    "si",
    $selected_staff_name,
    $request_id
);


if (!$update_stmt->execute()) {
    $update_stmt->close();
    die("Could not assign staff: " . $update_stmt->error);
}

$update_stmt->close();


/*
|--------------------------------------------------------------------------
| Create STAFF notification
|--------------------------------------------------------------------------
|
| user_email  = NULL
| track_id    = request track ID
| message     = staff notification
| is_read     = 0
| created_at  = current time
| staff_email = selected staff email
|
*/

$staff_message =
    "👨‍🔧 New request " .
    $track_id .
    " has been assigned to you. Please check your assigned requests.";


$staff_notification_sql = "INSERT INTO notifications
                            (
                                user_email,
                                track_id,
                                message,
                                is_read,
                                created_at,
                                staff_email
                            )
                            VALUES
                            (
                                NULL,
                                ?,
                                ?,
                                0,
                                NOW(),
                                ?
                            )";


$staff_notification_stmt =
    $conn->prepare($staff_notification_sql);


if (!$staff_notification_stmt) {
    die(
        "Staff notification query error: " .
        $conn->error
    );
}


$staff_notification_stmt->bind_param(
    "sss",
    $track_id,
    $staff_message,
    $staff_email
);


if (!$staff_notification_stmt->execute()) {

    $error = $staff_notification_stmt->error;

    $staff_notification_stmt->close();

    die(
        "Staff notification could not be created: " .
        $error
    );
}


$staff_notification_stmt->close();


/*
|--------------------------------------------------------------------------
| Create STUDENT notification
|--------------------------------------------------------------------------
|
| This keeps your existing student notification working.
|
*/

$student_message =
    "👨‍🔧 Staff member " .
    $selected_staff_name .
    " has been assigned to your request " .
    $track_id .
    ".";


$student_notification_sql =
    "INSERT INTO notifications
    (
        user_email,
        track_id,
        message,
        is_read,
        created_at,
        staff_email
    )
    VALUES
    (
        ?,
        ?,
        ?,
        0,
        NOW(),
        NULL
    )";


$student_notification_stmt =
    $conn->prepare($student_notification_sql);


if (!$student_notification_stmt) {

    die(
        "Student notification query error: " .
        $conn->error
    );
}


$student_notification_stmt->bind_param(
    "sss",
    $student_email,
    $track_id,
    $student_message
);


$student_notification_stmt->execute();

$student_notification_stmt->close();


/*
|--------------------------------------------------------------------------
| Success page
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Staff Assigned - SmartCampus360</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(
                135deg,
                #667eea,
                #764ba2
            );
        }

        .box {
            width: 90%;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);
        }

        .icon {
            font-size: 55px;
            margin-bottom: 10px;
        }

        h1 {
            color: #28a745;
            margin-bottom: 15px;
        }

        p {
            color: #555;
            line-height: 1.7;
            font-size: 16px;
        }

        .details {
            margin-top: 20px;
            padding: 15px;
            background: #f4f6ff;
            border-radius: 10px;
            text-align: left;
        }

        .details strong {
            color: #333;
        }

        .buttons {
            margin-top: 25px;
        }

        .button {
            display: inline-block;
            padding: 12px 22px;
            margin: 5px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .admin {
            background: #667eea;
        }

        .staff {
            background: #28a745;
        }

        .button:hover {
            opacity: 0.85;
        }

    </style>

</head>

<body>

    <div class="box">

        <div class="icon">✅</div>

        <h1>Staff Assigned Successfully</h1>

        <p>
            The request has been successfully assigned.
        </p>

        <div class="details">

            <p>
                <strong>Track ID:</strong>
                <?php echo htmlspecialchars($track_id); ?>
            </p>

            <p>
                <strong>Staff:</strong>
                <?php echo htmlspecialchars($selected_staff_name); ?>
            </p>

            <p>
                <strong>Staff Email:</strong>
                <?php echo htmlspecialchars($staff_email); ?>
            </p>

        </div>

        <p>
            🔔 Staff notification has been created successfully.
        </p>

        <div class="buttons">

            <a
                href="admin/requests.php"
                class="button admin">
                ← Admin Requests
            </a>

            <a
                href="staff/notifications.php"
                class="button staff">
                🔔 Staff Notifications
            </a>

        </div>

    </div>

</body>

</html>

<?php

$conn->close();

?>
```