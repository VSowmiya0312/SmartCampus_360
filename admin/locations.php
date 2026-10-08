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

$admin_name = $_SESSION["name"] ?? "Admin";

/*
|--------------------------------------------------------------------------
| GET LOCATIONS FROM REQUESTS
|--------------------------------------------------------------------------
|
| We use the existing requests table.
| No new database table is required.
|
*/

$locations = [];

$sql = "
    SELECT
        location,
        COUNT(*) AS total_requests
    FROM requests
    WHERE location IS NOT NULL
    AND TRIM(location) <> ''
    GROUP BY location
    ORDER BY total_requests DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}

/*
|--------------------------------------------------------------------------
| TOTAL UNIQUE LOCATIONS
|--------------------------------------------------------------------------
*/

$total_locations = count($locations);

/*
|--------------------------------------------------------------------------
| TOTAL REQUESTS WITH LOCATION
|--------------------------------------------------------------------------
*/

$total_location_requests = 0;

foreach ($locations as $location) {
    $total_location_requests +=
        (int)$location["total_requests"];
}

/*
|--------------------------------------------------------------------------
| MOST REPORTED LOCATION
|--------------------------------------------------------------------------
*/

$most_reported_location = "No data";

if ($total_locations > 0) {
    $most_reported_location =
        $locations[0]["location"];
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

    <title>Manage Locations - SmartCampus360</title>

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

            width: 100%;

            max-width: 1100px;

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
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }

        .summary-card {

            background: white;

            border-radius: 17px;

            padding: 22px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);
        }

        .summary-icon {

            font-size: 30px;

            margin-bottom: 8px;
        }

        .summary-card h2 {

            color: #2563eb;

            font-size: 27px;

            margin-bottom: 5px;
        }

        .summary-card p {

            color: #64748b;

            font-size: 13px;

            font-weight: bold;
        }

        .summary-card .small-text {

            color: #475569;

            font-size: 14px;

            font-weight: bold;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }

        /* LOCATION BOX */

        .location-box {

            background: white;

            border-radius: 20px;

            padding: 20px;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.08);
        }

        .location-title {

            font-size: 21px;

            margin-bottom: 18px;
        }

        /* LOCATION GRID */

        .location-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;
        }

        .location-card {

            border:
                1px solid #e2e8f0;

            border-radius: 16px;

            padding: 20px;

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #f8fafc
                );

            transition:
                transform 0.2s,
                box-shadow 0.2s;
        }

        .location-card:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, 0.08);
        }

        .location-icon {

            width: 45px;

            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #dbeafe;

            font-size: 23px;

            margin-bottom: 12px;
        }

        .location-name {

            font-size: 17px;

            font-weight: bold;

            color: #1e293b;

            margin-bottom: 8px;
        }

        .request-count {

            color: #64748b;

            font-size: 13px;
        }

        .request-count strong {

            color: #2563eb;

            font-size: 17px;
        }

        /* TABLE */

        .table-box {

            margin-top: 25px;

            background: white;

            border-radius: 20px;

            padding: 20px;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.08);

            overflow-x: auto;
        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 600px;
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

        .number {

            font-weight: bold;

            color: #64748b;
        }

        .location-name-table {

            font-weight: bold;

            color: #2563eb;
        }

        .count-badge {

            display: inline-block;

            background: #dbeafe;

            color: #1d4ed8;

            padding: 6px 12px;

            border-radius: 20px;

            font-weight: bold;

            font-size: 12px;
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

            .location-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .summary {

                grid-template-columns:
                    1fr;
            }
        }

        @media (max-width: 550px) {

            body {

                padding: 12px;
            }

            .location-grid {

                grid-template-columns:
                    1fr;
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

        <h1>📍 Campus Locations</h1>

        <p>
            View locations where students have reported
            campus issues.
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

        <div class="summary-card">

            <div class="summary-icon">
                📍
            </div>

            <h2>
                <?php echo $total_locations; ?>
            </h2>

            <p>
                Total Locations
            </p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                📋
            </div>

            <h2>
                <?php echo $total_location_requests; ?>
            </h2>

            <p>
                Location Requests
            </p>

        </div>


        <div class="summary-card">

            <div class="summary-icon">
                🔥
            </div>

            <div class="small-text">
                <?php
                echo htmlspecialchars(
                    $most_reported_location
                );
                ?>
            </div>

            <p>
                Most Reported Location
            </p>

        </div>

    </div>


    <!-- LOCATION CARDS -->

    <div class="location-box">

        <h2 class="location-title">
            🏫 Reported Campus Locations
        </h2>


        <?php if ($total_locations > 0): ?>

            <div class="location-grid">

                <?php foreach (
                    $locations as $location
                ): ?>

                    <div class="location-card">

                        <div class="location-icon">
                            📍
                        </div>

                        <div class="location-name">

                            <?php
                            echo htmlspecialchars(
                                $location["location"]
                            );
                            ?>

                        </div>

                        <div class="request-count">

                            Requests:

                            <strong>
                                <?php
                                echo (int)
                                    $location[
                                        "total_requests"
                                    ];
                                ?>
                            </strong>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    📍
                </div>

                <h2>
                    No Locations Found
                </h2>

                <p>
                    Locations will appear here when
                    students submit requests.
                </p>

            </div>

        <?php endif; ?>

    </div>


    <!-- LOCATION TABLE -->

    <?php if ($total_locations > 0): ?>

        <div class="table-box">

            <h2 class="location-title">
                📊 Location Request Summary
            </h2>

            <table>

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Location
                        </th>

                        <th>
                            Number of Requests
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php
                $number = 1;

                foreach ($locations as $location):
                ?>

                    <tr>

                        <td class="number">

                            <?php
                            echo $number++;
                            ?>

                        </td>

                        <td class="location-name-table">

                            📍

                            <?php
                            echo htmlspecialchars(
                                $location["location"]
                            );
                            ?>

                        </td>

                        <td>

                            <span
                                class="count-badge"
                            >

                                <?php
                                echo (int)
                                    $location[
                                        "total_requests"
                                    ];
                                ?>

                                Requests

                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
```