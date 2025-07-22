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

// Handle deleting a degree record
if (isset($_GET['deleteID']) && $alumniID > 0) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = $pdo->prepare("DELETE FROM degree WHERE degreeID = ? AND alumniID = ?");
    if ($stmt->execute([$deleteID, $alumniID])) {
        $successMessage = "Degree record deleted.";
    } else {
        $errorMessage = "Failed to delete degree record.";
    }
}

// Handle adding degree
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alumniIDPost = intval($_POST['alumniID']);
    $major = trim($_POST['major']);
    $minor = trim($_POST['minor']);
    $graduationDT = $_POST['graduationDT'];
    $university = trim($_POST['university']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);

    if ($alumniIDPost > 0 && $major && $graduationDT && $university) {
        $sql = "INSERT INTO degree (alumniID, major, minor, graduationDT, university, city, state)
                VALUES (:alumniID, :major, :minor, :graduationDT, :university, :city, :state)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':alumniID' => $alumniIDPost,
                ':major' => $major,
                ':minor' => $minor,
                ':graduationDT' => $graduationDT,
                ':university' => $university,
                ':city' => $city,
                ':state' => $state
            ]);
            $successMessage = "Degree added successfully!";
            $alumniID = $alumniIDPost;
        } catch (PDOException $e) {
            $errorMessage = "Failed to add degree: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill required fields: Alumni, Major, Graduation Date, University.";
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

// Fetch degrees
$degrees = [];
$alumniName = "";
if ($alumniID > 0) {
    $stmt = $pdo->prepare("SELECT fName, lName FROM alumni WHERE alumniID = ?");
    $stmt->execute([$alumniID]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($alumni) {
        $alumniName = $alumni['fName'] . ' ' . $alumni['lName'];
    }
    $stmt = $pdo->prepare("SELECT * FROM degree WHERE alumniID = ? ORDER BY graduationDT DESC");
    $stmt->execute([$alumniID]);
    $degrees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Degree Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Degree Management</h1>

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
        <h2>Degrees for <?= htmlspecialchars($alumniName) ?></h2>

        <?php if ($successMessage): ?>
            <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <?php if ($degrees): ?>
            <table>
                <tr>
                    <th>Major</th><th>Minor</th><th>Graduation Date</th><th>University</th>
                    <th>City</th><th>State</th><th>Actions</th>
                </tr>
                <?php foreach ($degrees as $degree): ?>
                    <tr>
                        <td><?= htmlspecialchars($degree['major']) ?></td>
                        <td><?= htmlspecialchars($degree['minor']) ?></td>
                        <td><?= htmlspecialchars($degree['graduationDT']) ?></td>
                        <td><?= htmlspecialchars($degree['university']) ?></td>
                        <td><?= htmlspecialchars($degree['city']) ?></td>
                        <td><?= htmlspecialchars($degree['state']) ?></td>
                        <td>
                            <a href="?alumniID=<?= $alumniID ?>&deleteID=<?= $degree['degreeID'] ?>"
                               onclick="return confirm('Are you sure you want to delete this degree record?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No degree records found.</p>
        <?php endif; ?>

        <h3>Add New Degree</h3>
        <form method="POST" action="">
            <input type="hidden" name="alumniID" value="<?= $alumniID ?>">
            <label>Major*:
                <input type="text" name="major" required>
            </label><br>

            <label>Minor:
                <input type="text" name="minor">
            </label><br>

            <label>Graduation Date*:
                <input type="date" name="graduationDT" required>
            </label><br>

            <label>University*:
                <input type="text" name="university" required>
            </label><br>

            <label>City:
                <input type="text" name="city">
            </label><br>

            <label>State:
                <input type="text" name="state" maxlength="2">
            </label><br>

            <button type="submit">Add Degree</button>
        </form>
    <?php endif; ?>

    <p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>
</body>
</html>

