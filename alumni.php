<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$searchTerm = $_GET['search'] ?? '';
$successMessage = "";
$errorMessage = "";

// Delete alumni
if (isset($_GET['deleteID'])) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = $pdo->prepare("DELETE FROM alumni WHERE alumniID = ?");
    if ($stmt->execute([$deleteID])) {
        $successMessage = "Alumni record deleted.";
    } else {
        $errorMessage = "Failed to delete alumni.";
    }
}

// Add alumni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fName = trim($_POST['fName']);
    $lName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($fName && $lName && $email && $phone) {
        $stmt = $pdo->prepare("INSERT INTO alumni (fName, lName, email, phone) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$fName, $lName, $email, $phone]);
            $successMessage = "Alumni added successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Insert failed: " . $e->getMessage();
        }
    } else {
        $errorMessage = "All fields are required.";
    }
}

// Search alumni (only show results if user searched)
$alumniList = [];
if ($searchTerm) {
    $stmt = $pdo->prepare("SELECT * FROM alumni WHERE fName LIKE ? OR lName LIKE ?");
    $likeTerm = "%$searchTerm%";
    $stmt->execute([$likeTerm, $likeTerm]);
    $alumniList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Alumni</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Alumni Management</h1>

    <form method="GET">
        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($searchTerm) ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php if ($successMessage): ?>
        <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <h2>Add New Alumni</h2>
    <form method="POST">
        <label>First Name*:
            <input type="text" name="fName" required>
        </label><br>
        <label>Last Name*:
            <input type="text" name="lName" required>
        </label><br>
        <label>Email*:
            <input type="email" name="email" required>
        </label><br>
        <label>Phone*:
            <input type="text" name="phone" required>
        </label><br>
        <button type="submit">Add Alumni</button>
    </form>

    <?php if ($searchTerm): ?>
        <h2>Search Results for "<?= htmlspecialchars($searchTerm) ?>"</h2>
        <?php if ($alumniList): ?>
            <table>
                <tr>
                    <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Phone</th><th>Action</th>
                </tr>
                <?php foreach ($alumniList as $alumni): ?>
                    <tr>
                        <td><?= $alumni['alumniID'] ?></td>
                        <td><?= htmlspecialchars($alumni['fName']) ?></td>
                        <td><?= htmlspecialchars($alumni['lName']) ?></td>
                        <td><?= htmlspecialchars($alumni['email']) ?></td>
                        <td><?= htmlspecialchars($alumni['phone']) ?></td>
                        <td><a href="?deleteID=<?= $alumni['alumniID'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No matching alumni found.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>
</body>
</html>
