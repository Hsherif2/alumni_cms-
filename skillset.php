<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$successMessage = "";
$errorMessage = "";

// Add skill
if (isset($_POST['add_skill'])) {
    $alumniID = intval($_POST['alumniID']);
    $skill = trim($_POST['skill']);
    $proficiency = trim($_POST['proficiency']);
    $description = trim($_POST['description']);

    if ($alumniID > 0 && $skill && $proficiency) {
        $stmt = $pdo->prepare("INSERT INTO skillset (alumniID, skill, proficiency, description) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$alumniID, $skill, $proficiency, $description]);
            $successMessage = "Skill added successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Failed to add skill: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill out all required fields.";
    }
}

// Update skill
if (isset($_POST['update_skill'])) {
    $SID = intval($_POST['SID']);
    $alumniID = intval($_POST['alumniID']);
    $skill = trim($_POST['skill']);
    $proficiency = trim($_POST['proficiency']);
    $description = trim($_POST['description']);

    if ($SID > 0 && $alumniID > 0 && $skill && $proficiency) {
        $stmt = $pdo->prepare("UPDATE skillset SET alumniID=?, skill=?, proficiency=?, description=? WHERE SID=?");
        try {
            $stmt->execute([$alumniID, $skill, $proficiency, $description, $SID]);
            $successMessage = "Skill updated successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Failed to update skill: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill out all required fields.";
    }
}

// Delete skill
if (isset($_GET['delete'])) {
    $SID = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM skillset WHERE SID = ?");
    if ($stmt->execute([$SID])) {
        $successMessage = "Skill deleted successfully.";
    } else {
        $errorMessage = "Failed to delete skill.";
    }
}

// Fetch all skills
$stmt = $pdo->query("SELECT * FROM skillset ORDER BY SID");
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Edit skill
$editSkill = null;
if (isset($_GET['edit'])) {
    $SID = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM skillset WHERE SID = ?");
    $stmt->execute([$SID]);
    $editSkill = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Skillset Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Skillset Management</h1>

<?php if ($successMessage): ?>
    <p style="color:green; max-width: 500px; margin: 10px auto;"><?= htmlspecialchars($successMessage) ?></p>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <p style="color:red; max-width: 500px; margin: 10px auto;"><?= htmlspecialchars($errorMessage) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>SID</th>
            <th>Alumni ID</th>
            <th>Skill</th>
            <th>Proficiency</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($skills as $skill): ?>
            <tr>
                <td><?= htmlspecialchars($skill['SID']) ?></td>
                <td><?= htmlspecialchars($skill['alumniID']) ?></td>
                <td><?= htmlspecialchars($skill['skill']) ?></td>
                <td><?= htmlspecialchars($skill['proficiency']) ?></td>
                <td><?= htmlspecialchars($skill['description'] ?? '') ?></td>
                <td>
                    <a href="?edit=<?= $skill['SID'] ?>">Edit</a> |
                    <a href="?delete=<?= $skill['SID'] ?>" onclick="return confirm('Delete this skill?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2><?= $editSkill ? "Edit Skill (SID: {$editSkill['SID']})" : "Add New Skill" ?></h2>

<form method="post" action="skillset.php" style="max-width: 500px; margin: 20px auto;">
    <input type="hidden" name="SID" value="<?= $editSkill ? htmlspecialchars($editSkill['SID']) : '' ?>"/>

    <label>Alumni ID:<br>
        <input type="number" name="alumniID" required value="<?= $editSkill ? htmlspecialchars($editSkill['alumniID']) : '' ?>" />
    </label><br>

    <label>Skill:<br>
        <input type="text" name="skill" required value="<?= $editSkill ? htmlspecialchars($editSkill['skill']) : '' ?>" />
    </label><br>

    <label>Proficiency:<br>
        <select name="proficiency" required>
            <?php
            $levels = ['Basic', 'Intermed', 'Adv'];
            foreach ($levels as $level) {
                $selected = ($editSkill && $editSkill['proficiency'] === $level) ? "selected" : "";
                echo "<option value='$level' $selected>$level</option>";
            }
            ?>
        </select>
    </label><br>

    <label>Description:<br>
        <textarea name="description"><?= $editSkill ? htmlspecialchars($editSkill['description']) : '' ?></textarea>
    </label><br>

    <button type="submit" name="<?= $editSkill ? 'update_skill' : 'add_skill' ?>">
        <?= $editSkill ? 'Update Skill' : 'Add Skill' ?>
    </button>
</form>

<p><a class="ksu-back-link" href="index.php">← Back to Home</a></p>

</body>
</html>


