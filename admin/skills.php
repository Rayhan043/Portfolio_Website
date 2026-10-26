<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'skills';
$notice = '';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM skills WHERE id = ?")->execute([(int)$_GET['delete']]);
    $notice = 'Skill deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $proficiency = max(0, min(100, (int)($_POST['proficiency'] ?? 0)));

    if ($name !== '' && $category !== '') {
        if ($id) {
            $pdo->prepare("UPDATE skills SET name=?, category=?, proficiency=? WHERE id=?")->execute([$name, $category, $proficiency, $id]);
        } else {
            $pdo->prepare("INSERT INTO skills (name, category, proficiency) VALUES (?,?,?)")->execute([$name, $category, $proficiency]);
        }
        $notice = 'Skill saved.';
    }
}

$editSkill = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editSkill = $stmt->fetch();
}

$skills = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC, id ASC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Skills Matrix</div>
<h2 class="section-title" style="margin-top:6px;">Manage Skills</h2>
<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <form method="POST">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= $editSkill['id'] ?? '' ?>">
    <div class="grid-3">
      <div class="form-group"><label>Name</label><input class="form-control" name="name" required value="<?= e($editSkill['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Category</label><input class="form-control" name="category" required value="<?= e($editSkill['category'] ?? '') ?>"></div>
      <div class="form-group"><label>Proficiency (0-100)</label><input class="form-control" type="number" min="0" max="100" name="proficiency" required value="<?= e($editSkill['proficiency'] ?? 70) ?>"></div>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editSkill ? 'Update' : 'Add' ?> Skill</button>
    <?php if ($editSkill): ?><a href="skills.php" class="btn btn-outline">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="glass panel" style="margin-top:24px;">
  <table>
    <tr><th>Name</th><th>Category</th><th>Proficiency</th><th>Actions</th></tr>
    <?php foreach ($skills as $s): ?>
      <tr>
        <td><?= e($s['name']) ?></td>
        <td><?= e($s['category']) ?></td>
        <td><?= (int)$s['proficiency'] ?>%</td>
        <td class="row-actions">
          <a href="?edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
          <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this skill?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
