<?php

require_once "../config/database.php";

$track_id = trim($_GET["track"] ?? "");

if ($track_id === "") {
    die("Invalid Track ID.");
}

/* Get request */

$sql = "SELECT * FROM requests WHERE track_id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $track_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Request not found.");
}

$request = $result->fetch_assoc();

$stmt->close();


/* Get ALL registered staff */

$staff_sql = "SELECT id, name, email, department, status
              FROM staff
              ORDER BY name ASC";

$staff_result = $conn->query($staff_sql);


/* Assign staff */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = intval($_POST["staff_id"] ?? 0);

    if ($staff_id <= 0) {

        echo "<script>
                alert('Please select a staff member.');
                window.location.href='assign_staff.php?track=" .
                urlencode($track_id) .
                "';
              </script>";

        exit;
    }


    /* Get selected staff */

    $staff_sql2 = "SELECT name, email
                   FROM staff
                   WHERE id = ?
                   LIMIT 1";

    $staff_stmt = $conn->prepare($staff_sql2);

    $staff_stmt->bind_param("i", $staff_id);

    $staff_stmt->execute();

    $staff_data = $staff_stmt->get_result();


    if ($staff_data->num_rows === 0) {

        die("Staff member not found.");

    }

    $selected_staff = $staff_data->fetch_assoc();

    $staff_stmt->close();


    $staff_name = $selected_staff["name"];
    $staff_email = $selected_staff["email"];


    /* Update request */

    $update_sql = "UPDATE requests
                   SET assigned_staff = ?,
                       status = 'Staff Assigned'
                   WHERE track_id = ?";

    $update_stmt = $conn->prepare($update_sql);

    $update_stmt->bind_param(
        "ss",
        $staff_name,
        $track_id
    );

    $update_stmt->execute();

    $update_stmt->close();


    /* Notify student */

    $student_email = $request["student_email"];

    $message = "👨‍🔧 Staff member " .
               $staff_name .
               " has been assigned to your request. Track ID: " .
               $track_id;

    $notification_sql = "INSERT INTO notifications
                         (user_email, message)
                         VALUES (?, ?)";

    $notification_stmt = $conn->prepare($notification_sql);

    $notification_stmt->bind_param(
        "ss",
        $student_email,
        $message
    );

    $notification_stmt->execute();

    $notification_stmt->close();


    /* Notify staff */

    $staff_message = "📋 A new campus request has been assigned to you. Track ID: " .
                     $track_id;

    $staff_notification_sql = "INSERT INTO notifications
                               (user_email, message)
                               VALUES (?, ?)";

    $staff_notification_stmt =
        $conn->prepare($staff_notification_sql);

    $staff_notification_stmt->bind_param(
        "ss",
        $staff_email,
        $staff_message
    );

    $staff_notification_stmt->execute();

    $staff_notification_stmt->close();


    echo "<script>
            alert('Staff assigned successfully.');
            window.location.href='requests.html';
          </script>";

    exit;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Assign Staff - SmartCampus360</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 30px;
}

.container {
    max-width: 650px;
    margin: auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #777;
    margin-bottom: 25px;
}

.request-box {
    background: #f5f6ff;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.request-box p {
    margin: 8px 0;
    color: #444;
}

.track {
    font-weight: bold;
    color: #667eea;
    font-size: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

select {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 16px;
    margin-bottom: 20px;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    opacity: 0.9;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: #667eea;
    font-weight: bold;
}

.no-staff {
    padding: 15px;
    background: #fff3cd;
    color: #856404;
    border-radius: 10px;
    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>👨‍🔧 Assign Staff</h1>

<p class="subtitle">
Assign a staff member to this campus request
</p>


<div class="request-box">

<p class="track">
🔎 Track ID:
<?php echo htmlspecialchars($request["track_id"]); ?>
</p>

<p>
<strong>Student:</strong>
<?php echo htmlspecialchars($request["student_name"]); ?>
</p>

<p>
<strong>Category:</strong>
<?php echo htmlspecialchars($request["category"]); ?>
</p>

<p>
<strong>Location:</strong>
<?php echo htmlspecialchars($request["location"]); ?>
</p>

<p>
<strong>Status:</strong>
<?php echo htmlspecialchars($request["status"]); ?>
</p>

</div>


<form method="POST">

<label for="staff_id">
Select Staff Member
</label>

<?php if ($staff_result && $staff_result->num_rows > 0): ?>

<select id="staff_id" name="staff_id" required>

<option value="">
-- Select Staff --
</option>

<?php while ($staff = $staff_result->fetch_assoc()): ?>

<option value="<?php echo $staff["id"]; ?>">

<?php echo htmlspecialchars($staff["name"]); ?>

<?php if (!empty($staff["department"])): ?>

 - <?php echo htmlspecialchars($staff["department"]); ?>

<?php endif; ?>

</option>

<?php endwhile; ?>

</select>

<button type="submit">
👨‍🔧 Assign Staff
</button>

<?php else: ?>

<div class="no-staff">
⚠️ No staff members are registered yet.
</div>

<?php endif; ?>

</form>


<a href="requests.html" class="back">
← Back to Requests
</a>

</div>

</div>

</body>

</html>

<?php

$conn->close();

?>