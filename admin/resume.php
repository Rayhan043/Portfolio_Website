<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'resume';
$notice = ''; $error = '';
$UPLOAD_BASE = __DIR__ . '/../uploads/resume';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $uploadError = null;
    $path = handle_upload('resume_file', $UPLOAD_BASE, ['pdf'], 10 * 1024 * 1024, $uploadError);
    if ($uploadError) {
        $error = $uploadError;
    } elseif ($path) {
        $pdo->prepare("INSERT INTO resume (file_path) VALUES (?)")->execute([$path]);
        $notice = 'Resume uploaded successfully.';
    } else {
        $error = 'Please choose a PDF file to upload.';
    }
}

$resumes = $pdo->query("SELECT * FROM resume ORDER BY id DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Resume Center</div>
<h2 class="section-title" style="margin-top:6px;">Manage Resume</h2>
<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-top:16px;"><?= e($error) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <form method="POST" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <div class="form-group"><label>Upload New Resume (PDF, max 10MB)</label><input class="form-control" type="file" name="resume_file" accept="application/pdf" required></div>
    <button type="submit" class="btn btn-primary">Upload Resume</button>
  </form>
</div>

<div class="glass panel" style="margin-top:24px;">
  <h3 style="margin-bottom:14px; font-size:15px;">Upload History (latest is shown live on site)</h3>
  <table>
    <tr><th>File</th><th>Uploaded</th></tr>
    <?php if (!$resumes): ?><tr><td colspan="2" style="color:var(--text-dim);">No resume uploaded yet.</td></tr><?php endif; ?>
    <?php foreach ($resumes as $i => $r): ?>
      <tr>
        <td><a href="../<?= e($r['file_path']) ?>" target="_blank"><?= e(basename($r['file_path'])) ?></a> <?= $i === 0 ? '<span class="badge badge-read">Live</span>' : '' ?></td>
        <td><?= date('d M Y, H:i', strtotime($r['uploaded_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
