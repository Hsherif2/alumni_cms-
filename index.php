<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Alumni CMS Dashboard</title>
    <style>
        /* Full-page background and overlay */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: white;
            background: url('images/ba5c8b4a-permalink.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0,0,0,0.6);
            z-index: 0;
        }

        /* Content wrapper sits above overlay */
        .content-wrapper {
            position: relative;
            z-index: 1;
            padding: 30px;
            max-width: 400px;
            margin: 40px auto;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.8);
        }

        h2 {
            color: #f0d50c;
            margin-top: 0;
            text-align: center;
            text-shadow: 0 0 6px #f0d50c;
        }

        ul {
            list-style: none;
            padding: 0;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        ul li a {
            color: #f0d50c;
            text-decoration: none;
            font-weight: bold;
            padding: 10px;
            border: 2px solid #f0d50c;
            border-radius: 6px;
            display: block;
            transition: background-color 0.3s ease, color 0.3s ease;
            text-align: center;
        }

        ul li a:hover {
            background-color: #f0d50c;
            color: black;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <h2>Welcome, <?= htmlspecialchars($user['fName']) ?>!</h2>
        <ul>
            <li><a href="alumni.php">Manage Alumni</a></li>
            <li><a href="address.php">Manage Addresses</a></li>
            <li><a href="degree.php">Manage Degrees</a></li>
            <li><a href="employment.php">Manage Employment</a></li>
            <li><a href="donation.php">Manage Donations</a></li>
            <li><a href="skillset.php">Manage Skills</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</body>
</html>







