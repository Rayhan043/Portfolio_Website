<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);

$projects = $pdo->query("SELECT * FROM projects WHERE status='active' ORDER BY sort_order ASC, id DESC")->fetchAll();
$projectImages = [];
if ($projects) {
    $ids = array_column($projects, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id IN ($in)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $img) {
        $projectImages[$img['project_id']][] = $img['image_path'];
    }
}

$skills = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC, id ASC")->fetchAll();
$experience = $pdo->query("SELECT * FROM experience ORDER BY start_date DESC")->fetchAll();
$certificates = $pdo->query("SELECT * FROM certificates ORDER BY sort_order ASC, id DESC")->fetchAll();
$resume = $pdo->query("SELECT * FROM resume ORDER BY id DESC LIMIT 1")->fetch();
$researchPapers = $pdo->query("SELECT * FROM research_papers WHERE is_published=1 ORDER BY COALESCE(published_date, DATE(created_at)) DESC, id DESC")->fetchAll();
$privateNotes = $pdo->query("SELECT id, title, description, created_at FROM private_notes ORDER BY created_at DESC")->fetchAll();

$stats = [
    'projects'      => count($projects),
    'models'        => count(array_filter($projects, fn($p) => stripos($p['technologies'], 'model') !== false)) ?: count($projects),
    'technologies'  => count($skills),
    'certifications'=> count($certificates),
];

$siteDataJs = [
    'about' => $settings['about_me'] ?? '',
    'skills' => array_map(fn($s) => ['name' => $s['name'], 'proficiency' => (int)$s['proficiency']], $skills),
    'projects' => array_map(fn($p) => ['title' => $p['title']], $projects),
    'experience' => array_map(fn($x) => ['title' => $x['title'], 'organization' => $x['organization']], $experience),
    'certificates' => array_map(fn($c) => ['title' => $c['title']], $certificates),
];

include __DIR__ . '/includes/header.php';
?>

<!-- ============ DASHBOARD / HERO ============ -->
<section id="dashboard">
  <div class="control-center">
    <div class="orbit-ring r1"></div>
    <div class="orbit-ring r2"></div>

    <div class="center-node">
      <?php if (!empty($settings['profile_image'])): ?>
        <img class="profile-portrait" src="<?= e($settings['profile_image']) ?>" alt="<?= e($settings['full_name'] ?? 'Profile photo') ?>">
      <?php else: ?>
        <div class="profile-portrait-placeholder" aria-hidden="true">✦</div>
      <?php endif; ?>
      <h1><?= e($settings['full_name'] ?? 'Your Name') ?></h1>
      <div class="role"><?= e($settings['tagline'] ?? 'Machine Learning Engineer') ?></div>
    </div>

    <div class="module glass mod-1" data-target="projects" onclick="document.getElementById('projects').scrollIntoView({behavior:'smooth'})">
      <div class="m-value" data-counter="<?= (int)$stats['projects'] ?>">0</div>
      <div class="m-label">Active Projects</div>
    </div>
    <div class="module glass mod-2" onclick="document.getElementById('projects').scrollIntoView({behavior:'smooth'})">
      <div class="m-value" data-counter="<?= (int)$stats['models'] ?>">0</div>
      <div class="m-label">Models Trained</div>
    </div>
    <div class="module glass mod-3" onclick="document.getElementById('skills').scrollIntoView({behavior:'smooth'})">
      <div class="m-value" data-counter="<?= (int)$stats['technologies'] ?>">0</div>
      <div class="m-label">Technologies</div>
    </div>
    <div class="module glass mod-4" onclick="document.getElementById('experience').scrollIntoView({behavior:'smooth'})">
      <div class="m-value"><?= count($experience) ?></div>
      <div class="m-label">Experience</div>
    </div>
    <div class="module glass mod-5" onclick="window.open('<?= e($settings['github_url'] ?? '#') ?>','_blank')">
      <div class="m-value">Git</div>
      <div class="m-label">GitHub Activity</div>
    </div>
    <div class="module glass mod-6" onclick="document.getElementById('dashboard').scrollIntoView({behavior:'smooth'})">
      <div class="m-value">R&amp;D</div>
      <div class="m-label">Research Notes</div>
    </div>
    <div class="module glass mod-7" onclick="document.getElementById('certifications').scrollIntoView({behavior:'smooth'})">
      <div class="m-value" data-counter="<?= (int)$stats['certifications'] ?>">0</div>
      <div class="m-label">Certifications</div>
    </div>

    <!-- Clean Grid Layout -->
    <div class="modules-grid-clean">
      <div class="module glass" style="position:static;" onclick="document.getElementById('projects').scrollIntoView({behavior:'smooth'})"><div class="m-value" data-counter="<?= (int)$stats['projects'] ?>">0</div><div class="m-label">Projects</div></div>
      <div class="module glass" style="position:static;" onclick="document.getElementById('projects').scrollIntoView({behavior:'smooth'})"><div class="m-value" data-counter="<?= (int)$stats['models'] ?>">0</div><div class="m-label">Models</div></div>
      <div class="module glass" style="position:static;" onclick="document.getElementById('skills').scrollIntoView({behavior:'smooth'})"><div class="m-value" data-counter="<?= (int)$stats['technologies'] ?>">0</div><div class="m-label">Tech</div></div>
      <div class="module glass" style="position:static;" onclick="document.getElementById('experience').scrollIntoView({behavior:'smooth'})"><div class="m-value"><?= count($experience) ?></div><div class="m-label">Experience</div></div>
      <div class="module glass" style="position:static;" onclick="window.open('<?= e($settings['github_url'] ?? '#') ?>','_blank')"><div class="m-value">Git</div><div class="m-label">GitHub</div></div>
      <div class="module glass" style="position:static;" onclick="document.getElementById('certifications').scrollIntoView({behavior:'smooth'})"><div class="m-value" data-counter="<?= (int)$stats['certifications'] ?>">0</div><div class="m-label">Certifications</div></div>
    </div>
  </div>

  <div class="scroll-hint">Scroll to explore <div class="chevron">⌄</div></div>
