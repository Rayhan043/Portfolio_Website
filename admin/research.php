<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'research';
$notice = ''; $error = '';
$uploadDir = __DIR__ . '/../uploads/research';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (($_GET['t'] ?? '') === substr(csrf_token(), 0, 12)) {
        $stmt = $pdo->prepare("SELECT file_path FROM research_papers WHERE id=?");
        $stmt->execute([$id]); $old = $stmt->fetch();
        $pdo->prepare("DELETE FROM research_papers WHERE id=?")->execute([$id]);
        if ($old && $old['file_path'] && strpos($old['file_path'], 'uploads/research/') === 0) {
            $path = __DIR__ . '/../' . $old['file_path']; if (is_file($path)) @unlink($path);
        }
        $notice = 'Research paper deleted.';
    }
}
$edit = null;
if (isset($_GET['edit'])) { $st=$pdo->prepare("SELECT * FROM research_papers WHERE id=?"); $st->execute([(int)$_GET['edit']]); $edit=$st->fetch(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id=(int)($_POST['id'] ?? 0); $title=trim($_POST['title'] ?? '');
    $authors=trim($_POST['authors'] ?? ''); $abstract=trim($_POST['abstract'] ?? '');
    $url=trim($_POST['paper_url'] ?? ''); $date=trim($_POST['published_date'] ?? '');
    if ($title === '') $error='Paper title is required.';
    elseif ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http','https'], true))) $error='Please enter a valid http:// or https:// URL, or leave the optional link empty.';
    else {
        $upErr=null; $uploaded=handle_upload('paper_file',$uploadDir,['pdf'],15*1024*1024,$upErr);
        if ($upErr) $error=$upErr;
        else {
            $existing=null;
            if ($id) { $st=$pdo->prepare("SELECT file_path FROM research_papers WHERE id=?"); $st->execute([$id]); $existing=$st->fetch(); }
            $filePath=$uploaded ?: ($existing['file_path'] ?? null);
            if (!$uploaded && !$url && !$filePath) $error='Upload a PDF or provide an optional paper link.';
            else {
                if ($id) {
                    $st=$pdo->prepare("UPDATE research_papers SET title=?,authors=?,abstract=?,paper_url=?,file_path=?,published_date=?,is_published=? WHERE id=?");
                    $st->execute([$title,$authors,$abstract,$url ?: null,$filePath,$date ?: null,isset($_POST['is_published'])?1:0,$id]);
                } else {
                    $st=$pdo->prepare("INSERT INTO research_papers (title,authors,abstract,paper_url,file_path,published_date,is_published) VALUES (?,?,?,?,?,?,?)");
                    $st->execute([$title,$authors,$abstract,$url ?: null,$filePath,$date ?: null,isset($_POST['is_published'])?1:0]);
                }
                if ($uploaded && !empty($existing['file_path']) && $existing['file_path'] !== $uploaded && strpos($existing['file_path'],'uploads/research/')===0) {
                    $oldPath=__DIR__.'/../'.$existing['file_path']; if(is_file($oldPath)) @unlink($oldPath);
                }
                $notice='Research paper saved.'; $edit=null;
            }
        }
    }
}
$papers=$pdo->query("SELECT * FROM research_papers ORDER BY created_at DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="section-tag">Research Library</div><h2 class="section-title" style="margin-top:6px;">Manage Research Papers</h2>
<?php if($notice): ?><div class="alert alert-success" style="margin-top:16px;"><?=e($notice)?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error" style="margin-top:16px;"><?=e($error)?></div><?php endif; ?>
<div class="glass panel" style="margin-top:20px;">
<h3 style="margin-bottom:16px;"><?= $edit ? 'Edit paper' : 'Add a research paper' ?></h3>
<form method="post" enctype="multipart/form-data"><?php csrf_field(); ?><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<div class="form-group"><label>Paper title *</label><input class="form-control" name="title" required maxlength="200" value="<?=e($edit['title'] ?? '')?>"></div>
<div class="form-group"><label>Authors (optional)</label><input class="form-control" name="authors" value="<?=e($edit['authors'] ?? '')?>"></div>
<div class="form-group"><label>Abstract / description (optional)</label><textarea class="form-control" name="abstract"><?=e($edit['abstract'] ?? '')?></textarea></div>
<div class="grid-2"><div class="form-group"><label>Publication date (optional)</label><input class="form-control" type="date" name="published_date" value="<?=e($edit['published_date'] ?? '')?>"></div>
<div class="form-group"><label>External paper link (optional)</label><input class="form-control" type="url" name="paper_url" placeholder="https://..." value="<?=e($edit['paper_url'] ?? '')?>"></div></div>
<div class="form-group"><label>PDF upload (optional, max 15MB)</label><input class="form-control" type="file" name="paper_file" accept="application/pdf,.pdf"></div>
<label style="display:flex;align-items:center;gap:8px;margin:12px 0;"><input type="checkbox" name="is_published" value="1" <?=(!isset($edit['is_published']) || $edit['is_published'])?'checked':''?>> Show this paper publicly</label>
<button class="btn btn-primary" type="submit">Save Paper</button> <a class="btn btn-outline" href="research.php">Clear</a></form></div>
<div class="glass panel" style="margin-top:22px;"><h3 style="margin-bottom:12px;">Saved papers</h3><table><tr><th>Title</th><th>Public</th><th>Files / links</th><th>Actions</th></tr>
<?php foreach($papers as $paper): ?><tr><td><?=e($paper['title'])?></td><td><?=$paper['is_published']?'Yes':'Hidden'?></td><td><?php if($paper['file_path']): ?>PDF<?php endif; ?> <?=($paper['file_path']&&$paper['paper_url'])?' / ':''?><?= $paper['paper_url']?'Link':'' ?></td><td class="row-actions"><a class="btn btn-outline btn-sm" href="research.php?edit=<?=(int)$paper['id']?>">Edit</a><a class="btn btn-outline btn-sm" href="research.php?delete=<?=(int)$paper['id']?>&amp;t=<?=e(substr(csrf_token(),0,12))?>" onclick="return confirm('Delete this paper?')">Delete</a></td></tr><?php endforeach; ?>
<?php if(!$papers): ?><tr><td colspan="4">No research papers added yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
