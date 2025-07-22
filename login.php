<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uid = $_POST['uid'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM user WHERE UID = ? AND password = ?");
    $stmt->execute([$uid, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt2 = $pdo->prepare("SELECT alumniID FROM alumni WHERE email = ?");
        $stmt2->execute([$uid]);
        $alumni = $stmt2->fetch();

        if ($alumni) {
            $user['alumniID'] = $alumni['alumniID'];
        } else {
            $user['alumniID'] = null;
        }

        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        /* Reset and base */
        * {
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: white;
        }

        /* Background image and overlay */
        body {
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

        /* Centered form container */
        .form-container {
            position: fixed; /* Fix position relative to viewport */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0,0,0,0.85);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.9);
            width: 100%;
            max-width: 400px;
            z-index: 1; /* Above overlay */
        }

        /* Form elements */
        form h2 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
            font-size: 2rem;
            color: #f0d50c;
            text-shadow: 0 0 8px #f0d50c;
        }

        form input[type="text"],
        form input[type="password"] {
            width: 100%;
            padding: 14px 15px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
        }

        form input::placeholder {
            color: #666;
        }

        form button {
            width: 100%;
            padding: 14px 0;
            margin-top: 15px;
            background-color: #f0d50c;
            color: black;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #d4b409;
        }

        /* Error message */
        .error-msg {
            color: #ff5555;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="">
            <h2>Login</h2>
            <?php if (isset($error)) : ?>
                <p class="error-msg"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <input type="text" name="uid" placeholder="User ID (email?)" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
