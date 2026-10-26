<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'dashboard';

$totalProjects = $pdo->query("SELECT COUNT(*) c FROM projects")->fetch()['c'];
$totalSkills = $pdo->query("SELECT COUNT(*) c FROM skills")->fetch()['c'];
$totalCerts = $pdo->query("SELECT COUNT(*) c FROM certificates")->fetch()['c'];
$totalMessages = $pdo->query("SELECT COUNT(*) c FROM messages")->fetch()['c'];
$totalResearch = $pdo->query("SELECT COUNT(*) c FROM research_papers")->fetch()['c'];
$totalPrivateNotes = $pdo->query("SELECT COUNT(*) c FROM private_notes")->fetch()['c'];
$unreadMessages = $pdo->query("SELECT COUNT(*) c FROM messages WHERE is_read = 0")->fetch()['c'];
$settings = get_settings($pdo);
$downloads = (int)($settings['resume_downloads'] ?? 0);

$latestMessages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>

<div class="section-tag">Mission Control</div>
<h2 class="section-title" style="margin-top:6px;">Welcome back, <?= e($_SESSION['admin_username']) ?></h2>

<div class="grid-4" style="margin-top:28px;">
  <div class="glass stat-card"><div class="val"><?= $totalProjects ?></div><div class="lbl">Total Projects</div></div>
  <div class="glass stat-card"><div class="val"><?= $totalSkills ?></div><div class="lbl">Total Skills</div></div>
  <div class="glass stat-card"><div class="val"><?= $totalCerts ?></div><div class="lbl">Total Certificates</div></div>
  <div class="glass stat-card"><div class="val"><?= $totalMessages ?></div><div class="lbl">Total Messages</div></div>
  <div class="glass stat-card"><div class="val"><?= $totalResearch ?></div><div class="lbl">Research Papers</div></div>
  <div class="glass stat-card"><div class="val"><?= $totalPrivateNotes ?></div><div class="lbl">Locked Notes</div></div>
</div>
<div class="grid-4" style="margin-top:16px;">
  <div class="glass stat-card"><div class="val" style="color:var(--danger)"><?= $unreadMessages ?></div><div class="lbl">Unread Messages</div></div>
  <div class="glass stat-card"><div class="val"><?= $downloads ?></div><div class="lbl">Resume Downloads</div></div>
</div>

<div class="glass panel" style="margin-top:28px;">
  <h3 style="margin-bottom:16px; font-size:16px;">Latest Contacts</h3>
  <table>
    <tr><th>Name</th><th>Subject</th><th>Date</th><th>Status</th></tr>
    <?php if (!$latestMessages): ?><tr><td colspan="4" style="color:var(--text-dim);">No messages yet.</td></tr><?php endif; ?>
    <?php foreach ($latestMessages as $m): ?>
      <tr>
        <td><?= e($m['name']) ?></td>
        <td><?= e($m['subject']) ?></td>
        <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
        <td><span class="badge <?= $m['is_read'] ? 'badge-read' : 'badge-unread' ?>"><?= $m['is_read'] ? 'Read' : 'Unread' ?></span></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <a href="messages.php" class="btn btn-outline btn-sm" style="margin-top:16px;">View All Messages →</a>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
