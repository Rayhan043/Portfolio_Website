<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'experience';
$notice = '';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM experience WHERE id = ?")->execute([(int)$_GET['delete']]);
    $notice = 'Entry deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $org = trim($_POST['organization'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $start = $_POST['start_date'] ?? '';
    $end = trim($_POST['end_date'] ?? '') ?: null;

    if ($title !== '' && $org !== '' && $start !== '') {
        if ($id) {
            $pdo->prepare("UPDATE experience SET title=?, organization=?, description=?, start_date=?, end_date=? WHERE id=?")
                ->execute([$title, $org, $desc, $start, $end, $id]);
        } else {
            $pdo->prepare("INSERT INTO experience (title, organization, description, start_date, end_date) VALUES (?,?,?,?,?)")
                ->execute([$title, $org, $desc, $start, $end]);
        }
        $notice = 'Experience saved.';
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM experience WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$rows = $pdo->query("SELECT * FROM experience ORDER BY start_date DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Experience Timeline</div>
<h2 class="section-title" style="margin-top:6px;">Manage Experience</h2>
<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <form method="POST">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= $editItem['id'] ?? '' ?>">
    <div class="grid-2">
      <div class="form-group"><label>Title / Role</label><input class="form-control" name="title" required value="<?= e($editItem['title'] ?? '') ?>"></div>
      <div class="form-group"><label>Organization</label><input class="form-control" name="organization" required value="<?= e($editItem['organization'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Description</label><textarea class="form-control" name="description"><?= e($editItem['description'] ?? '') ?></textarea></div>
    <div class="grid-2">
      <div class="form-group"><label>Start Date</label><input class="form-control" type="date" name="start_date" required value="<?= e($editItem['start_date'] ?? '') ?>"></div>
      <div class="form-group"><label>End Date (leave blank if current)</label><input class="form-control" type="date" name="end_date" value="<?= e($editItem['end_date'] ?? '') ?>"></div>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update' : 'Add' ?> Entry</button>
    <?php if ($editItem): ?><a href="experience.php" class="btn btn-outline">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="glass panel" style="margin-top:24px;">
  <table>
    <tr><th>Title</th><th>Organization</th><th>Period</th><th>Actions</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['organization']) ?></td>
        <td><?= format_date_range($r['start_date'], $r['end_date']) ?></td>
        <td class="row-actions">
          <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
          <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
