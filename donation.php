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

// Handle deleting a donation
if (isset($_GET['deleteID']) && $alumniID > 0) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = $pdo->prepare("DELETE FROM donations WHERE donationID = ? AND alumniID = ?");
    if ($stmt->execute([$deleteID, $alumniID])) {
        $successMessage = "Donation record deleted.";
    } else {
        $errorMessage = "Failed to delete donation record.";
    }
}

// Handle adding donation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumniIDPost = intval($_POST['alumniID']);
    $amount = $_POST['donationAmt'];
    $date = $_POST['donationDT'];
    $reason = trim($_POST['reason']);
    $description = trim($_POST['description']);

    if ($alumniIDPost > 0 && $amount && $date) {
        $sql = "INSERT INTO donations (alumniID, donationAmt, donationDT, reason, description)
                VALUES (:alumniID, :donationAmt, :donationDT, :reason, :description)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':alumniID' => $alumniIDPost,
                ':donationAmt' => $amount,
                ':donationDT' => $date,
                ':reason' => $reason,
                ':description' => $description
            ]);
            $successMessage = "Donation added successfully!";
            $alumniID = $alumniIDPost;
        } catch (PDOException $e) {
            $errorMessage = "Failed to add donation: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill in required fields: Alumni ID, Amount, and Date.";
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

// Fetch donations
$donations = [];
$alumniName = "";
if ($alumniID > 0) {
    $stmt = $pdo->prepare("SELECT fName, lName FROM alumni WHERE alumniID = ?");
    $stmt->execute([$alumniID]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($alumni) {
        $alumniName = $alumni['fName'] . ' ' . $alumni['lName'];
    }

    $stmt = $pdo->prepare("SELECT * FROM donations WHERE alumniID = ? ORDER BY donationDT DESC");
    $stmt->execute([$alumniID]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Donation Management</h1>

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
        <h2>Donations for <?= htmlspecialchars($alumniName) ?></h2>

        <?php if ($successMessage): ?>
            <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <?php if ($donations): ?>
            <table>
                <tr>
                    <th>Amount</th><th>Date</th><th>Reason</th><th>Description</th><th>Actions</th>
                </tr>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?= htmlspecialchars($donation['donationAmt']) ?></td>
                        <td><?= htmlspecialchars($donation['donationDT']) ?></td>
                        <td><?= htmlspecialchars($donation['reason']) ?></td>
                        <td><?= htmlspecialchars($donation['description']) ?></td>
                        <td>
                            <a href="?alumniID=<?= $alumniID ?>&deleteID=<?= $donation['donationID'] ?>"
                               onclick="return confirm('Are you sure you want to delete this donation record?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No donations found.</p>
        <?php endif; ?>

        <h3>Add New Donation</h3>
        <form method="POST" action="">
            <input type="hidden" name="alumniID" value="<?= $alumniID ?>">
            <label>Amount ($)*:
                <input type="number" name="donationAmt" step="0.01" required>
            </label><br>

            <label>Date*:
                <input type="date" name="donationDT" required>
            </label><br>

            <label>Reason:
                <input type="text" name="reason" maxlength="200">
            </label><br>

            <label>Description:
                <textarea name="description" rows="3" maxlength="200"></textarea>
            </label><br>

            <button type="submit">Add Donation</button>
        </form>
    <?php endif; ?>

    <p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>
</body>
</html>

