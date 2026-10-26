<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'certificates';
$notice = ''; $error = '';
$UPLOAD_BASE = __DIR__ . '/../uploads/certificates';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM certificates WHERE id = ?")->execute([(int)$_GET['delete']]);
    $notice = 'Certificate deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $issuer = trim($_POST['issuer'] ?? '');
    $issueDate = $_POST['issue_date'] ?: null;

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $uploadError = null;
        $image = handle_upload('image', $UPLOAD_BASE, ['jpg','jpeg','png','webp'], 5 * 1024 * 1024, $uploadError);
        if ($uploadError) $error = $uploadError;
        $pdf = handle_upload('pdf', $UPLOAD_BASE, ['pdf'], 10 * 1024 * 1024, $uploadError);
        if ($uploadError) $error = $uploadError;

        if (!$error) {
            if ($id) {
                $sql = "UPDATE certificates SET title=?, issuer=?, issue_date=?" . ($image ? ", image_path=?" : "") . ($pdf ? ", pdf_path=?" : "") . " WHERE id=?";
                $params = [$title, $issuer, $issueDate];
                if ($image) $params[] = $image;
                if ($pdf) $params[] = $pdf;
                $params[] = $id;
                $pdo->prepare($sql)->execute($params);
            } else {
                $pdo->prepare("INSERT INTO certificates (title, issuer, issue_date, image_path, pdf_path) VALUES (?,?,?,?,?)")
                    ->execute([$title, $issuer, $issueDate, $image, $pdf]);
            }
            $notice = 'Certificate saved.';
        }
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$rows = $pdo->query("SELECT * FROM certificates ORDER BY sort_order ASC, id DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Certifications Center</div>
<h2 class="section-title" style="margin-top:6px;">Manage Certificates</h2>
<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-top:16px;"><?= e($error) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <form method="POST" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= $editItem['id'] ?? '' ?>">
    <div class="grid-2">
      <div class="form-group"><label>Title</label><input class="form-control" name="title" required value="<?= e($editItem['title'] ?? '') ?>"></div>
      <div class="form-group"><label>Issuer</label><input class="form-control" name="issuer" value="<?= e($editItem['issuer'] ?? '') ?>"></div>
    </div>
    <div class="grid-2">
      <div class="form-group"><label>Issue Date</label><input class="form-control" type="date" name="issue_date" value="<?= e($editItem['issue_date'] ?? '') ?>"></div>
      <div class="form-group"><label>Certificate Image</label><input class="form-control" type="file" name="image" accept="image/*"></div>
    </div>
    <div class="form-group"><label>Certificate PDF</label><input class="form-control" type="file" name="pdf" accept="application/pdf"></div>
    <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update' : 'Add' ?> Certificate</button>
    <?php if ($editItem): ?><a href="certificates.php" class="btn btn-outline">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="glass panel" style="margin-top:24px;">
  <table>
    <tr><th>Title</th><th>Issuer</th><th>Date</th><th>Actions</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['issuer']) ?></td>
        <td><?= $r['issue_date'] ? date('M Y', strtotime($r['issue_date'])) : '—' ?></td>
        <td class="row-actions">
          <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
          <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this certificate?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