</section>

<!-- ============ ABOUT / DASHBOARD DETAIL ============ -->
<section class="section reveal" id="about">
  <div class="grid-2">
    <div class="glass panel">
      <div class="section-tag">About Me</div>
      <p style="margin-top:14px; color: var(--text-dim);"><?= nl2br(e($settings['about_me'] ?? '')) ?></p>
      <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
        <a href="<?= e($settings['github_url'] ?? '#') ?>" target="_blank" class="btn btn-outline">GitHub →</a>
        <a href="<?= e($settings['linkedin_url'] ?? '#') ?>" target="_blank" class="btn btn-outline">LinkedIn →</a>
      </div>
    </div>
    <div class="glass panel">
      <div class="section-tag">Current Learning Path</div>
      <p style="margin-top:10px; color: var(--text-dim); font-size: 14px;"><?= e($settings['current_learning_path'] ?? '') ?></p>
      <div class="section-tag" style="margin-top:22px;">Career Goal</div>
      <p style="margin-top:10px; color: var(--text-dim); font-size: 14px;"><?= e($settings['career_goal'] ?? '') ?></p>
    </div>
  </div>
</section>

<!-- ============ PROJECT VAULT ============ -->
<section class="section reveal" id="projects">
  <div class="section-head">
    <div class="section-tag">Project Vault</div>
    <h2 class="section-title">Deployed Experiments</h2>
    <p class="section-sub">Real projects, real models — click any card to open the full lab report.</p>
  </div>
  <div class="grid-3">
    <?php if (!$projects): ?>
      <p style="color:var(--text-dim);">No projects published yet. Add some from the admin panel.</p>
    <?php endif; ?>
    <?php foreach ($projects as $p): ?>
      <?php
        $imgs = $projectImages[$p['id']] ?? [];
        
        // Auto Resolution Path Logic
        $thumbPath = $p['thumbnail'] ?? '';
        if ($thumbPath && !str_starts_with($thumbPath, 'http')) {
            if (!str_starts_with($thumbPath, 'uploads/')) {
                $thumbPath = 'uploads/projects/' . $thumbPath;
            }
        }

        $payload = [
          'title'        => $p['title'],
          'description'  => $p['description'],
          'technologies' => $p['technologies'],
          'github'       => $p['github_link'] ?? '',
          'demo'         => $p['demo_link'] ?? '',
          'thumbnail'    => $thumbPath,
          'video'        => $p['video_path'] ?? '',
          'images'       => $imgs,
        ];
        
        $jsonPayload = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
      ?>
      <div class="project-card glass" onclick="openProjectModal(JSON.parse(this.getAttribute('data-payload')))" data-payload="<?= $jsonPayload ?>" style="cursor:pointer;">
        <img class="project-thumb" src="<?= e($thumbPath ?: 'assets/img/placeholder.svg') ?>" alt="<?= e($p['title']) ?>" onerror="this.onerror=null; this.src='assets/img/placeholder.svg';">
        <div class="p-body">
          <h3><?= e($p['title']) ?></h3>
          <p><?= e(mb_strimwidth($p['description'], 0, 110, '…')) ?></p>
          <div class="tag-list">
            <?php foreach (array_slice(explode(',', $p['technologies']), 0, 3) as $t): ?>
              <span class="tag-chip"><?= e(trim($t)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ SKILLS MATRIX + CONSTELLATION ============ -->
<section class="section reveal" id="skills">
  <div class="section-head">
    <div class="section-tag">Skills Matrix</div>
    <h2 class="section-title">Tech Constellation</h2>
    <p class="section-sub">Core competencies, mapped as a live network.</p>
  </div>
  <div class="grid-2">
    <div class="glass panel">
      <?php foreach ($skills as $s): ?>
        <div class="skill-row">
          <div class="skill-name"><?= e($s['name']) ?></div>
          <div class="skill-bar-track"><div class="skill-bar-fill" data-pct="<?= (int)$s['proficiency'] ?>"></div></div>
          <div class="skill-pct"><?= (int)$s['proficiency'] ?>%</div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="glass panel">
      <svg id="constellation"></svg>
    </div>
  </div>
</section>

<!-- ============ EXPERIENCE TIMELINE ============ -->
<section class="section reveal" id="experience">
  <div class="section-head">
    <div class="section-tag">Experience</div>
    <h2 class="section-title">Timeline</h2>
  </div>
  <div class="glass panel">
    <div class="timeline">
      <?php if (!$experience): ?><p style="color:var(--text-dim);">No experience entries yet.</p><?php endif; ?>
      <?php foreach ($experience as $x): ?>
        <div class="timeline-item">
          <h3><?= e($x['title']) ?></h3>
          <div class="meta"><?= e($x['organization']) ?> · <?= format_date_range($x['start_date'], $x['end_date']) ?></div>
          <p><?= nl2br(e($x['description'])) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ RESUME CENTER ============ -->
<section class="section reveal" id="resume">
  <div class="section-head">
    <div class="section-tag">Resume Center</div>
    <h2 class="section-title">Download / Preview</h2>
  </div>
  <div class="glass panel">
    <?php if ($resume): ?>
      <iframe class="resume-frame" src="<?= e($resume['file_path']) ?>"></iframe>
      <div style="margin-top:18px;">
        <a href="download_resume.php" class="btn btn-primary">⬇ Download Resume</a>
      </div>
    <?php else: ?>
      <p style="color:var(--text-dim);">Resume not uploaded yet.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ============ RESEARCH PAPERS ============ -->
<section class="section reveal" id="research">
  <div class="section-head">
    <div class="section-tag">Research & Publications</div>
    <h2 class="section-title">Research Papers</h2>
    <p class="section-sub">Research work, thesis papers and publications. Paper links are optional.</p>
  </div>
  <div class="grid-3">
    <?php if (!$researchPapers): ?><div class="glass panel"><h3>Research library is ready</h3><p style="color:var(--text-dim);margin-top:8px;">Research papers will appear here when published from the admin panel.</p></div><?php endif; ?>
    <?php foreach ($researchPapers as $paper): ?>
      <article class="glass panel research-card">
        <div class="section-tag">Research Paper</div>
        <h3 style="margin-top:12px;"><?=e($paper['title'])?></h3>
        <?php if (!empty($paper['authors'])): ?><p class="research-meta"><?=e($paper['authors'])?></p><?php endif; ?>
        <?php if (!empty($paper['published_date'])): ?><p class="research-meta"><?=e(date('M Y',strtotime($paper['published_date'])))?></p><?php endif; ?>
        <?php if (!empty($paper['abstract'])): ?><p style="color:var(--text-dim);margin-top:12px;"><?=e($paper['abstract'])?></p><?php endif; ?>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;">
          <?php if (!empty($paper['file_path'])): ?><a class="btn btn-primary" href="<?=e($paper['file_path'])?>" target="_blank" rel="noopener">Read PDF ↗</a><?php endif; ?>
          <?php if (!empty($paper['paper_url'])): ?><a class="btn btn-outline" href="<?=e($paper['paper_url'])?>" target="_blank" rel="noopener noreferrer">External Link ↗</a><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ PRIVATE NOTES VAULT ============ -->
<section class="section reveal" id="private-notes">
  <div class="section-head">
    <div class="section-tag">Restricted Access</div>
    <h2 class="section-title">Private Notes Vault</h2>
    <p class="section-sub">These PDFs are locked. A correct password is required before a file can be opened.</p>
  </div>
  <div class="grid-3">
    <?php if (!$privateNotes): ?><div class="glass panel"><div class="locked-icon">⌑</div><h3>Locked study notes</h3><p style="color:var(--text-dim);margin-top:8px;">Private notes uploaded by the administrator will appear here as locked items.</p></div><?php endif; ?>
    <?php foreach ($privateNotes as $note): ?>
      <article class="glass panel private-note-card">
        <div class="locked-icon">🔒</div>
        <h3 style="margin-top:12px;"><?=e($note['title'])?></h3>
        <?php if (!empty($note['description'])): ?><p style="color:var(--text-dim);margin-top:8px;"><?=e($note['description'])?></p><?php endif; ?>
        <form method="post" action="view_note.php" style="margin-top:16px;">
          <?php csrf_field(); ?>
          <input type="hidden" name="note_id" value="<?=(int)$note['id']?>">
          <div class="form-group"><label for="note-password-<?=(int)$note['id']?>">Password to unlock</label><input class="form-control" id="note-password-<?=(int)$note['id']?>" type="password" name="note_password" required autocomplete="current-password" placeholder="Enter password"></div>
          <button class="btn btn-primary" type="submit">Unlock PDF</button>
        </form>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ CERTIFICATIONS ============ -->
<section class="section reveal" id="certifications">
  <div class="section-head">
    <div class="section-tag">Certifications Center</div>
    <h2 class="section-title">Verified Credentials</h2>
  </div>
  <div class="grid-3">
    <?php if (!$certificates): ?><p style="color:var(--text-dim);">No certificates uploaded yet.</p><?php endif; ?>
    <?php foreach ($certificates as $c): ?>
      <div class="cert-card glass">
        <img class="cert-thumb" src="<?= e($c['image_path'] ?: 'assets/img/placeholder.svg') ?>" alt="<?= e($c['title']) ?>">
        <div>
          <h4><?= e($c['title']) ?></h4>
          <div class="meta"><?= e($c['issuer']) ?><?= $c['issue_date'] ? ' · ' . date('M Y', strtotime($c['issue_date'])) : '' ?></div>
          <?php if ($c['pdf_path']): ?><a href="<?= e($c['pdf_path']) ?>" target="_blank" class="btn btn-sm btn-outline">View PDF</a><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ CONTACT HUB ============ -->
<section class="section reveal" id="contact">
  <div class="section-head">
    <div class="section-tag">Contact Hub</div>
    <h2 class="section-title">Open a Channel</h2>
    <p class="section-sub">Ready to collaborate? Drop me a message or reach out directly.</p>
  </div>
  <div class="grid-2">
    <div class="glass panel" style="position: relative; overflow: hidden;">
      <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(123,97,255,0.1) 0%, transparent 70%); pointer-events: none; border-radius: 50%;"></div>
      <div style="position: relative; z-index: 1;">
        <div id="contact-alert" class="alert" style="display:none; margin-bottom:16px;"></div>
        <form id="contact-form">
          <div class="form-group"><label style="font-weight: 600; color: #E8EDF9;">Full Name</label><input required class="form-control" type="text" name="name" placeholder="Your name"></div>
          <div class="form-group"><label style="font-weight: 600; color: #E8EDF9;">Email Address</label><input required class="form-control" type="email" name="email" placeholder="your@email.com"></div>
          <div class="form-group"><label style="font-weight: 600; color: #E8EDF9;">Company (optional)</label><input class="form-control" type="text" name="company" placeholder="Your company"></div>
          <div class="form-group"><label style="font-weight: 600; color: #E8EDF9;">Subject</label><input required class="form-control" type="text" name="subject" placeholder="What is this about?"></div>
          <div class="form-group"><label style="font-weight: 600; color: #E8EDF9;">Message</label><textarea required class="form-control" name="message" rows="5" placeholder="Tell me more..."></textarea></div>
          <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 15px; font-weight: 600;">Send Message</button>
        </form>
      </div>
    </div>
    <div class="glass panel" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 2px; color: #00E5FF; margin-bottom: 24px; font-weight: 600;">Direct Contact</div>
        <div class="contact-info-row" style="margin-bottom: 20px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(8px)'; this.style.color='#00E5FF';" onmouseout="this.style.transform='translateX(0)'; this.style.color='#A0AEC0';">
          <div class="ico" style="font-size: 20px;">@</div>
          <div style="flex: 1; display: flex; flex-direction: column;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 4px;">Email</div>
            <a href="mailto:<?= e($settings['email'] ?? '') ?>" style="color: inherit; text-decoration: none; font-weight: 500;"><?= e($settings['email'] ?? '') ?></a>
          </div>
        </div>
        <div class="contact-info-row" style="margin-bottom: 20px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(8px)'; this.style.color='#00E5FF';" onmouseout="this.style.transform='translateX(0)'; this.style.color='#A0AEC0';">
          <div class="ico" style="font-size: 20px;">☎</div>
          <div style="flex: 1; display: flex; flex-direction: column;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 4px;">Phone</div>
            <a href="tel:<?= e($settings['phone'] ?? '') ?>" style="color: inherit; text-decoration: none; font-weight: 500;"><?= e($settings['phone'] ?? '') ?></a>
          </div>
        </div>
        <div class="contact-info-row" style="margin-bottom: 24px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(8px)'; this.style.color='#00E5FF';" onmouseout="this.style.transform='translateX(0)'; this.style.color='#A0AEC0';">
          <div class="ico" style="font-size: 20px;">📍</div>
          <div style="flex: 1; display: flex; flex-direction: column;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 4px;">Location</div>
            <div style="color: inherit; font-weight: 500;"><?= e($settings['location'] ?? '') ?></div>
          </div>
        </div>
      </div>
      <div style="padding-top: 24px; border-top: 1px solid rgba(0,229,255,0.2);">
        <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 2px; color: #00E5FF; margin-bottom: 16px; font-weight: 600;">Follow My Work</div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= e($settings['github_url'] ?? '#') ?>" target="_blank" class="btn btn-outline" style="flex: 1; text-align: center; font-weight: 600; padding: 10px 16px;">GitHub</a>
          <a href="<?= e($settings['linkedin_url'] ?? '#') ?>" target="_blank" class="btn btn-outline" style="flex: 1; text-align: center; font-weight: 600; padding: 10px 16px;">LinkedIn</a>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  buildConstellation(window.SITE_DATA.skills || []);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>