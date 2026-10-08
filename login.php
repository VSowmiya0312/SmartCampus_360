<?php

session_start();

require_once "config/database.php";


/* =========================
   ONLY POST REQUEST
========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.html");
    exit;

}


/* =========================
   GET FORM DATA
========================= */

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";


/* =========================
   BASIC VALIDATION
========================= */

if ($email === "" || $password === "") {

    echo "<script>
        alert('Please enter email and password.');
        window.location.href = 'login.html';
    </script>";

    exit;

}


/* =========================
   FIND USER
========================= */

$sql = "
    SELECT
        id,
        name,
        email,
        password,
        role
    FROM users
    WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();


/* =========================
   EMAIL NOT FOUND
========================= */

if ($result->num_rows === 0) {

    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Email not registered. Please register first.');
        window.location.href = 'login.html';
    </script>";

    exit;

}


$user = $result->fetch_assoc();


/* =========================
   CHECK PASSWORD
========================= */

$password_valid = password_verify(
    $password,
    $user["password"]
);


/*
|--------------------------------------------------------------------------
| SUPPORT OLD PLAIN-TEXT PASSWORDS
|--------------------------------------------------------------------------
|
| Some users may have been registered before password_hash()
| was added to your registration system.
|
| If an old password is stored as plain text, allow that
| password to work once and immediately convert it into
| a secure password hash.
|
*/

if (!$password_valid) {

    if (
        $user["password"] !== "" &&
        hash_equals(
            $user["password"],
            $password
        )
    ) {

        $new_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $update_stmt = $conn->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        if ($update_stmt) {

            $update_stmt->bind_param(
                "si",
                $new_hash,
                $user["id"]
            );

            $update_stmt->execute();

            $update_stmt->close();

            $password_valid = true;

        }

    }

}


/* =========================
   PASSWORD INVALID
========================= */

if (!$password_valid) {

    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Invalid password. Please enter the correct password.');
        window.location.href = 'login.html';
    </script>";

    exit;

}


/* =========================
   CHECK ROLE
========================= */

$role = strtolower(
    trim($user["role"])
);

$allowed_roles = [
    "student",
    "staff",
    "admin"
];

if (!in_array($role, $allowed_roles)) {

    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Invalid role in database.');
        window.location.href = 'login.html';
    </script>";

    exit;

}


/* =========================
   CREATE SESSION
========================= */

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];

$_SESSION["name"] = $user["name"];

$_SESSION["email"] = $user["email"];

$_SESSION["role"] = $role;


/* =========================
   CLOSE DATABASE
========================= */

$stmt->close();

$conn->close();


/* =========================
   REDIRECT
========================= */

if ($role === "student") {

    header(
        "Location: student/dashboard.php"
    );

    exit;

}


if ($role === "staff") {

    header(
        "Location: staff/dashboard.php"
    );

    exit;

}


if ($role === "admin") {

    header(
        "Location: admin/dashboard.html"
    );

    exit;

}

?>