```php
<?php

require_once "config/database.php";

/* ---------------------------------
   ONLY POST REQUEST ALLOWED
---------------------------------- */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}


/* ---------------------------------
   GET FORM DATA
---------------------------------- */

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";
$role = strtolower(trim($_POST["role"] ?? ""));


/* ---------------------------------
   BASIC VALIDATION
---------------------------------- */

if (
    $name === "" ||
    $email === "" ||
    $password === "" ||
    $confirm_password === "" ||
    $role === ""
) {
    echo "<script>
        alert('Please fill all fields.');
        window.location.href='register.html';
    </script>";
    exit;
}


/* ---------------------------------
   EMAIL VALIDATION
---------------------------------- */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
        alert('Please enter a valid email address.');
        window.location.href='register.html';
    </script>";
    exit;
}


/* ---------------------------------
   PASSWORD VALIDATION
---------------------------------- */

if (strlen($password) < 6) {
    echo "<script>
        alert('Password must contain at least 6 characters.');
        window.location.href='register.html';
    </script>";
    exit;
}


if ($password !== $confirm_password) {
    echo "<script>
        alert('Passwords do not match.');
        window.location.href='register.html';
    </script>";
    exit;
}


/* ---------------------------------
   CHECK VALID ROLE
---------------------------------- */

$allowed_roles = [
    "student",
    "staff",
    "admin"
];

if (!in_array($role, $allowed_roles)) {
    echo "<script>
        alert('Invalid role selected.');
        window.location.href='register.html';
    </script>";
    exit;
}


/* ---------------------------------
   CHECK WHETHER EMAIL ALREADY EXISTS
---------------------------------- */

$check_sql = "SELECT id FROM users WHERE email = ? LIMIT 1";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Database error: " . $conn->error);
}

$check_stmt->bind_param("s", $email);

$check_stmt->execute();

$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {

    $check_stmt->close();
    $conn->close();

    echo "<script>
        alert('This email is already registered. Please use another email.');
        window.location.href='register.html';
    </script>";

    exit;
}

$check_stmt->close();


/* ---------------------------------
   HASH PASSWORD
---------------------------------- */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* ---------------------------------
   INSERT USER INTO USERS TABLE
---------------------------------- */

$user_sql = "
    INSERT INTO users
    (name, email, password, role)
    VALUES (?, ?, ?, ?)
";

$user_stmt = $conn->prepare($user_sql);

if (!$user_stmt) {
    die("Database error: " . $conn->error);
}

$user_stmt->bind_param(
    "ssss",
    $name,
    $email,
    $hashed_password,
    $role
);


/* ---------------------------------
   EXECUTE USER INSERT
---------------------------------- */

if (!$user_stmt->execute()) {

    $user_stmt->close();
    $conn->close();

    echo "<script>
        alert('Registration failed. Please try again.');
        window.location.href='register.html';
    </script>";

    exit;
}

$user_stmt->close();


/* =================================================
   IMPORTANT:
   IF ROLE = STAFF
   ALSO ADD THE PERSON TO STAFF TABLE
================================================= */

if ($role === "staff") {

    $department = "Campus Maintenance";
    $phone = "";
    $status = "Available";


    /* ---------------------------------
       CHECK STAFF TABLE FIRST
    ---------------------------------- */

    $staff_check_sql = "
        SELECT id
        FROM staff
        WHERE email = ?
        LIMIT 1
    ";

    $staff_check_stmt = $conn->prepare($staff_check_sql);

    if (!$staff_check_stmt) {
        die("Staff database error: " . $conn->error);
    }

    $staff_check_stmt->bind_param(
        "s",
        $email
    );

    $staff_check_stmt->execute();

    $staff_result = $staff_check_stmt->get_result();


    /* ---------------------------------
       ADD TO STAFF TABLE
    ---------------------------------- */

    if ($staff_result->num_rows === 0) {

        $staff_sql = "
            INSERT INTO staff
            (name, email, department, phone, status)
            VALUES (?, ?, ?, ?, ?)
        ";

        $staff_stmt = $conn->prepare($staff_sql);

        if (!$staff_stmt) {
            die("Staff database error: " . $conn->error);
        }

        $staff_stmt->bind_param(
            "sssss",
            $name,
            $email,
            $department,
            $phone,
            $status
        );


        if (!$staff_stmt->execute()) {

            $staff_stmt->close();
            $staff_check_stmt->close();
            $conn->close();

            echo "<script>
                alert('User registered, but staff registration failed. Please contact admin.');
                window.location.href='login.html';
            </script>";

            exit;
        }

        $staff_stmt->close();
    }

    $staff_check_stmt->close();
}


/* ---------------------------------
   CLOSE DATABASE
---------------------------------- */

$conn->close();


/* ---------------------------------
   SUCCESS PAGE
---------------------------------- */

$display_role = ucfirst($role);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Registration Successful - SmartCampus360</title>

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

    padding: 20px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2
        );
}


.success-card {

    width: 100%;

    max-width: 500px;

    background: white;

    padding: 45px 35px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 20px 50px
        rgba(0, 0, 0, 0.20);
}


.success-icon {

    width: 80px;

    height: 80px;

    margin: 0 auto 20px;

    display: flex;

    justify-content: center;

    align-items: center;

    border-radius: 50%;

    background: #e8f8ee;

    font-size: 45px;
}


h1 {

    margin: 10px 0;

    color: #222;

    font-size: 30px;
}


p {

    color: #666;

    font-size: 16px;

    line-height: 1.6;
}


.role {

    display: inline-block;

    margin-top: 5px;

    padding: 7px 15px;

    border-radius: 20px;

    background: #eef0ff;

    color: #667eea;

    font-weight: bold;
}


.staff-message {

    margin-top: 20px;

    padding: 15px;

    border-radius: 10px;

    background: #f0f9ff;

    color: #1769aa;

    font-size: 14px;
}


.login-button {

    display: inline-block;

    margin-top: 25px;

    padding: 13px 30px;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #667eea,
            #764ba2
        );

    color: white;

    text-decoration: none;

    font-weight: bold;

    transition: 0.3s;
}


.login-button:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(102, 126, 234, 0.35);
}

</style>

</head>


<body>

<div class="success-card">

    <div class="success-icon">
        ✅
    </div>


    <h1>
        Registration Successful!
    </h1>


    <p>
        Welcome to <strong>SmartCampus360</strong>.
    </p>


    <p>
        Your account has been successfully created.
    </p>


    <div class="role">
        <?php echo htmlspecialchars($display_role); ?>
    </div>


    <?php if ($role === "staff") { ?>

        <div class="staff-message">

            👨‍🔧 <strong>Staff account created!</strong>

            <br><br>

            You have been automatically added
            to the staff list.

            <br>

            You can now be assigned campus requests
            by the administrator.

        </div>

    <?php } ?>


    <a
        href="login.html"
        class="login-button"
    >
        🔐 Login Now
    </a>

</div>

</body>

</html>
```