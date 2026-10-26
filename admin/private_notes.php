<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$active = 'notes'; $notice=''; $error='';
$uploadDir=__DIR__.'/../uploads/private_notes';
if(isset($_GET['delete'])) {
  $id=(int)$_GET['delete'];
  if(($_GET['t'] ?? '')===substr(csrf_token(),0,12)) {
    $st=$pdo->prepare("SELECT file_path FROM private_notes WHERE id=?"); $st->execute([$id]); $old=$st->fetch();
    $pdo->prepare("DELETE FROM private_notes WHERE id=?")->execute([$id]);
    if($old && strpos($old['file_path'],'uploads/private_notes/')===0){$f=__DIR__.'/../'.$old['file_path'];if(is_file($f))@unlink($f);}
    $notice='Private note deleted.';
  }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_verify(); $id=(int)($_POST['id']??0); $title=trim($_POST['title']??''); $desc=trim($_POST['description']??''); $password=$_POST['note_password']??'';
 if($title==='')$error='A note title is required.';
 else{
  $upErr=null; $uploaded=handle_upload('note_pdf',$uploadDir,['pdf'],20*1024*1024,$upErr);
  if($upErr)$error=$upErr;
  else{
   $existing=null;if($id){$st=$pdo->prepare("SELECT * FROM private_notes WHERE id=?");$st->execute([$id]);$existing=$st->fetch();}
   if(!$existing && !$uploaded)$error='Please upload a PDF.';
   elseif(!$existing && strlen($password)<8)$error='Set a password with at least 8 characters.';
   elseif($existing && !$uploaded && $password==='' && !$existing)$error='Upload a PDF.';
   else{
    $path=$uploaded?:($existing['file_path']??'');
    $hash=($password!=='')?password_hash($password,PASSWORD_DEFAULT):($existing['password_hash']??'');
    if($id){$st=$pdo->prepare("UPDATE private_notes SET title=?,description=?,file_path=?,password_hash=? WHERE id=?");$st->execute([$title,$desc,$path,$hash,$id]);}
    else{$st=$pdo->prepare("INSERT INTO private_notes(title,description,file_path,password_hash) VALUES(?,?,?,?)");$st->execute([$title,$desc,$path,$hash]);}
    if($uploaded && !empty($existing['file_path']) && $existing['file_path']!==$uploaded && strpos($existing['file_path'],'uploads/private_notes/')===0){$f=__DIR__.'/../'.$existing['file_path'];if(is_file($f))@unlink($f);}
    $notice='Private PDF saved. The password is never displayed.'; 
   }
  }
 }
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare("SELECT * FROM private_notes WHERE id=?");$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$notes=$pdo->query("SELECT id,title,description,file_path,created_at FROM private_notes ORDER BY created_at DESC")->fetchAll();
include __DIR__.'/includes/admin_header.php';
?>
<div class="section-tag">Locked Notes Vault</div><h2 class="section-title" style="margin-top:6px;">Private PDF Notes</h2>
<p style="color:var(--text-dim);margin-top:8px;">PDF files are stored in a blocked folder and served only after the correct password is verified. Use a unique password of at least 8 characters.</p>
<?php if($notice):?><div class="alert alert-success" style="margin-top:16px;"><?=e($notice)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error" style="margin-top:16px;"><?=e($error)?></div><?php endif;?>
<div class="glass panel" style="margin-top:20px;"><h3 style="margin-bottom:16px;"><?=$edit?'Edit private note':'Upload private note'?></h3>
<form method="post" enctype="multipart/form-data"><?php csrf_field();?><input type="hidden" name="id" value="<?=(int)($edit['id']??0)?>">
<div class="form-group"><label>Note title *</label><input class="form-control" name="title" required maxlength="200" value="<?=e($edit['title']??'')?>"></div>
<div class="form-group"><label>Description (optional)</label><textarea class="form-control" name="description"><?=e($edit['description']??'')?></textarea></div>
<div class="form-group"><label>PDF file (max 20MB) <?=$edit?'— choose only to replace current file':''?></label><input class="form-control" type="file" name="note_pdf" accept="application/pdf,.pdf" <?=$edit?'':'required'?>></div>
<div class="form-group"><label><?=$edit?'New password (leave blank to keep current password)':'Password to unlock (minimum 8 characters)'?></label><input class="form-control" type="password" name="note_password" minlength="8" <?=$edit?'':'required'?> autocomplete="new-password"></div>
<button class="btn btn-primary" type="submit">Save Locked PDF</button> <a class="btn btn-outline" href="private_notes.php">Clear</a></form></div>
<div class="glass panel" style="margin-top:22px;"><h3 style="margin-bottom:12px;">Locked notes on site</h3><table><tr><th>Title</th><th>Uploaded</th><th>Actions</th></tr>
<?php foreach($notes as $n):?><tr><td><?=e($n['title'])?></td><td><?=date('d M Y',strtotime($n['created_at']))?></td><td class="row-actions"><a class="btn btn-outline btn-sm" href="private_notes.php?edit=<?=(int)$n['id']?>">Edit</a><a class="btn btn-outline btn-sm" href="private_notes.php?delete=<?=(int)$n['id']?>&amp;t=<?=e(substr(csrf_token(),0,12))?>" onclick="return confirm('Delete this private PDF?')">Delete</a></td></tr><?php endforeach;?>
<?php if(!$notes):?><tr><td colspan="3">No private notes uploaded.</td></tr><?php endif;?></table></div>
<?php include __DIR__.'/includes/admin_footer.php';?>
