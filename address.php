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

// Handle deleting an address
if (isset($_GET['deleteID']) && $alumniID > 0) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = $pdo->prepare("DELETE FROM address WHERE addressID = ? AND alumniID = ?");
    if ($stmt->execute([$deleteID, $alumniID])) {
        $successMessage = "Address deleted successfully.";
    } else {
        $errorMessage = "Failed to delete address.";
    }
}

// Handle adding address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumniIDPost = intval($_POST['alumniID']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipCode = trim($_POST['zip']);
    $primaryYN = strtoupper(trim($_POST['primaryYN']));
    $activeYN = strtoupper(trim($_POST['activeYN']));

    if ($alumniIDPost > 0 && $address) {
        if ($primaryYN === 'Y') {
            $pdo->prepare("UPDATE address SET primaryYN = 'N' WHERE alumniID = ?")->execute([$alumniIDPost]);
        }

        $stmt = $pdo->prepare("INSERT INTO address (alumniID, address, city, state, zipCode, primaryYN, activeYN)
                               VALUES (:alumniID, :address, :city, :state, :zipCode, :primaryYN, :activeYN)");
        try {
            $stmt->execute([
                ':alumniID' => $alumniIDPost,
                ':address' => $address,
                ':city' => $city,
                ':state' => $state,
                ':zipCode' => $zipCode,
                ':primaryYN' => $primaryYN,
                ':activeYN' => $activeYN
            ]);
            $successMessage = "Address added successfully.";
            $alumniID = $alumniIDPost;
        } catch (PDOException $e) {
            $errorMessage = "Failed to add address: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill out all required fields.";
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

// Get alumni name and addresses
$addresses = [];
$alumniName = "";
if ($alumniID > 0) {
    $stmt = $pdo->prepare("SELECT fName, lName FROM alumni WHERE alumniID = ?");
    $stmt->execute([$alumniID]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($alumni) {
        $alumniName = $alumni['fName'] . ' ' . $alumni['lName'];
    }

    $stmt = $pdo->prepare("SELECT * FROM address WHERE alumniID = ? ORDER BY addressID DESC");
    $stmt->execute([$alumniID]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alumni Address Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Alumni Address Management</h1>

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
    <h2>Addresses for <?= htmlspecialchars($alumniName) ?></h2>

    <?php if ($successMessage): ?>
        <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <?php if ($addresses): ?>
        <table>
            <tr>
                <th>Address</th><th>City</th><th>State</th><th>ZIP</th>
                <th>Primary</th><th>Active</th><th>Actions</th>
            </tr>
            <?php foreach ($addresses as $addr): ?>
                <tr>
                    <td><?= htmlspecialchars($addr['address'] ?? '') ?></td>
                    <td><?= htmlspecialchars($addr['city'] ?? '') ?></td>
                    <td><?= htmlspecialchars($addr['state'] ?? '') ?></td>
                    <td><?= htmlspecialchars($addr['zipCode'] ?? '') ?></td>
                    <td><?= htmlspecialchars($addr['primaryYN'] ?? '') ?></td>
                    <td><?= htmlspecialchars($addr['activeYN'] ?? '') ?></td>
                    <td>
                        <a href="?alumniID=<?= $alumniID ?>&deleteID=<?= $addr['addressID'] ?>"
                           onclick="return confirm('Are you sure you want to delete this address?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No addresses found.</p>
    <?php endif; ?>

    <h3>Add New Address</h3>
    <form method="POST" action="">
        <input type="hidden" name="alumniID" value="<?= $alumniID ?>">

        <label>Street Address*:
            <input type="text" name="address" required>
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

        <label>Primary Address (Y/N)*:
            <input type="text" name="primaryYN" maxlength="1" pattern="[YNyn]" required>
        </label><br>

        <label>Active Address (Y/N)*:
            <input type="text" name="activeYN" maxlength="1" pattern="[YNyn]" required>
        </label><br>

        <button type="submit">Add Address</button>
    </form>
<?php endif; ?>

<p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>

</body>
</html>

