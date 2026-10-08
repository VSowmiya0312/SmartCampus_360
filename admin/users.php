```php
<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

/*
|--------------------------------------------------------------------------
| ADMIN DETAILS
|--------------------------------------------------------------------------
*/

$admin_name = $_SESSION["name"] ?? "Admin";

/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

$users = [];

$sql = "
    SELECT
        id,
        name,
        email,
        role
    FROM users
    ORDER BY id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$total_users = count($users);

$student_count = 0;
$staff_count = 0;
$admin_count = 0;

foreach ($users as $user) {

    $role = strtolower(trim($user["role"]));

    if ($role === "student") {
        $student_count++;
    }

    if ($role === "staff") {
        $staff_count++;
    }

    if ($role === "admin") {
        $admin_count++;
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

    <title>Manage Users - SmartCampus360</title>

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

            min-height: 100vh;

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

            color: #1e293b;

            padding: 25px;
        }

        .container {

            max-width: 1200px;

            width: 100%;

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

            padding: 28px;

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

            font-size: 15px;

            opacity: 0.92;
        }

        .header-buttons {

            display: flex;

            gap: 10px;

            margin-top: 18px;

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

        /* SUMMARY */

        .summary {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }

        .card {

            background: white;

            border-radius: 17px;

            padding: 22px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);
        }

        .card-icon {

            font-size: 30px;

            margin-bottom: 8px;
        }

        .card h2 {

            color: #2563eb;

            font-size: 28px;

            margin-bottom: 5px;
        }

        .card p {

            color: #64748b;

            font-size: 13px;

            font-weight: bold;
        }

        /* USERS BOX */

        .users-box {

            background: white;

            padding: 20px;

            border-radius: 20px;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.08);

            overflow-x: auto;
        }

        .users-title {

            margin-bottom: 18px;

            font-size: 21px;
        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 700px;
        }

        th {

            background: #eef2ff;

            padding: 14px;

            text-align: left;

            font-size: 13px;

            color: #334155;
        }

        td {

            padding: 14px;

            border-bottom:
                1px solid #e2e8f0;

            font-size: 14px;
        }

        tr:hover {

            background: #f8fafc;
        }

        .user-name {

            font-weight: bold;

            color: #1e293b;
        }

        .email {

            color: #475569;
        }

        .id {

            font-weight: bold;

            color: #64748b;
        }

        /* ROLE */

        .role {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;
        }

        .student {

            background: #dbeafe;

            color: #1d4ed8;
        }

        .staff {

            background: #dcfce7;

            color: #166534;
        }

        .admin {

            background: #ede9fe;

            color: #6d28d9;
        }

        /* EMPTY */

        .empty {

            text-align: center;

            padding: 60px 20px;

            color: #64748b;
        }

        .empty-icon {

            font-size: 50px;

            margin-bottom: 15px;
        }

        /* MOBILE */

        @media (max-width: 850px) {

            .summary {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {

            body {

                padding: 12px;
            }

            .summary {

                grid-template-columns: 1fr;
            }

            .header h1 {

                font-size: 24px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <h1>👥 Manage Users</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($admin_name); ?>.
            View all registered SmartCampus360 users.
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


    <!-- SUMMARY -->

    <div class="summary">

        <div class="card">

            <div class="card-icon">
                👥
            </div>

            <h2>
                <?php echo $total_users; ?>
            </h2>

            <p>Total Users</p>

        </div>


        <div class="card">

            <div class="card-icon">
                🎓
            </div>

            <h2>
                <?php echo $student_count; ?>
            </h2>

            <p>Students</p>

        </div>


        <div class="card">

            <div class="card-icon">
                👨‍🔧
            </div>

            <h2>
                <?php echo $staff_count; ?>
            </h2>

            <p>Staff Members</p>

        </div>


        <div class="card">

            <div class="card-icon">
                🛡️
            </div>

            <h2>
                <?php echo $admin_count; ?>
            </h2>

            <p>Admins</p>

        </div>

    </div>


    <!-- USERS -->

    <div class="users-box">

        <h2 class="users-title">
            📋 Registered Users
        </h2>


        <?php if (count($users) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>User ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Role</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($users as $user): ?>

                    <?php

                    $role =
                        strtolower(
                            trim($user["role"])
                        );

                    ?>

                    <tr>

                        <td class="id">

                            #
                            <?php
                            echo htmlspecialchars(
                                $user["id"]
                            );
                            ?>

                        </td>


                        <td class="user-name">

                            <?php
                            echo htmlspecialchars(
                                $user["name"]
                            );
                            ?>

                        </td>


                        <td class="email">

                            <?php
                            echo htmlspecialchars(
                                $user["email"]
                            );
                            ?>

                        </td>


                        <td>

                            <span
                                class="role
                                <?php
                                echo htmlspecialchars($role);
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    ucfirst($role)
                                );
                                ?>

                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>


        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    👥
                </div>

                <h2>
                    No Users Found
                </h2>

                <p>
                    Registered users will appear here.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
```