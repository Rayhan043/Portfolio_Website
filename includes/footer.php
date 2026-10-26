<footer>
  &copy; <?= date('Y') ?> <?= e($settings['full_name'] ?? '') ?>. Built With Me.
</footer>

<nav id="command-dock">
  <div class="dock-item active" data-target="dashboard">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
    <span>Dashboard</span>
  </div>
  <div class="dock-item" data-target="projects">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v12H4z"/><path d="M4 20h16"/></svg>
    <span>Projects</span>
  </div>
  <div class="dock-item" data-target="skills">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
    <span>Skills</span>
  </div>
  <div class="dock-item" data-target="experience">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
    <span>Experience</span>
  </div>
  <div class="dock-item" data-target="resume">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v5h5"/></svg>
    <span>Resume</span>
  </div>
  <div class="dock-item" data-target="research">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/></svg>
    <span>Research</span>
  </div>
  <div class="dock-item" data-target="private-notes">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
    <span>Locked Notes</span>
  </div>
  <div class="dock-item" data-target="certifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5"/></svg>
    <span>Certificate</span>
  </div>
  <div class="dock-item" data-target="contact">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 6l8 7 8-7"/></svg>
    <span>Contact</span>
  </div>
</nav>

<div class="modal-overlay" id="project-modal">
  <div class="modal-box glass">
    <div class="modal-close">&times;</div>
    <div class="modal-media" id="modal-main-media"></div>
    <div class="modal-gallery" id="modal-gallery"></div>
    <div class="modal-content-inner">
      <h2 id="modal-title"></h2>
      <p id="modal-desc" style="color:var(--text-dim); margin-top:10px;"></p>
      <div class="tag-list" id="modal-tech"></div>
      <div style="margin-top:20px; display:flex; gap:12px;">
        <a href="#" id="modal-gh" target="_blank" class="btn btn-outline">GitHub Repo</a>
        <a href="#" id="modal-demo" target="_blank" class="btn btn-primary">Live Demo</a>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/main.js?v=<?= time() ?>"></script>
<script>window.SITE_DATA = <?= json_encode($siteDataJs ?? [], JSON_UNESCAPED_SLASHES) ?>;</script>
</body>
</html>
