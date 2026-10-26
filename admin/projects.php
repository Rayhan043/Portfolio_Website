<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'projects';

$notice = '';
$error = '';
$UPLOAD_BASE = __DIR__ . '/../uploads/projects';

// Helper function to clean file path for Database
function clean_db_path($path) {
    if (!$path) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    // Strip redundant uploads/ or projects/ prefixes
    $path = str_replace(['uploads/projects/', 'uploads/', 'projects/'], '', $path);
    return ltrim($path, '/');
}

// Helper function to get image src for HTML preview
function get_preview_src($path) {
    if (!$path) return 'assets/img/placeholder.svg';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    $clean = clean_db_path($path);
    return '../uploads/projects/' . $clean;
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (($_GET['t'] ?? '') === substr(csrf_token(), 0, 12)) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Project deleted.';
    }
}

// ---------- CREATE / UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $github = trim($_POST['github_link'] ?? '');
    $demo = trim($_POST['demo_link'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $status = $_POST['status'] === 'archived' ? 'archived' : 'active';

    if ($title === '' || $description === '' || $technologies === '') {
        $error = 'Title, description, and technologies are required.';
    } else {
        $uploadError = null;
        $rawThumb = handle_upload('thumbnail', $UPLOAD_BASE, ['jpg','jpeg','png','webp'], 5 * 1024 * 1024, $uploadError);
        if ($uploadError) $error = $uploadError;

        $rawVideo = handle_upload('video', $UPLOAD_BASE, ['mp4'], 50 * 1024 * 1024, $uploadError);
        if ($uploadError) $error = $uploadError;

        $thumb = clean_db_path($rawThumb);
        $video = clean_db_path($rawVideo);
        
        if (!$video && !empty($video_url)) {
            $video = $video_url;
        }

        if (!$error) {
            if ($id) {
                $sql = "UPDATE projects SET title=?, description=?, technologies=?, github_link=?, demo_link=?, status=?" .
                       ($thumb ? ", thumbnail=?" : "") . ($video ? ", video_path=?" : "") . " WHERE id=?";
                $params = [$title, $description, $technologies, $github, $demo, $status];
                if ($thumb) $params[] = $thumb;
                if ($video) $params[] = $video;
                $params[] = $id;
                $pdo->prepare($sql)->execute($params);
            } else {
                $stmt = $pdo->prepare("INSERT INTO projects (title, description, technologies, github_link, demo_link, thumbnail, video_path, status) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$title, $description, $technologies, $github, $demo, $thumb, $video, $status]);
                $id = $pdo->lastInsertId();
            }

            // Gallery Upload
            if (!empty($_FILES['gallery']['name'][0])) {
                foreach ($_FILES['gallery']['name'] as $i => $name) {
                    if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $tmp = ['name' => $name, 'type' => $_FILES['gallery']['type'][$i], 'tmp_name' => $_FILES['gallery']['tmp_name'][$i], 'error' => $_FILES['gallery']['error'][$i], 'size' => $_FILES['gallery']['size'][$i]];
                    $_FILES['__single'] = $tmp;
                    $galErr = null;
                    $rawGal = handle_upload('__single', $UPLOAD_BASE, ['jpg','jpeg','png','webp'], 5 * 1024 * 1024, $galErr);
                    $path = clean_db_path($rawGal);
                    if ($path) {
                        $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?, ?)")->execute([$id, $path]);
                    }
                }
            }
            $notice = 'Project saved.';
        }
    }
}

$editProject = null;
$editImages = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProject = $stmt->fetch();
    if ($editProject) {
        $stmt2 = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ?");
        $stmt2->execute([$editProject['id']]);
        $editImages = $stmt2->fetchAll();
    }
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>

<div class="section-tag">Project Vault</div>
<h2 class="section-title" style="margin-top:6px;">Manage Projects</h2>

<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-top:16px;"><?= e($error) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <h3 style="margin-bottom:16px; font-size:15px;"><?= $editProject ? 'Edit Project' : 'Add New Project' ?></h3>
  <form method="POST" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= $editProject['id'] ?? '' ?>">
    <div class="grid-2">
      <div class="form-group"><label>Title</label><input class="form-control" name="title" required value="<?= e($editProject['title'] ?? '') ?>"></div>
      <div class="form-group"><label>Technologies (comma separated)</label><input class="form-control" name="technologies" required value="<?= e($editProject['technologies'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Description</label><textarea class="form-control" name="description" required><?= e($editProject['description'] ?? '') ?></textarea></div>
    <div class="grid-2">
      <div class="form-group"><label>GitHub Link</label><input class="form-control" name="github_link" value="<?= e($editProject['github_link'] ?? '') ?>"></div>
      <div class="form-group">
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
          <input type="checkbox" id="demo_toggle" <?= !empty($editProject['demo_link']) ? 'checked' : '' ?> style="width:18px; height:18px; cursor:pointer;">
          <span>Enable Live Demo</span>
        </label>
        <input class="form-control" id="demo_link_input" type="url" name="demo_link" placeholder="https://example.com/demo" value="<?= e($editProject['demo_link'] ?? '') ?>" style="display:<?= !empty($editProject['demo_link']) ? 'block' : 'none' ?>">
      </div>
    </div>
    <script>
      document.getElementById('demo_toggle').addEventListener('change', function() {
        const input = document.getElementById('demo_link_input');
        if (this.checked) {
          input.style.display = 'block';
          input.required = false;
        } else {
          input.style.display = 'none';
          input.value = '';
        }
      });
    </script>
    <div class="grid-2">
      <div class="form-group">
        <label>Thumbnail Image <?= $editProject['thumbnail'] ?? '' ? '(leave blank to keep current)' : '' ?></label>
        <input class="form-control" type="file" name="thumbnail" accept="image/*">
        <?php if ($editProject && $editProject['thumbnail']): ?>
          <img src="<?= e(get_preview_src($editProject['thumbnail'])) ?>" style="width:100px; height:100px; object-fit:cover; border-radius:8px; margin-top:8px;">
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Demo Video URL (Cloudinary Link)</label>
        <input class="form-control" type="url" name="video_url" placeholder="https://res.cloudinary.com/.../video.mp4" value="<?= e((isset($editProject['video_path']) && str_starts_with($editProject['video_path'], 'http')) ? $editProject['video_path'] : '') ?>" style="margin-bottom:8px;">
        <label style="font-size:12px; color:var(--text-dim);">Or Upload Video File (Max 50MB)</label>
        <input class="form-control" type="file" name="video" accept="video/mp4">
      </div>
    </div>
    <div class="form-group"><label>Additional Gallery Images (multiple)</label><input class="form-control" type="file" name="gallery[]" accept="image/*" multiple></div>
    <div class="form-group">
      <label>Status</label>
      <select class="form-control" name="status">
        <option value="active" <?= ($editProject['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="archived" <?= ($editProject['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
      </select>
    </div>
    <?php if ($editImages): ?>
      <div class="form-group"><label>Current Gallery</label>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <?php foreach ($editImages as $img): ?>
            <img src="<?= e(get_preview_src($img['image_path'])) ?>" style="width:70px; height:70px; object-fit:cover; border-radius:8px;">
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary"><?= $editProject ? 'Update Project' : 'Create Project' ?></button>
    <?php if ($editProject): ?><a href="projects.php" class="btn btn-outline">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="glass panel" style="margin-top:24px;">
  <table>
    <tr><th>Title</th><th>Technologies</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($projects as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><?= e(mb_strimwidth($p['technologies'], 0, 40, '…')) ?></td>
        <td><span class="badge <?= $p['status'] === 'active' ? 'badge-read' : 'badge-unread' ?>"><?= e($p['status']) ?></span></td>
        <td class="row-actions">
          <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
          <a href="?delete=<?= $p['id'] ?>&t=<?= substr(csrf_token(), 0, 12) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>