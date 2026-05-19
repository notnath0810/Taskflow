<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TaskFlow ✨ — Student Task Manager</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fraunces:ital,opsz,wght@0,9..144,700;1,9..144,800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    :root {
      --violet: #7c3aed;
      --pink:   #ec4899;
      --cyan:   #06b6d4;
      --lime:   #84cc16;
      --orange: #f97316;
      --yellow: #fbbf24;
      --bg:     #faf8ff;
      --text:   #1e1b4b;
      --muted:  #7c6dab;
      --white:  #ffffff;
      --grad-hero: linear-gradient(135deg, #7c3aed 0%, #ec4899 50%, #f97316 100%);
      --grad-ab:   linear-gradient(135deg, #7c3aed, #ec4899);
      --font-display: 'Fraunces', serif;
      --font-body:    'Nunito', sans-serif;
    }

    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text);
      overflow-x: hidden;
    }

    /* ── BLOBS ── */
    .blob {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: .3;
      pointer-events: none;
      z-index: 0;
      animation: blobDrift 12s ease-in-out infinite alternate;
    }
    .blob-1 { width: 500px; height: 500px; background: #c4b5fd; top: -100px; left: -100px; }
    .blob-2 { width: 400px; height: 400px; background: #fbcfe8; top: 40%; right: -120px; animation-delay: -3s; }
    .blob-3 { width: 350px; height: 350px; background: #a5f3fc; bottom: -80px; left: 30%; animation-delay: -6s; }
    @keyframes blobDrift {
      from { transform: translate(0,0) scale(1); }
      to   { transform: translate(30px,20px) scale(1.08); }
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 2.5rem;
      background: rgba(250,248,255,.85);
      backdrop-filter: blur(16px);
      border-bottom: 1.5px solid rgba(196,181,253,.3);
    }
    .nav-logo {
      display: flex; align-items: center; gap: .6rem;
      font-family: var(--font-body); font-weight: 900; font-size: 1.2rem;
      background: var(--grad-ab); -webkit-background-clip: text;
      -webkit-text-fill-color: transparent; background-clip: text; text-decoration: none;
    }
    .nav-logo-icon {
      width: 34px; height: 34px; background: var(--grad-ab); border-radius: 10px;
      display: grid; place-items: center; font-size: .95rem;
      box-shadow: 0 4px 14px rgba(124,58,237,.3);
      animation: float 3s ease-in-out infinite; flex-shrink: 0;
    }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }

    .nav-cta {
      background: var(--grad-ab); color: #fff; border: none; border-radius: 50px;
      padding: .55rem 1.5rem; font-family: var(--font-body); font-size: .88rem;
      font-weight: 800; cursor: pointer; text-decoration: none;
      box-shadow: 0 4px 14px rgba(124,58,237,.3);
      transition: transform .2s, box-shadow .2s;
    }
    .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,58,237,.4); }

    /* ── HERO ── */
    .hero {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      text-align: center; padding: 7rem 2rem 4rem;
    }
    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(196,181,253,.25); border: 1.5px solid rgba(196,181,253,.5);
      border-radius: 50px; padding: .4rem 1.1rem; font-size: .8rem; font-weight: 800;
      color: var(--violet); margin-bottom: 1.8rem; letter-spacing: .04em;
      text-transform: uppercase;
      animation: fadeUp .6s ease both;
    }
    .hero-title {
      font-family: var(--font-display); font-size: clamp(3rem,8vw,6rem);
      font-weight: 800; line-height: 1.05; letter-spacing: -.03em;
      margin-bottom: 1.5rem; animation: fadeUp .6s .1s ease both;
    }
    .hero-title .line-1 { display: block; color: var(--text); }
    .hero-title .line-2 {
      display: block; background: var(--grad-hero); background-size: 200% 100%;
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      animation: fadeUp .6s .1s ease both, gradFlow 4s ease infinite alternate;
    }
    @keyframes gradFlow { from{background-position:0% 50%} to{background-position:100% 50%} }
    .hero-sub {
      font-size: 1.15rem; font-weight: 600; color: var(--muted);
      max-width: 540px; margin: 0 auto 2.8rem; line-height: 1.7;
      animation: fadeUp .6s .2s ease both;
    }
    .hero-btns {
      display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;
      animation: fadeUp .6s .3s ease both;
    }
    .btn-hero-main {
      display: inline-flex; align-items: center; gap: .6rem;
      background: var(--grad-ab); color: #fff; text-decoration: none;
      padding: 1rem 2.2rem; border-radius: 16px; font-family: var(--font-body);
      font-size: 1.05rem; font-weight: 900;
      box-shadow: 0 6px 28px rgba(124,58,237,.4);
      transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
    }
    .btn-hero-main:hover { transform: translateY(-4px) scale(1.03); box-shadow: 0 12px 36px rgba(124,58,237,.5); }
    .btn-hero-ghost {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(255,255,255,.8); color: var(--violet); text-decoration: none;
      padding: 1rem 2rem; border-radius: 16px; font-family: var(--font-body);
      font-size: 1.05rem; font-weight: 800; border: 2px solid rgba(196,181,253,.6);
      transition: transform .2s, background .2s; backdrop-filter: blur(8px);
    }
    .btn-hero-ghost:hover { transform: translateY(-3px); background: #fff; }

    /* ── FLOATING TASK CARDS ── */
    .hero-deco {
      position: relative; margin-top: 4rem;
      animation: fadeUp .6s .45s ease both;
      width: 100%; max-width: 700px;
    }
    .deco-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .deco-card {
      background: rgba(255,255,255,.9); backdrop-filter: blur(12px);
      border: 2px solid rgba(196,181,253,.4); border-radius: 18px;
      padding: 1rem 1.4rem; display: flex; align-items: center; gap: 1rem;
      box-shadow: 0 8px 32px rgba(124,58,237,.1); text-align: left;
    }
    .deco-card-icon { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 1.3rem; flex-shrink: 0; }
    .deco-card-title { font-weight: 800; font-size: .92rem; color: var(--text); }
    .deco-card-sub   { font-size: .78rem; color: var(--muted); font-weight: 600; margin-top: 1px; }
    .deco-badge { padding: .28rem .75rem; border-radius: 50px; font-size: .72rem; font-weight: 800; white-space: nowrap; }
    .deco-card-1 { animation: cardFloat1 4s ease-in-out infinite; }
    .deco-card-2 { animation: cardFloat2 4.5s ease-in-out infinite; }
    .deco-card-3 { animation: cardFloat3 5s ease-in-out infinite; }
    .deco-card-4 { animation: cardFloat1 3.8s ease-in-out infinite reverse; }
    @keyframes cardFloat1 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-8px) rotate(.5deg)} }
    @keyframes cardFloat2 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-6px) rotate(-.4deg)} }
    @keyframes cardFloat3 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-10px) rotate(.3deg)} }

    /* ── STATS ── */
    .stats-strip {
      position: relative; z-index: 1;
      display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;
      padding: 3rem 2rem;
      background: rgba(255,255,255,.7); backdrop-filter: blur(10px);
      border-top: 1.5px solid rgba(196,181,253,.3);
      border-bottom: 1.5px solid rgba(196,181,253,.3);
    }
    .stat-item { text-align: center; }
    .stat-num {
      font-family: var(--font-display); font-size: 2.8rem; font-weight: 800;
      background: var(--grad-ab); -webkit-background-clip: text;
      -webkit-text-fill-color: transparent; background-clip: text; line-height: 1;
    }
    .stat-label { font-size: .85rem; font-weight: 700; color: var(--muted); margin-top: .3rem; }

    /* ── SHARED SECTION STYLES ── */
    .section-eyebrow {
      text-align: center; font-size: .78rem; font-weight: 900;
      text-transform: uppercase; letter-spacing: .12em; color: var(--violet); margin-bottom: .8rem;
    }
    .section-title {
      text-align: center; font-family: var(--font-display);
      font-size: clamp(1.8rem,4vw,2.8rem); font-weight: 800;
      letter-spacing: -.03em; color: var(--text); margin-bottom: .8rem;
    }
    .section-sub { text-align: center; color: var(--muted); font-size: .95rem; font-weight: 600; margin-bottom: 3.5rem; }

    /* ── FEATURES ── */
    .features {
      position: relative; z-index: 1;
      padding: 6rem 2rem; max-width: 1100px; margin: 0 auto;
    }
    .features-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem; }
    .feature-card {
      background: rgba(255,255,255,.9); border: 2px solid rgba(196,181,253,.25);
      border-radius: 20px; padding: 1.8rem; backdrop-filter: blur(8px);
      transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s, border-color .25s;
    }
    .feature-card:hover { transform: translateY(-6px); box-shadow: 0 16px 48px rgba(124,58,237,.15); border-color: rgba(196,181,253,.6); }
    .feature-icon {
      width: 54px; height: 54px; border-radius: 14px;
      display: grid; place-items: center; font-size: 1.6rem;
      margin-bottom: 1.2rem; box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .feature-title { font-size: 1.05rem; font-weight: 900; color: var(--text); margin-bottom: .5rem; }
    .feature-desc  { font-size: .88rem; color: var(--muted); font-weight: 600; line-height: 1.65; }

    /* ── HOW IT WORKS ── */
    .how {
      position: relative; z-index: 1; padding: 6rem 2rem;
      background: rgba(255,255,255,.6); backdrop-filter: blur(8px);
      border-top: 1.5px solid rgba(196,181,253,.2);
      border-bottom: 1.5px solid rgba(196,181,253,.2);
    }
    .how-inner { max-width: 960px; margin: 0 auto; }
    .steps { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.5rem; margin-top: 3.5rem; }
    .step { text-align: center; padding: 1.8rem 1.2rem; background: rgba(255,255,255,.9); border: 2px solid rgba(196,181,253,.2); border-radius: 20px; transition: transform .2s, box-shadow .2s; }
    .step:hover { transform: translateY(-4px); box-shadow: 0 12px 36px rgba(124,58,237,.1); }
    .step-num {
      width: 56px; height: 56px; border-radius: 50%;
      display: grid; place-items: center; font-family: var(--font-display);
      font-size: 1.4rem; font-weight: 800; color: #fff;
      margin: 0 auto 1.2rem; box-shadow: 0 6px 20px rgba(0,0,0,.15);
    }
    .step-icon { font-size: 1.8rem; margin-bottom: .8rem; display: block; }
    .step-title { font-weight: 900; font-size: 1rem; color: var(--text); margin-bottom: .5rem; }
    .step-desc  { font-size: .85rem; color: var(--muted); font-weight: 600; line-height: 1.6; }

    /* ── APP PREVIEW / CRUD DEMO ── */
    .preview-section {
      position: relative; z-index: 1; padding: 6rem 2rem;
      max-width: 1000px; margin: 0 auto;
    }
    .crud-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 3.5rem; }
    .crud-card {
      border-radius: 20px; padding: 1.8rem; border: 2px solid rgba(196,181,253,.2);
      transition: transform .2s, box-shadow .2s;
    }
    .crud-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(0,0,0,.1); }
    .crud-card-icon { font-size: 2rem; margin-bottom: 1rem; display: block; }
    .crud-card-label { font-size: .7rem; font-weight: 900; text-transform: uppercase; letter-spacing: .1em; margin-bottom: .4rem; }
    .crud-card-title { font-size: 1.1rem; font-weight: 900; color: var(--text); margin-bottom: .5rem; }
    .crud-card-desc  { font-size: .87rem; font-weight: 600; line-height: 1.6; }

    /* ── FAKE TASK ROW (Actions preview) ── */
    .preview-task-row {
      background: rgba(255,255,255,.95); border: 2px solid rgba(196,181,253,.3);
      border-radius: 16px; padding: 1rem 1.4rem;
      display: flex; align-items: center; gap: 1rem; margin-bottom: .8rem;
      box-shadow: 0 4px 16px rgba(124,58,237,.07);
      transition: transform .15s;
    }
    .preview-task-row:hover { transform: translateX(4px); }
    .preview-task-info { flex: 1; }
    .preview-task-title { font-weight: 800; font-size: .92rem; color: var(--text); }
    .preview-task-sub   { font-size: .76rem; color: var(--muted); font-weight: 600; margin-top: 2px; }
    .preview-actions    { display: flex; gap: .5rem; }
    .preview-btn {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .38rem .75rem; border-radius: 10px; font-family: var(--font-body);
      font-size: .78rem; font-weight: 800; cursor: default; border: none; white-space: nowrap;
    }
    .pbtn-edit     { background: #f0f0ff; color: var(--violet); border: 2px solid #e0d9ff; }
    .pbtn-done     { background: #dcfce7; color: #15803d; border: 2px solid #86efac; }
    .pbtn-delete   { background: #fff0f5; color: #e11d48; border: 2px solid #fecdd3; }
    .preview-badge { padding: .28rem .75rem; border-radius: 50px; font-size: .72rem; font-weight: 800; }

    /* ── CTA ── */
    .cta-section { position: relative; z-index: 1; padding: 7rem 2rem; text-align: center; }
    .cta-box {
      display: inline-block; background: var(--grad-hero); background-size: 200% 200%;
      animation: gradFlow 4s ease infinite alternate;
      border-radius: 28px; padding: 4rem 3rem; max-width: 700px; width: 100%;
      box-shadow: 0 20px 60px rgba(124,58,237,.3); position: relative; overflow: hidden;
    }
    .cta-box::before {
      content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 60%);
      animation: rotateSlow 8s linear infinite;
    }
    @keyframes rotateSlow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    .cta-title { font-family: var(--font-display); font-size: clamp(1.8rem,5vw,2.8rem); font-weight: 800; color: #fff; margin-bottom: .8rem; position: relative; }
    .cta-sub { color: rgba(255,255,255,.85); font-size: 1rem; font-weight: 600; margin-bottom: 2.2rem; position: relative; }
    .btn-cta-white {
      display: inline-flex; align-items: center; gap: .6rem;
      background: #fff; color: var(--violet); text-decoration: none;
      padding: 1rem 2.5rem; border-radius: 14px; font-family: var(--font-body);
      font-size: 1.05rem; font-weight: 900; box-shadow: 0 8px 28px rgba(0,0,0,.15);
      transition: transform .22s cubic-bezier(.34,1.56,.64,1); position: relative;
    }
    .btn-cta-white:hover { transform: translateY(-4px) scale(1.04); box-shadow: 0 14px 38px rgba(0,0,0,.22); }

    /* ── FOOTER ── */
    footer {
      position: relative; z-index: 1; text-align: center; padding: 2rem;
      font-size: .82rem; font-weight: 700; color: var(--muted);
      border-top: 1.5px solid rgba(196,181,253,.2);
    }
    footer span { background: var(--grad-ab); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 900px) {
      .features-grid { grid-template-columns: 1fr 1fr; }
      .steps         { grid-template-columns: 1fr 1fr; }
      .crud-grid     { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      nav { padding: .8rem 1.2rem; }
      .deco-grid { grid-template-columns: 1fr; }
      .features-grid { grid-template-columns: 1fr; }
      .steps { grid-template-columns: 1fr; }
      .cta-box { padding: 2.5rem 1.5rem; }
      .hero-btns { flex-direction: column; align-items: center; }
    }
  </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- ── NAV ── -->
<nav>
  <a href="landing.php" class="nav-logo">
    <div class="nav-logo-icon">✓</div>
    TaskFlow ✨
  </a>
  <a href="index.php" class="nav-cta">Open App →</a>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-eyebrow">🎒 Made for students, by students</div>
  <h1 class="hero-title">
    <span class="line-1">Stop forgetting.</span>
    <span class="line-2">Start crushing it.</span>
  </h1>
  <p class="hero-sub">TaskFlow helps you track every assignment, beat every deadline, and actually feel good about your workload. 🎯</p>
  <div class="hero-btns">
    <a href="index.php" class="btn-hero-main">🚀 Get Started — It's Free</a>
    <a href="#features" class="btn-hero-ghost">✨ See Features</a>
  </div>

  <!-- Floating task cards -->
  <div class="hero-deco">
    <div class="deco-grid">
      <div class="deco-card deco-card-1">
        <div class="deco-card-icon" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)">📝</div>
        <div style="flex:1">
          <div class="deco-card-title">Emerging Technology Report</div>
          <div class="deco-card-sub">Due in 2 days · ITPC CPE 3205</div>
        </div>
        <div class="deco-badge" style="background:linear-gradient(135deg,#ffe4e6,#fecdd3);color:#be123c;border:1.5px solid #fda4af">🔴 High</div>
      </div>
      <div class="deco-card deco-card-2">
        <div class="deco-card-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0)">✅</div>
        <div style="flex:1">
          <div class="deco-card-title">Embedded Systems Assignment</div>
          <div class="deco-card-sub">Completed · CPE 3201</div>
        </div>
        <div class="deco-badge" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;border:1.5px solid #86efac">✅ Done</div>
      </div>
      <div class="deco-card deco-card-3">
        <div class="deco-card-icon" style="background:linear-gradient(135deg,#fff7ed,#fed7aa)">⏳</div>
        <div style="flex:1">
          <div class="deco-card-title">Read Chapter 5</div>
          <div class="deco-card-sub">Ongoing · CPE 3204</div>
        </div>
        <div class="deco-badge" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);color:#c2410c;border:1.5px solid #fdba74">⏳ Ongoing</div>
      </div>
      <div class="deco-card deco-card-4">
        <div class="deco-card-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a)">🔬</div>
        <div style="flex:1">
          <div class="deco-card-title">Research Report Revisions</div>
          <div class="deco-card-sub">Due Friday · CPE 3207L</div>
        </div>
        <div class="deco-badge" style="background:linear-gradient(135deg,#fef9c3,#fde68a);color:#b45309;border:1.5px solid #fcd34d">🟡 Medium</div>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURES ── -->
<section class="features" id="features">
  <div class="section-eyebrow">⚡ Features</div>
  <h2 class="section-title">Everything you need to stay on track</h2>
  <p class="section-sub">Simple, colorful, and built for busy students like you.</p>

  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)">➕</div>
      <div class="feature-title">Add Tasks Fast</div>
      <div class="feature-desc">Quickly log assignments with a title, subject, description, priority, and due date — all in one clean form.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0)">✅</div>
      <div class="feature-title">Track Your Progress</div>
      <div class="feature-desc">Mark tasks as Pending, Ongoing, or Completed. Watch your done pile grow and feel that sweet satisfaction!</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#ffe4e6,#fecdd3)">🔴</div>
      <div class="feature-title">Priority Levels</div>
      <div class="feature-desc">High, Medium, or Low — always know what to tackle first so nothing urgent slips through the cracks.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a)">📅</div>
      <div class="feature-title">Deadline Alerts</div>
      <div class="feature-desc">Due dates glow red when overdue and yellow when urgent. You'll never be surprised by a deadline again.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#a5f3fc,#67e8f9)">🔍</div>
      <div class="feature-title">Search & Filter</div>
      <div class="feature-desc">Instantly filter by status, priority, or search by keyword. Find any task in seconds — no endless scrolling.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:linear-gradient(135deg,#fed7aa,#fdba74)">↕️</div>
      <div class="feature-title">Smart Sorting</div>
      <div class="feature-desc">Sort by due date, priority, status, or title — ascending or descending. Two different ways to rearrange your view.</div>
    </div>
  </div>
</section>

<!-- ── ACTIONS PREVIEW (new section!) ── -->
<section style="position:relative;z-index:1;padding:6rem 2rem;background:rgba(255,255,255,.6);backdrop-filter:blur(8px);border-top:1.5px solid rgba(196,181,253,.2);border-bottom:1.5px solid rgba(196,181,253,.2)">
  <div style="max-width:880px;margin:0 auto">
    <div class="section-eyebrow">⚙️ Actions</div>
    <h2 class="section-title">Full control over every task</h2>
    <p class="section-sub">Edit, complete, or remove any task in one click. It's that easy.</p>

    <!-- live-looking task rows -->
    <div class="preview-task-row">
      <div style="font-size:1.4rem">📝</div>
      <div class="preview-task-info">
        <div class="preview-task-title">Finish Emerging Technology Report</div>
        <div class="preview-task-sub">CPE 3205 · Due tomorrow · High Priority</div>
      </div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#ffe4e6,#fecdd3);color:#be123c;border:1.5px solid #fda4af">🔴 High</div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#6d28d9;border:1.5px solid #c4b5fd;margin-left:.3rem">📋 Pending</div>
      <div class="preview-actions">
        <div class="preview-btn pbtn-edit">✏️ Edit</div>
        <div class="preview-btn pbtn-done">✅ Complete</div>
        <div class="preview-btn pbtn-delete">🗑 Delete</div>
      </div>
    </div>

    <div class="preview-task-row">
      <div style="font-size:1.4rem">📐</div>
      <div class="preview-task-info">
        <div class="preview-task-title">Embedded Systems Assignment #3</div>
        <div class="preview-task-sub">CPE 3201 · Due Friday · Medium Priority</div>
      </div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#fef9c3,#fde68a);color:#b45309;border:1.5px solid #fcd34d">🟡 Medium</div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);color:#c2410c;border:1.5px solid #fdba74;margin-left:.3rem">⏳ Ongoing</div>
      <div class="preview-actions">
        <div class="preview-btn pbtn-edit">✏️ Edit</div>
        <div class="preview-btn pbtn-done">✅ Complete</div>
        <div class="preview-btn pbtn-delete">🗑 Delete</div>
      </div>
    </div>

    <div class="preview-task-row">
      <div style="font-size:1.4rem">📖</div>
      <div class="preview-task-info">
        <div class="preview-task-title">Research Report Revisions </div>
        <div class="preview-task-sub">CPE 3207L · No due date · Low Priority</div>
      </div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;border:1.5px solid #86efac">🟢 Low</div>
      <div class="preview-badge" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;border:1.5px solid #86efac;margin-left:.3rem">✅ Completed</div>
      <div class="preview-actions">
        <div class="preview-btn pbtn-edit">✏️ Edit</div>
        <div class="preview-btn pbtn-done">✅ Complete</div>
        <div class="preview-btn pbtn-delete">🗑 Delete</div>
      </div>
    </div>

    <!-- CRUD labels -->
    <div style="display:flex;gap:1rem;margin-top:1.8rem;flex-wrap:wrap;justify-content:center">
      <div style="display:flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#ede9fe,#ddd6fe);border:1.5px solid #c4b5fd;border-radius:50px;padding:.4rem 1rem;font-size:.82rem;font-weight:800;color:#6d28d9">➕ CREATE — Add new task</div>
      <div style="display:flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#a5f3fc,#67e8f9);border:1.5px solid #67e8f9;border-radius:50px;padding:.4rem 1rem;font-size:.82rem;font-weight:800;color:#0e7490">📋 READ — View all tasks</div>
      <div style="display:flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#fef9c3,#fde68a);border:1.5px solid #fcd34d;border-radius:50px;padding:.4rem 1rem;font-size:.82rem;font-weight:800;color:#b45309">✏️ UPDATE — Edit task</div>
      <div style="display:flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#ffe4e6,#fecdd3);border:1.5px solid #fda4af;border-radius:50px;padding:.4rem 1rem;font-size:.82rem;font-weight:800;color:#be123c">🗑 DELETE — Remove task</div>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="how">
  <div class="how-inner">
    <div class="section-eyebrow">🗺️ How It Works</div>
    <h2 class="section-title">Four steps to inbox zero</h2>
    <p class="section-sub">It's genuinely this simple — no account needed, just open and go!</p>

    <div class="steps">
      <div class="step">
        <div class="step-num" style="background:linear-gradient(135deg,#7c3aed,#ec4899)">1</div>
        <span class="step-icon">📝</span>
        <div class="step-title">Add your task</div>
        <div class="step-desc">Fill in the title, subject, priority, and due date. Hit the big purple button and you're done.</div>
      </div>
      <div class="step">
        <div class="step-num" style="background:linear-gradient(135deg,#06b6d4,#7c3aed)">2</div>
        <span class="step-icon">⏳</span>
        <div class="step-title">Start working</div>
        <div class="step-desc">Click Edit and change the status to Ongoing when you begin. Everyone can see what's in progress.</div>
      </div>
      <div class="step">
        <div class="step-num" style="background:linear-gradient(135deg,#f97316,#fbbf24)">3</div>
        <span class="step-icon">✅</span>
        <div class="step-title">Mark complete</div>
        <div class="step-desc">Hit the ✅ Complete button when you're done. Watch your progress stats light up green!</div>
      </div>
      <div class="step">
        <div class="step-num" style="background:linear-gradient(135deg,#84cc16,#06b6d4)">4</div>
        <span class="step-icon">🔍</span>
        <div class="step-title">Sort & review</div>
        <div class="step-desc">Use filters and sorting to review what's urgent, what's done, and what needs attention next.</div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
  <div class="cta-box">
    <h2 class="cta-title">Ready to get things done? 🚀</h2>
    <p class="cta-sub">Your tasks are waiting. Let's clear that list together.</p>
    <a href="index.php" class="btn-cta-white">✨ Open TaskFlow Now</a>
  </div>
</section>

<!-- ── FOOTER ── --> 
<footer>
  Built with 💜 by <span>TaskFlow Team</span> · Powered by PHP + MySQL + XAMPP
  <br>
  Nathaniel M. Padas || Alexa Janine Gomez
</footer>

</body>
</html>