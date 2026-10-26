<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($settings['full_name'] ?? 'ML Engineer') ?> | Portfolio</title>
<meta name="description" content="<?= e($settings['tagline'] ?? 'Machine Learning Engineer') ?> — portfolio and project vault of <?= e($settings['full_name'] ?? '') ?>.">
<meta property="og:title" content="<?= e($settings['full_name'] ?? '') ?> — Machine Learning Engineer">
<meta property="og:description" content="<?= e($settings['about_me'] ?? '') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div id="loader">
  <div class="loader-ring"></div>
  <div class="loader-text">Loading <span>portfolio</span>…</div>
</div>

<div class="bg-gradient"></div>
<canvas id="data-stream"></canvas>

<div id="topbar">
  <div class="brand"><?= e($settings['full_name'] ?? 'ML') ?><span class="dot">.</span></div>
</div>