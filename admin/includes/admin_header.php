<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin · ML Command Center</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { display: flex; min-height: 100vh; }
  #admin-sidebar {
    width: 230px; flex-shrink: 0; position: fixed; top: 0; bottom: 0; left: 0;
    background: var(--bg-alt); border-right: 1px solid var(--card-border);
    padding: 22px 16px; display: flex; flex-direction: column; z-index: 100;
  }
  #admin-sidebar .brand { font-family: var(--font-head); font-size: 18px; margin-bottom: 28px; padding: 0 10px; }
  #admin-sidebar a {
    display: block; padding: 11px 14px; border-radius: 10px; color: var(--text-dim);
    font-size: 13.5px; margin-bottom: 4px; transition: all .2s;
  }
  #admin-sidebar a:hover, #admin-sidebar a.active { background: var(--card-bg); color: var(--primary); }
  #admin-sidebar .logout { margin-top: auto; color: var(--danger); }
  #admin-main { margin-left: 230px; flex: 1; padding: 32px 40px; position: relative; z-index: 2; max-width: 1200px; }
  @media (max-width: 800px) {
    #admin-sidebar { width: 100%; height: auto; position: static; flex-direction: row; overflow-x: auto; }
    #admin-main { margin-left: 0; padding: 20px; }
    body { flex-direction: column; }
  }
  table { width: 100%; border-collapse: collapse; }
  th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid var(--card-border); font-size: 13.5px; }
  th { color: var(--text-dim); font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; }
  .badge-unread { background: rgba(255,92,122,0.15); color: var(--danger); }
  .badge-read { background: rgba(0,255,179,0.1); color: var(--accent); }
  .stat-card { padding: 20px; }
  .stat-card .val { font-family: var(--font-head); font-size: 30px; color: var(--primary); }
  .stat-card .lbl { color: var(--text-dim); font-size: 12px; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
  .row-actions { display: flex; gap: 8px; }
</style>
</head>
<body>
<div class="bg-gradient"></div>

<nav id="admin-sidebar">
  <div class="brand">ML<span style="color:var(--accent)">.</span>Admin</div>
  <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
  <a href="projects.php" class="<?= $active === 'projects' ? 'active' : '' ?>">🗂 Projects</a>
  <a href="skills.php" class="<?= $active === 'skills' ? 'active' : '' ?>">🧠 Skills</a>
  <a href="experience.php" class="<?= $active === 'experience' ? 'active' : '' ?>">📈 Experience</a>
  <a href="certificates.php" class="<?= $active === 'certificates' ? 'active' : '' ?>">🎓 Certificates</a>
  <a href="research.php" class="<?= $active === 'research' ? 'active' : '' ?>">📚 Research Papers</a>
  <a href="private_notes.php" class="<?= $active === 'notes' ? 'active' : '' ?>">🔒 Private Notes</a>
  <a href="resume.php" class="<?= $active === 'resume' ? 'active' : '' ?>">📄 Resume</a>
  <a href="settings.php" class="<?= $active === 'settings' ? 'active' : '' ?>">⚙️ Site Info</a>
  <a href="messages.php" class="<?= $active === 'messages' ? 'active' : '' ?>">✉️ Messages</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</nav>

<div id="admin-main">
