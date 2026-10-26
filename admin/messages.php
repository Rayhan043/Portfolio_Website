<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'messages';

if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([(int)$_GET['read']]);
}
if (isset($_GET['unread'])) {
    $pdo->prepare("UPDATE messages SET is_read = 0 WHERE id = ?")->execute([(int)$_GET['unread']]);
}
if (isset($_GET['archive'])) {
    $pdo->prepare("UPDATE messages SET is_archived = 1 WHERE id = ?")->execute([(int)$_GET['archive']]);
}
if (isset($_GET['unarchive'])) {
    $pdo->prepare("UPDATE messages SET is_archived = 0 WHERE id = ?")->execute([(int)$_GET['unarchive']]);
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([(int)$_GET['delete']]);
}

$filter = $_GET['filter'] ?? 'inbox';
if ($filter === 'archived') {
    $rows = $pdo->query("SELECT * FROM messages WHERE is_archived = 1 ORDER BY created_at DESC")->fetchAll();
} else {
    $rows = $pdo->query("SELECT * FROM messages WHERE is_archived = 0 ORDER BY created_at DESC")->fetchAll();
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Message Dashboard</div>
<h2 class="section-title" style="margin-top:6px;">Contact Messages</h2>

<div style="margin:18px 0; display:flex; gap:10px;">
  <a href="?filter=inbox" class="btn btn-sm <?= $filter !== 'archived' ? 'btn-primary' : 'btn-outline' ?>">Inbox</a>
  <a href="?filter=archived" class="btn btn-sm <?= $filter === 'archived' ? 'btn-primary' : 'btn-outline' ?>">Archived</a>
</div>

<div class="glass panel">
  <table>
    <tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr>
    <?php if (!$rows): ?><tr><td colspan="6" style="color:var(--text-dim);">No messages here.</td></tr><?php endif; ?>
    <?php foreach ($rows as $m): ?>
      <tr>
        <td><?= e($m['name']) ?><?= $m['company'] ? '<br><span style="color:var(--text-dim); font-size:11px;">' . e($m['company']) . '</span>' : '' ?></td>
        <td><?= e($m['email']) ?></td>
        <td>
          <?= e($m['subject']) ?>
          <div style="color:var(--text-dim); font-size:12px; margin-top:4px; max-width:280px;"><?= e(mb_strimwidth($m['message'], 0, 90, '…')) ?></div>
        </td>
        <td><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
        <td><span class="badge <?= $m['is_read'] ? 'badge-read' : 'badge-unread' ?>"><?= $m['is_read'] ? 'Read' : 'Unread' ?></span></td>
        <td class="row-actions">
          <?php if (!$m['is_read']): ?><a href="?read=<?= $m['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-outline">Mark Read</a>
          <?php else: ?><a href="?unread=<?= $m['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-outline">Mark Unread</a><?php endif; ?>
          <?php if ($filter === 'archived'): ?>
            <a href="?unarchive=<?= $m['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-outline">Restore</a>
          <?php else: ?>
            <a href="?archive=<?= $m['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-outline">Archive</a>
          <?php endif; ?>
          <a href="?delete=<?= $m['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
