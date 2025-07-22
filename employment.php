<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$searchTerm = $_GET['search'] ?? '';
$alumniID = isset($_GET['alumniID']) ? intval($_GET['alumniID']) : 0;
$successMessage = "";
$errorMessage = "";

// Handle deleting an employment record
if (isset($_GET['deleteID']) && $alumniID > 0) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = $pdo->prepare("DELETE FROM employment WHERE EID = ? AND alumniID = ?");
    if ($stmt->execute([$deleteID, $alumniID])) {
        $successMessage = "Employment record deleted.";
    } else {
        $errorMessage = "Failed to delete employment record.";
    }
}

// Handle adding employment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alumniIDPost = intval($_POST['alumniID']);
    $company = trim($_POST['company']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);
    $jobTitle = trim($_POST['jobTitle']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'] ?: null;
    $currentYN = strtoupper(trim($_POST['currentYN']));

    if ($alumniIDPost > 0 && $company && $jobTitle && $startDate) {
        $sql = "INSERT INTO employment (alumniID, company, city, state, zip, jobTitle, startDate, endDate, currentYN)
                VALUES (:alumniID, :company, :city, :state, :zip, :jobTitle, :startDate, :endDate, :currentYN)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':alumniID' => $alumniIDPost,
                ':company' => $company,
                ':city' => $city,
                ':state' => $state,
                ':zip' => $zip,
                ':jobTitle' => $jobTitle,
                ':startDate' => $startDate,
                ':endDate' => $endDate,
                ':currentYN' => $currentYN
            ]);
            $successMessage = "Employment record added successfully!";
            $alumniID = $alumniIDPost;
        } catch (PDOException $e) {
            $errorMessage = "Failed to add employment: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill required fields: Alumni, Company, Job Title, Start Date.";
    }
}

// Alumni search
$alumniList = [];
if ($searchTerm) {
    $stmt = $pdo->prepare("SELECT alumniID, fName, lName FROM alumni WHERE fName LIKE ? OR lName LIKE ? ORDER BY lName, fName");
    $likeTerm = "%$searchTerm%";
    $stmt->execute([$likeTerm, $likeTerm]);
    $alumniList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch employment
$employments = [];
$alumniName = "";
if ($alumniID > 0) {
    $stmt = $pdo->prepare("SELECT fName, lName FROM alumni WHERE alumniID = ?");
    $stmt->execute([$alumniID]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($alumni) {
        $alumniName = $alumni['fName'] . ' ' . $alumni['lName'];
    }
    $stmt = $pdo->prepare("SELECT * FROM employment WHERE alumniID = ? ORDER BY startDate DESC");
    $stmt->execute([$alumniID]);
    $employments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alumni Employment Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Alumni Employment Management</h1>

    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search alumni by name" value="<?= htmlspecialchars($searchTerm) ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php if ($searchTerm): ?>
        <h2>Alumni matching "<?= htmlspecialchars($searchTerm) ?>"</h2>
        <?php if ($alumniList): ?>
            <ul>
            <?php foreach ($alumniList as $alumni): ?>
                <li>
                    <a href="?alumniID=<?= $alumni['alumniID'] ?>">
                        <?= htmlspecialchars($alumni['fName'] . ' ' . $alumni['lName']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No alumni found.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($alumniID > 0): ?>
        <h2>Employment History for <?= htmlspecialchars($alumniName) ?></h2>

        <?php if ($successMessage): ?>
            <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <?php if ($employments): ?>
            <table>
                <tr>
                    <th>Company</th><th>City</th><th>State</th><th>ZIP</th>
                    <th>Job Title</th><th>Start Date</th><th>End Date</th><th>Current</th><th>Actions</th>
                </tr>
                <?php foreach ($employments as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <td><?= htmlspecialchars($job['city']) ?></td>
                        <td><?= htmlspecialchars($job['state']) ?></td>
                        <td><?= htmlspecialchars($job['zip']) ?></td>
                        <td><?= htmlspecialchars($job['jobTitle']) ?></td>
                        <td><?= htmlspecialchars($job['startDate']) ?></td>
                        <td><?= htmlspecialchars($job['endDate'] ?: 'N/A') ?></td>
                        <td><?= htmlspecialchars($job['currentYN']) ?></td>
                        <td>
                            <a href="?alumniID=<?= $alumniID ?>&deleteID=<?= $job['EID'] ?>"
                               onclick="return confirm('Are you sure you want to delete this employment record?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No employment records found.</p>
        <?php endif; ?>

        <h3>Add New Employment Record</h3>
        <form method="POST" action="">
            <input type="hidden" name="alumniID" value="<?= $alumniID ?>">
            <label>Company*:
                <input type="text" name="company" required>
            </label><br>

            <label>City:
                <input type="text" name="city">
            </label><br>

            <label>State:
                <input type="text" name="state" maxlength="2">
            </label><br>

            <label>ZIP:
                <input type="text" name="zip" maxlength="10">
            </label><br>

            <label>Job Title*:
                <input type="text" name="jobTitle" required>
            </label><br>

            <label>Start Date*:
                <input type="date" name="startDate" required>
            </label><br>

            <label>End Date:
                <input type="date" name="endDate">
            </label><br>

            <label>Current Job? (Y/N):
                <input type="text" name="currentYN" maxlength="1" pattern="[YNyn]" title="Enter Y or N">
            </label><br>

            <button type="submit">Add Employment</button>
        </form>
    <?php endif; ?>

    <p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>


</body>
</html>
