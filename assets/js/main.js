// ===================== LOADER =====================
window.addEventListener('load', () => {
  const loader = document.getElementById('loader');
  setTimeout(() => loader && loader.classList.add('hidden'), 500);
});

// ===================== DATA STREAM PARTICLE BACKGROUND =====================
(function particles() {
  const canvas = document.getElementById('data-stream');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let w, h, particles;

  function resize() {
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resize);
  resize();

  const COUNT = window.innerWidth < 768 ? 40 : 90;
  particles = Array.from({ length: COUNT }, () => ({
    x: Math.random() * w,
    y: Math.random() * h,
    vy: 0.2 + Math.random() * 0.6,
    r: 0.6 + Math.random() * 1.6,
    hue: Math.random() > 0.5 ? '0,229,255' : '123,97,255',
  }));

  function tick() {
    ctx.clearRect(0, 0, w, h);
    particles.forEach(p => {
      p.y -= p.vy;
      if (p.y < -10) { p.y = h + 10; p.x = Math.random() * w; }
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${p.hue}, 0.5)`;
      ctx.fill();
    });
    requestAnimationFrame(tick);
  }
  tick();
})();

// ===================== ANIMATED COUNTERS =====================
function animateCounters() {
  document.querySelectorAll('[data-counter]').forEach(el => {
    const target = parseInt(el.getAttribute('data-counter'), 10) || 0;
    if (el.dataset.done) return;
    el.dataset.done = '1';
    let cur = 0;
    const step = Math.max(1, Math.ceil(target / 40));
    const iv = setInterval(() => {
      cur += step;
      if (cur >= target) { cur = target; clearInterval(iv); }
      el.textContent = cur;
    }, 30);
  });
}
animateCounters();

// ===================== SCROLL REVEAL =====================
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      entry.target.querySelectorAll('.skill-bar-fill').forEach(bar => {
        bar.style.width = bar.getAttribute('data-pct') + '%';
      });
    }
  });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ===================== COMMAND DOCK ACTIVE STATE =====================
const dockItems = document.querySelectorAll('.dock-item');
const sections = [...dockItems].map(d => document.getElementById(d.dataset.target)).filter(Boolean);

dockItems.forEach(item => {
  item.addEventListener('click', () => {
    const target = document.getElementById(item.dataset.target);
    if (target) target.scrollIntoView({ behavior: 'smooth' });
  });
});

const navObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    const id = entry.target.id;
    const dockItem = document.querySelector(`.dock-item[data-target="${id}"]`);
    if (!dockItem) return;
    if (entry.isIntersecting) {
      dockItems.forEach(d => d.classList.remove('active'));
      dockItem.classList.add('active');
    }
  });
}, { threshold: 0.4 });
sections.forEach(s => navObserver.observe(s));

// ===================== CRASH-PROOF PROJECT MODAL =====================
function getUploadPath(path) {
  if (!path) return '';
  let cleanPath = String(path).replace(/\\/g, '/').replace(/^\/+/, '');
  if (cleanPath.startsWith('http://') || cleanPath.startsWith('https://')) {
    return cleanPath;
  }
  if (cleanPath.startsWith('uploads/')) {
    return cleanPath;
  }
  return 'uploads/projects/' + cleanPath;
}

function openProjectModal(data) {
  try {
    const overlay = document.getElementById('project-modal');
    const galleryWrap = document.getElementById('modal-gallery');
    const mainMedia = document.getElementById('modal-main-media');
    const title = document.getElementById('modal-title');
    const desc = document.getElementById('modal-desc');
    const techWrap = document.getElementById('modal-tech');
    const linkGh = document.getElementById('modal-gh');
    const linkDemo = document.getElementById('modal-demo');

    if (!overlay) return;

    title.textContent = data.title || 'Project Details';
    desc.textContent = data.description || '';
    
    if (data.technologies && typeof data.technologies === 'string') {
      techWrap.innerHTML = data.technologies.split(',').map(t => `<span class="tag-chip">${t.trim()}</span>`).join('');
    } else {
      techWrap.innerHTML = '';
    }

    if (data.github && data.github.trim() !== '') { 
      linkGh.href = data.github; 
      linkGh.style.display = 'inline-flex'; 
    } else { 
      linkGh.style.display = 'none'; 
    }

    if (data.demo && data.demo.trim() !== '') { 
      linkDemo.href = data.demo; 
      linkDemo.style.display = 'inline-flex'; 
    } else { 
      linkDemo.style.display = 'none'; 
    }

    const media = [];
    if (data.video) media.push({ type: 'video', src: getUploadPath(data.video) });
    if (Array.isArray(data.images) && data.images.length > 0) {
      data.images.forEach(img => { if (img) media.push({ type: 'image', src: getUploadPath(img) }); });
    }
    if (media.length === 0 && data.thumbnail) {
      media.push({ type: 'image', src: getUploadPath(data.thumbnail) });
    }

    function renderMain(item) {
      if (!item) { mainMedia.innerHTML = ''; return; }
      mainMedia.innerHTML = item.type === 'video'
        ? `<video src="${item.src}" controls autoplay style="width:100%; max-height:400px; border-radius:8px;"></video>`
        : `<img src="${item.src}" alt="${data.title || 'Project'}" style="width:100%; max-height:400px; object-fit:cover; border-radius:8px;" onerror="this.src='assets/img/placeholder.svg';">`;
    }
    renderMain(media[0]);

    if (galleryWrap) {
      galleryWrap.innerHTML = media.map((m, i) => {
        if (m.type === 'video') {
          return `<div data-i="${i}" class="thumb-item ${i === 0 ? 'active' : ''}" style="width:45px; height:45px; background:#7B61FF; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; cursor:pointer; color:#fff; font-weight:bold; font-size:14px; margin-right:6px;">▶</div>`;
        }
        return `<img src="${m.src}" data-i="${i}" class="${i === 0 ? 'active' : ''}" style="width:45px; height:45px; object-fit:cover; border-radius:6px; cursor:pointer; margin-right:6px;" onerror="this.src='assets/img/placeholder.svg';">`;
      }).join('');

      galleryWrap.querySelectorAll('[data-i]').forEach(el => {
        el.addEventListener('click', () => {
          galleryWrap.querySelectorAll('[data-i]').forEach(i => i.classList.remove('active'));
          el.classList.add('active');
          renderMain(media[parseInt(el.dataset.i, 10)]);
        });
      });
    }

    overlay.classList.add('open');
  } catch (err) {
    console.error('Modal Opening Error:', err);
  }
}

document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
  el.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-close') || e.target.classList.contains('modal-overlay')) {
      e.currentTarget.closest('.modal-overlay')?.classList.remove('open')
        || document.querySelectorAll('.modal-overlay').forEach(o => o.classList.remove('open'));
      const mainMedia = document.getElementById('modal-main-media');
      if (mainMedia) mainMedia.innerHTML = '';
    }
  });
});

// ===================== TECH CONSTELLATION =====================
function buildConstellation(skills) {
  const svg = document.getElementById('constellation');
  if (!svg || !skills.length) return;
  const W = svg.clientWidth || 600, H = 340;
  svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
  const cx = W / 2, cy = H / 2, radius = Math.min(W, H) / 2 - 50;

  const nodes = skills.map((s, i) => {
    const angle = (i / skills.length) * Math.PI * 2;
    return { ...s, x: cx + radius * Math.cos(angle), y: cy + radius * Math.sin(angle) };
  });

  let svgHTML = '';
  nodes.forEach((n) => {
    svgHTML += `<line class="const-link" x1="${cx}" y1="${cy}" x2="${n.x}" y2="${n.y}" />`;
  });
  svgHTML += `<circle cx="${cx}" cy="${cy}" r="26" fill="rgba(123,97,255,0.25)" stroke="#7B61FF" stroke-width="1.5" />`;
  svgHTML += `<text x="${cx}" y="${cy + 4}" text-anchor="middle" font-size="10" fill="#E8EDF9">SKILLS</text>`;

  nodes.forEach(n => {
    const r = 8 + (n.proficiency / 100) * 12;
    svgHTML += `<g class="const-node">
      <circle cx="${n.x}" cy="${n.y}" r="${r}" />
      <text x="${n.x}" y="${n.y + r + 14}" text-anchor="middle">${n.name}</text>
    </g>`;
  });

  svg.innerHTML = svgHTML;
}

// ===================== TERMINAL MODE =====================
(function terminal() {
  const toggle = document.getElementById('terminal-toggle');
  const win = document.getElementById('terminal-window');
  const closeBtn = document.getElementById('terminal-close');
  const body = document.getElementById('terminal-body');
  const input = document.getElementById('terminal-input');
  if (!toggle || !win) return;

  const commands = {
    help: () => 'Available commands: help, about, skills, projects, experience, certifications, resume, contact, clear',
    about: () => window.SITE_DATA?.about || 'No data.',
    skills: () => (window.SITE_DATA?.skills || []).map(s => `${s.name.padEnd(20)} ${'█'.repeat(Math.round(s.proficiency / 10))}`).join('\n'),
    projects: () => (window.SITE_DATA?.projects || []).map(p => `→ ${p.title}`).join('\n') || 'No projects yet.',
    experience: () => (window.SITE_DATA?.experience || []).map(x => `→ ${x.title} @ ${x.organization}`).join('\n') || 'No experience yet.',
    certifications: () => (window.SITE_DATA?.certificates || []).map(c => `→ ${c.title}`).join('\n') || 'No certificates yet.',
    resume: () => { window.location.href = '#resume'; win.classList.remove('open'); return 'Scrolling to resume...'; },
    contact: () => { window.location.href = '#contact'; win.classList.remove('open'); return 'Scrolling to contact...'; },
  };

  function printLine(text, isPrompt) {
    const div = document.createElement('div');
    div.className = 'line';
    if (isPrompt) div.innerHTML = `<span class="prompt">guest@ml-portfolio:~$</span> ${text}`;
    else div.textContent = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }

  toggle.addEventListener('click', () => {
    win.classList.add('open');
    input.focus();
    if (!body.dataset.greeted) {
      printLine('Welcome to Terminal Mode. Type "help" to see available commands.');
      body.dataset.greeted = '1';
    }
  });
  closeBtn.addEventListener('click', () => win.classList.remove('open'));

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { win.classList.remove('open'); return; }
    if (e.key !== 'Enter') return;
    const cmd = input.value.trim().toLowerCase();
    printLine(input.value, true);
    input.value = '';
    if (cmd === 'clear') { body.innerHTML = ''; return; }
    if (commands[cmd]) printLine(commands[cmd]());
    else printLine(`Command not found: "${cmd}". Type "help".`);
  });
})();

// ===================== CONTACT FORM (AJAX) =====================
const contactForm = document.getElementById('contact-form');
if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = contactForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Transmitting...';
    btn.disabled = true;
    try {
      const res = await fetch('contact_submit.php', { method: 'POST', body: new FormData(contactForm) });
      const data = await res.json();
      const box = document.getElementById('contact-alert');
      box.style.display = 'block';
      box.className = 'alert ' + (data.success ? 'alert-success' : 'alert-error');
      box.textContent = data.message;
      if (data.success) contactForm.reset();
    } catch (err) {
      const box = document.getElementById('contact-alert');
      box.style.display = 'block';
      box.className = 'alert alert-error';
      box.textContent = 'Network error. Please try again.';
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });
}