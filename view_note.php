<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
csrf_verify();
$id=(int)($_POST['note_id']??0); $password=(string)($_POST['note_password']??'');
$st=$pdo->prepare("SELECT title,file_path,password_hash FROM private_notes WHERE id=?");
$st->execute([$id]); $note=$st->fetch();
if(!$note || !password_verify($password,$note['password_hash'])){
  http_response_code(403);
  header('Content-Type: text/html; charset=UTF-8');
  echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Access denied</title><body style="font-family:Times New Roman,serif;background:#050505;color:#fff;padding:40px"><h1 style="color:#00F5FF">Incorrect password</h1><p>The password did not match. Return to the <a style="color:#00F5FF" href="index.php#private-notes">locked notes</a> section and try again.</p></body></html>'; exit;
}
$relative=str_replace('\\','/',trim((string)$note['file_path']));
$relative=preg_replace('#^uploads/private-notes/#','uploads/private_notes/',$relative);
$path=realpath(__DIR__.'/'.$relative);
$privateDir=realpath(__DIR__.'/uploads/private_notes');
if($path === false || $privateDir === false || strpos($path, $privateDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)){
  http_response_code(404);
  exit('File not found.');
}
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="private-note-'.$id.'.pdf"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
header('Content-Length: '.filesize($path));
readfile($path);
exit;
