<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="refresh" content="3;url=login.php">
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your existing style.css -->
</head>
<body>
    <div class="logout-box">
        <h2>You’ve been logged out.</h2>
        <p>Redirecting you to the login page...</p>
    
    </div>
</body>
</html>
