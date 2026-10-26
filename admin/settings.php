<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'settings';
$notice = '';

$fields = ['full_name','tagline','about_me','current_learning_path','career_goal','github_url','linkedin_url','email','phone','location','twitter_url'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach ($fields as $f) {
        set_setting($pdo, $f, trim($_POST[$f] ?? ''));
    }
    $uploadError = null;
    $newImage = handle_upload('profile_image', __DIR__ . '/../uploads/profile', ['jpg','jpeg','png','webp'], 5 * 1024 * 1024, $uploadError);
    if ($uploadError) {
        $notice = $uploadError;
    } elseif ($newImage) {
        $oldImageStmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key='profile_image' LIMIT 1");
        $oldImage = $oldImageStmt->fetchColumn() ?: '';
        set_setting($pdo, 'profile_image', $newImage);
        if ($oldImage && strpos($oldImage, 'uploads/profile/') === 0) {
            $oldPath = __DIR__ . '/../' . $oldImage;
            if (is_file($oldPath)) @unlink($oldPath);
        }
        $notice = 'Site information and profile photo updated.';
    } elseif (!empty($_POST['remove_profile_image'])) {
        $oldImageStmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key='profile_image' LIMIT 1");
        $oldImage = $oldImageStmt->fetchColumn() ?: '';
        if ($oldImage && strpos($oldImage, 'uploads/profile/') === 0) {
            $oldPath = __DIR__ . '/../' . $oldImage;
            if (is_file($oldPath)) @unlink($oldPath);
        }
        set_setting($pdo, 'profile_image', '');
        $notice = 'Profile photo removed.';
    } else {
        $notice = 'Site information updated.';
    }
}

$settings = get_settings($pdo);
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Site Info</div>
<h2 class="section-title" style="margin-top:6px;">Dashboard &amp; Contact Hub Content</h2>
<?php if ($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?= e($notice) ?></div><?php endif; ?>

<div class="glass panel" style="margin-top:20px;">
  <form method="POST" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <div class="grid-2">
      <div class="form-group"><label>Full Name</label><input class="form-control" name="full_name" value="<?= e($settings['full_name'] ?? '') ?>"></div>
      <div class="form-group"><label>Tagline / Role</label><input class="form-control" name="tagline" value="<?= e($settings['tagline'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>About Me</label><textarea class="form-control" name="about_me"><?= e($settings['about_me'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Current Learning Path</label><input class="form-control" name="current_learning_path" value="<?= e($settings['current_learning_path'] ?? '') ?>"></div>
    <div class="form-group"><label>Career Goal</label><input class="form-control" name="career_goal" value="<?= e($settings['career_goal'] ?? '') ?>"></div>
    <div class="grid-2">
      <div class="form-group"><label>GitHub URL</label><input class="form-control" name="github_url" value="<?= e($settings['github_url'] ?? '') ?>"></div>
      <div class="form-group"><label>LinkedIn URL</label><input class="form-control" name="linkedin_url" value="<?= e($settings['linkedin_url'] ?? '') ?>"></div>
    </div>
    <div class="grid-2">
      <div class="form-group"><label>Email</label><input class="form-control" name="email" value="<?= e($settings['email'] ?? '') ?>"></div>
      <div class="form-group"><label>Phone</label><input class="form-control" name="phone" value="<?= e($settings['phone'] ?? '') ?>"></div>
    </div>
    <div class="grid-2">
      <div class="form-group"><label>Location</label><input class="form-control" name="location" value="<?= e($settings['location'] ?? '') ?>"></div>
      <div class="form-group"><label>Twitter/X URL (optional)</label><input class="form-control" name="twitter_url" value="<?= e($settings['twitter_url'] ?? '') ?>"></div>
    </div>

    <div class="form-group">
      <label>Profile Photo (JPG, PNG or WEBP, max 5MB)</label>
      <?php if (!empty($settings['profile_image'])): ?>
        <div style="margin:12px 0;"><img src="../<?= e($settings['profile_image']) ?>" alt="Current profile photo" style="width:112px;height:112px;object-fit:cover;border-radius:50%;border:3px solid var(--primary);box-shadow:0 0 24px rgba(0,245,255,.22);"></div>
      <?php endif; ?>
      <input class="form-control" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <label style="display:flex;align-items:center;gap:8px;margin-top:10px;font-weight:normal;"><input type="checkbox" name="remove_profile_image" value="1"> Remove current profile photo</label>
      <small style="color:var(--text-dim);display:block;margin-top:6px;">Use a clear, well-lit square portrait. The site crops it neatly without stretching.</small>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
