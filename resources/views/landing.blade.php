<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script>
    document.documentElement.setAttribute('data-theme','dark');document.documentElement.style.colorScheme='dark';
  </script>
  <title>FitGo — Your AI Keto & Fitness Coach</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet" />
  <link href="{{ asset('css/fitgo-theme.css') }}" rel="stylesheet" />
  <style>
    /* Page-specific (tokens: public/css/fitgo-theme.css) */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--ff-body);
      background: var(--dark);
      color: var(--text);
      overflow-x: hidden;
    }
    h1,h2,h3,h4,h5 { font-family: var(--ff-head); color: var(--heading); line-height: 1.15; }
    a { text-decoration: none; color: inherit; }

    /* ─── NOISE GRAIN OVERLAY ──────────────────────────── */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: var(--noise-opacity);
    }

    /* ─── NAVBAR ────────────────────────────────────────── */
    .navbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 999;
      padding: 20px 0;
      transition: all .35s ease;
    }
    .navbar.stuck {
      padding: 14px 0;
      background: var(--navbar-stuck-bg);
      backdrop-filter: blur(24px);
      border-bottom: 1px solid var(--border);
    }
    .nav-brand {
      font-family: var(--ff-head);
      font-size: 1.55rem;
      font-weight: 800;
      color: var(--heading);
      letter-spacing: -0.5px;
    }
    .nav-brand span { color: var(--g); }
    .nav-link-custom {
      color: var(--muted);
      font-weight: 500;
      font-size: .9rem;
      letter-spacing: .3px;
      transition: color .2s;
      padding: 6px 14px;
    }
    .nav-link-custom:hover { color: var(--heading); }

    /* ─── BUTTONS ───────────────────────────────────────── */
    .btn-g {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, var(--g), var(--g2));
      color: #fff;
      font-family: var(--ff-head);
      font-weight: 700; font-size: .95rem;
      padding: 13px 30px;
      border-radius: var(--r3);
      border: none; cursor: pointer;
      box-shadow: 0 4px 24px rgba(34,197,94,.35);
      transition: all .3s ease;
    }
    .btn-g:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 34px rgba(34,197,94,.5);
      color: #fff;
    }
    .btn-g-lg {
      padding: 17px 42px;
      font-size: 1.08rem;
      border-radius: var(--r3);
    }
    .btn-outline-g {
      display: inline-flex; align-items: center; gap: 8px;
      background: transparent;
      color: var(--g);
      font-family: var(--ff-head);
      font-weight: 600; font-size: .9rem;
      padding: 11px 26px;
      border-radius: var(--r3);
      border: 1.5px solid var(--g);
      cursor: pointer;
      transition: all .25s;
    }
    .btn-outline-g:hover { background: var(--g); color: #fff; transform: translateY(-1px); }

    /* ─── HERO ──────────────────────────────────────────── */
    .hero {
      min-height: 100vh;
      display: flex; align-items: center;
      position: relative;
      overflow: hidden;
      padding-top: 96px;
      padding-bottom: 56px;
    }
    /* radial green glow behind hero */
    .hero::after {
      content: '';
      position: absolute;
      top: -120px; left: 50%;
      transform: translateX(-50%);
      width: 900px; height: 700px;
      background: radial-gradient(ellipse at center, rgba(34,197,94,.12) 0%, transparent 72%);
      pointer-events: none;
    }
    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(34,197,94,.12);
      border: 1px solid rgba(34,197,94,.3);
      color: var(--g3);
      font-size: .82rem; font-weight: 600;
      letter-spacing: .8px; text-transform: uppercase;
      padding: 7px 18px; border-radius: var(--r3);
      margin-bottom: 24px;
      animation: fadeUp .7s ease both;
    }
    .hero-title {
      font-size: clamp(2.8rem, 6vw, 5.2rem);
      font-weight: 800;
      line-height: 1.07;
      letter-spacing: -1.5px;
      animation: fadeUp .75s .1s ease both;
    }
    .hero-title .green-word {
      color: var(--g);
      position: relative;
      display: inline-block;
    }
    .hero-title .green-word::after {
      content: '';
      position: absolute;
      bottom: 4px; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--g), transparent);
      border-radius: 2px;
    }
    .hero-sub {
      font-size: 1.15rem; color: var(--muted);
      max-width: 480px; line-height: 1.7;
      margin: 22px 0 36px;
      animation: fadeUp .8s .2s ease both;
    }
    .hero-actions { animation: fadeUp .85s .3s ease both; }
    .hero-trust {
      display: flex; align-items: center; gap: 16px;
      margin-top: 40px;
      animation: fadeUp .9s .4s ease both;
    }
    .trust-avatars {
      display: flex;
    }
    .trust-avatars span {
      width: 36px; height: 36px;
      border-radius: 50%;
      border: 2px solid var(--dark);
      background: linear-gradient(135deg, var(--g2), var(--g));
      display: flex; align-items: center; justify-content: center;
      font-size: .7rem; font-weight: 700; color: var(--heading);
      margin-left: -10px;
    }
    .trust-avatars span:first-child { margin-left: 0; }
    .trust-text { font-size: .85rem; color: var(--muted); }
    .trust-text strong { color: var(--heading); }

    /* hero visual right side */
    .hero-card-stack {
      position: relative; height: 520px;
      margin-top: 8px;
      animation: fadeUp .9s .2s ease both;
    }
    .hcard {
      position: absolute;
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: var(--r2);
      padding: 22px 26px;
      box-shadow: 0 20px 60px rgba(0,0,0,.5);
      backdrop-filter: blur(8px);
    }
    .hcard-main {
      top: 30px; right: 0;
      width: 320px;
      background: linear-gradient(135deg, #0f2218, #0a1a10);
      border-color: rgba(34,197,94,.2);
    }
    .hcard-stat {
      bottom: 30px; left: 0;
      width: 200px;
      animation: float 4s ease-in-out infinite;
    }
    .hcard-badge {
      top: 180px; left: -20px;
      width: 175px;
      padding: 16px 18px;
      animation: float 5s 1s ease-in-out infinite;
    }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

    .hcard-title { font-size: .7rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 12px; }
    .hcard-value { font-family: var(--ff-head); font-size: 1.9rem; font-weight: 800; color: var(--heading); }
    .hcard-value .unit { font-size: 1rem; color: var(--muted); font-weight: 400; }
    .hcard-sub { font-size: .78rem; color: var(--g); margin-top: 4px; }

    .macro-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .macro-label { font-size: .78rem; color: var(--muted); }
    .macro-bar { height: 6px; border-radius: 3px; margin-top: 3px; }
    .macro-val { font-size: .78rem; font-weight: 600; color: var(--heading); }

    /* meal items inside main hero card */
    .meal-item {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .meal-item:last-child { border-bottom: none; padding-bottom: 0; }
    .meal-icon {
      width: 42px; height: 42px; border-radius: 10px;
      background: rgba(34,197,94,.12);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
    }
    .meal-info { flex: 1; }
    .meal-name { font-size: .88rem; font-weight: 600; color: var(--heading); }
    .meal-cal { font-size: .75rem; color: var(--muted); }
    .meal-check {
      width: 22px; height: 22px; border-radius: 50%;
      background: linear-gradient(135deg, var(--g), var(--g2));
      display: flex; align-items: center; justify-content: center;
      font-size: .65rem; color: var(--heading);
    }

    /* ─── ANIMATED STATS BAND ───────────────────────────── */
    .stats-band {
      background: var(--dark2);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      padding: 48px 0;
    }
    .stat-item { text-align: center; }
    .stat-num {
      font-family: var(--ff-head);
      font-size: 2.6rem; font-weight: 800;
      background: linear-gradient(135deg, var(--g), var(--g3));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .stat-label { font-size: .88rem; color: var(--muted); margin-top: 4px; }

    /* ─── FEATURES ──────────────────────────────────────── */
    .section { padding: 100px 0; }
    .section-eyebrow {
      font-size: .78rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1.5px;
      color: var(--g); margin-bottom: 14px;
    }
    .section-title {
      font-size: clamp(1.9rem, 3.5vw, 2.8rem);
      font-weight: 800; letter-spacing: -.5px;
    }
    .section-sub { color: var(--muted); font-size: 1rem; max-width: 540px; margin-top: 14px; line-height: 1.7; }

    .feat-card {
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: var(--r2);
      padding: 32px 28px;
      height: 100%;
      transition: all .35s ease;
      position: relative;
      overflow: hidden;
    }
    .feat-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(34,197,94,.07), transparent);
      opacity: 0;
      transition: opacity .35s;
    }
    .feat-card:hover { border-color: rgba(34,197,94,.35); transform: translateY(-4px); box-shadow: 0 20px 50px rgba(0,0,0,.4); }
    .feat-card:hover::before { opacity: 1; }
    .feat-icon {
      width: 52px; height: 52px; border-radius: 14px;
      background: rgba(34,197,94,.12);
      border: 1px solid rgba(34,197,94,.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; margin-bottom: 20px;
    }
    .feat-title { font-family: var(--ff-head); font-size: 1.12rem; font-weight: 700; margin-bottom: 10px; }
    .feat-desc { font-size: .9rem; color: var(--muted); line-height: 1.65; }

    /* ─── HOW IT WORKS ──────────────────────────────────── */
    .hiw-bg { background: var(--dark2); }
    .step-line {
      position: relative;
      padding-left: 70px;
      padding-bottom: 52px;
    }
    .step-line::before {
      content: '';
      position: absolute;
      left: 24px; top: 52px;
      width: 2px; bottom: 0;
      background: linear-gradient(180deg, var(--g) 0%, transparent 100%);
      opacity: .3;
    }
    .step-line:last-child::before { display: none; }
    .step-num {
      position: absolute; left: 0; top: 0;
      width: 48px; height: 48px; border-radius: 50%;
      background: linear-gradient(135deg, var(--g), var(--g2));
      display: flex; align-items: center; justify-content: center;
      font-family: var(--ff-head); font-weight: 800; font-size: 1rem; color: var(--heading);
      box-shadow: 0 0 0 6px rgba(34,197,94,.12);
    }
    .step-title { font-family: var(--ff-head); font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
    .step-desc { font-size: .9rem; color: var(--muted); line-height: 1.65; }

    /* ─── QUIZ CTA STRIP ────────────────────────────────── */
    .quiz-strip {
      background: linear-gradient(135deg, #0a1f13 0%, #061410 100%);
      border: 1px solid rgba(34,197,94,.2);
      border-radius: var(--r2);
      padding: 60px;
      position: relative;
      overflow: hidden;
    }
    .quiz-strip::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 300px; height: 300px;
      background: radial-gradient(circle, rgba(34,197,94,.15), transparent 70%);
    }
    .quiz-strip-title { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; letter-spacing: -.5px; }
    .quiz-strip-sub { color: var(--muted); font-size: 1rem; margin: 14px 0 0; max-width: 460px; }

    /* ─── TESTIMONIALS ──────────────────────────────────── */
    .testi-card {
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: var(--r2);
      padding: 30px 28px;
      height: 100%;
      transition: all .3s;
    }
    .testi-card:hover { border-color: rgba(34,197,94,.25); transform: translateY(-3px); }
    .testi-stars { color: #fbbf24; font-size: .85rem; margin-bottom: 14px; }
    .testi-text { font-size: .92rem; color: var(--text); line-height: 1.7; margin-bottom: 22px; font-style: italic; }
    .testi-author { display: flex; align-items: center; gap: 12px; }
    .testi-avatar {
      width: 42px; height: 42px; border-radius: 50%;
      background: linear-gradient(135deg, var(--g2), var(--g));
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .85rem; color: var(--heading);
    }
    .testi-name { font-weight: 600; font-size: .9rem; }
    .testi-loc { font-size: .75rem; color: var(--muted); }
    .testi-loss {
      margin-left: auto;
      background: rgba(34,197,94,.12);
      border: 1px solid rgba(34,197,94,.25);
      color: var(--g);
      font-size: .75rem; font-weight: 700;
      padding: 4px 10px; border-radius: var(--r3);
    }

    /* ─── FOOTER ────────────────────────────────────────── */
    .footer {
      background: var(--dark2);
      border-top: 1px solid var(--border);
      padding: 60px 0 32px;
    }
    .footer-brand-name {
      font-family: var(--ff-head);
      font-size: 1.4rem; font-weight: 800;
    }
    .footer-brand-name span { color: var(--g); }
    .footer-desc { font-size: .88rem; color: var(--muted); margin-top: 10px; max-width: 260px; line-height: 1.6; }
    .footer-heading { font-family: var(--ff-head); font-size: .85rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--heading); margin-bottom: 16px; }
    .footer-link { display: block; font-size: .88rem; color: var(--muted); margin-bottom: 10px; transition: color .2s; }
    .footer-link:hover { color: var(--g); }
    .footer-copy { font-size: .8rem; color: var(--muted); border-top: 1px solid var(--border); margin-top: 48px; padding-top: 24px; }

    /* ─── ANIMATIONS ────────────────────────────────────── */
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(22px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity .65s ease, transform .65s ease; }
    .reveal.in { opacity: 1; transform: none; }

    /* ─── UTILITIES ─────────────────────────────────────── */
    .divider-g {
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--g), transparent);
      opacity: .25;
      margin: 0;
    }
    .pill-tag {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(34,197,94,.1);
      border: 1px solid rgba(34,197,94,.2);
      color: var(--g3); font-size: .78rem; font-weight: 600;
      padding: 5px 14px; border-radius: var(--r3);
    }

    /* ─── RESPONSIVE ────────────────────────────────────── */
    @media (max-width: 768px) {
      .hero { padding-top: 120px; text-align: center; }
      .hero-sub { margin-left: auto; margin-right: auto; }
      .hero-trust { justify-content: center; }
      .hero-card-stack { display: none; }
      .quiz-strip { padding: 36px 24px; }
      .quiz-strip .d-flex { flex-direction: column; gap: 24px; }
    }
  </style>
</head>
<body>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="navbar" id="mainNav">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between w-100">
      <a href="{{ route('home') }}" class="nav-brand">Fit<span>Go</span></a>

      <div class="d-none d-md-flex align-items-center gap-1">
        <a href="#features" class="nav-link-custom">Features</a>
        <a href="#how-it-works" class="nav-link-custom">How It Works</a>
        <a href="#results" class="nav-link-custom">Results</a>
        <a href="#about" class="nav-link-custom">About</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-outline-g d-none d-md-inline-flex">My Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn-outline-g d-none d-md-inline-flex">Log in</a>
        @endauth
        <a href="{{ route('quiz') }}" class="btn-g">Take the Quiz <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>
</nav>

<!-- ══════════════ HERO ══════════════ -->
<section class="hero">
  <div class="container position-relative" style="z-index:1">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6">
        <div class="hero-eyebrow">
          <i class="bi bi-stars"></i> AI-Powered Diet & Fitness
        </div>
        <h1 class="hero-title">
          Kickstart Your <span class="green-word">Keto</span><br>
          Weight Loss Journey
        </h1>
        <p class="hero-sub">
          Discover the power of ketosis. Personalised meal plans, adaptive workouts, and AI guidance — all tailored to <em>you</em>.
        </p>
        <div class="hero-actions d-flex flex-wrap gap-3">
          <a href="{{ route('quiz') }}" class="btn-g btn-g-lg">
            Start Free Quiz <i class="bi bi-arrow-right-circle-fill"></i>
          </a>
          <button class="btn-outline-g btn-g-lg" onclick="document.getElementById('features').scrollIntoView({behavior:'smooth'})">
            <i class="bi bi-play-circle"></i> See How It Works
          </button>
        </div>
        <div class="hero-trust">
          <div class="trust-avatars">
            <span>AK</span><span>VH</span><span>BD</span><span>MR</span>
          </div>
          <div class="trust-text">
            Joined by <strong>300,000+</strong> people who transformed their lives
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-card-stack">
          <!-- Main card - Today's Meals -->
          <div class="hcard hcard-main">
            <div class="d-flex align-items-center justify-content-between mb-16" style="margin-bottom:16px">
              <div>
                <div class="hcard-title">Today's Meal Plan</div>
                <div style="font-size:.78rem;color:var(--g)">1,840 kcal · On track ✓</div>
              </div>
              <div class="pill-tag"><i class="bi bi-check-circle-fill"></i> Day 4</div>
            </div>
            <div class="meal-item">
              <div class="meal-icon">🥑</div>
              <div class="meal-info">
                <div class="meal-name">Avocado Egg Bowl</div>
                <div class="meal-cal">Breakfast · 420 kcal</div>
              </div>
              <div class="meal-check"><i class="bi bi-check"></i></div>
            </div>
            <div class="meal-item">
              <div class="meal-icon">🥗</div>
              <div class="meal-info">
                <div class="meal-name">Grilled Chicken Salad</div>
                <div class="meal-cal">Lunch · 540 kcal</div>
              </div>
              <div class="meal-check"><i class="bi bi-check"></i></div>
            </div>
            <div class="meal-item">
              <div class="meal-icon">🥩</div>
              <div class="meal-info">
                <div class="meal-name">Ribeye + Asparagus</div>
                <div class="meal-cal">Dinner · 680 kcal</div>
              </div>
              <div style="width:22px;height:22px;border-radius:50%;border:1.5px solid rgba(255,255,255,.2);"></div>
            </div>
            <!-- Macros -->
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.06)">
              <div class="macro-row">
                <div>
                  <div class="macro-label">Fats</div>
                  <div class="macro-bar" style="width:80px;background:linear-gradient(90deg,var(--g),var(--g2));width:74%"></div>
                </div>
                <div class="macro-val">74%</div>
              </div>
              <div class="macro-row" style="margin-top:8px">
                <div>
                  <div class="macro-label">Protein</div>
                  <div class="macro-bar" style="background:rgba(249,115,22,.6);width:22%"></div>
                </div>
                <div class="macro-val" style="color:var(--o)">22%</div>
              </div>
            </div>
          </div>

          <!-- Stat card -->
          <div class="hcard hcard-stat">
            <div class="hcard-title">Weight Lost</div>
            <div class="hcard-value">−11.2 <span class="unit">lbs</span></div>
            <div class="hcard-sub"><i class="bi bi-arrow-down-right"></i> In just 4 weeks</div>
          </div>

          <!-- Badge card -->
          <div class="hcard hcard-badge">
            <div style="font-size:1.6rem;margin-bottom:6px">🔥</div>
            <div style="font-size:.82rem;font-weight:700;color:var(--heading)">Ketosis Active</div>
            <div style="font-size:.72rem;color:var(--g);margin-top:3px">Fat burning mode ON</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ STATS BAND ══════════════ -->
<div class="stats-band">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3 reveal">
        <div class="stat-num">300K+</div>
        <div class="stat-label">Active Members</div>
      </div>
      <div class="col-6 col-md-3 reveal">
        <div class="stat-num">93%</div>
        <div class="stat-label">Success Rate</div>
      </div>
      <div class="col-6 col-md-3 reveal">
        <div class="stat-num">500+</div>
        <div class="stat-label">Keto Recipes</div>
      </div>
      <div class="col-6 col-md-3 reveal">
        <div class="stat-num">4.9★</div>
        <div class="stat-label">Average Rating</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════ FEATURES ══════════════ -->
<section class="section" id="features">
  <div class="container">
    <div class="row gy-4 mb-5">
      <div class="col-lg-5 reveal">
        <div class="section-eyebrow">Everything You Need</div>
        <h2 class="section-title">One App. Total Transformation.</h2>
        <p class="section-sub">We don't just give you a diet — we give you a complete system with AI-powered guidance every step of the way.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">🤖</div>
          <div class="feat-title">AI-Personalised Plans</div>
          <div class="feat-desc">Answer a short quiz and get a diet + workout plan built specifically for your body, goals, and lifestyle.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">🥑</div>
          <div class="feat-title">500+ Keto Recipes</div>
          <div class="feat-desc">Delicious, easy-to-make recipes with full macro breakdowns — filtered by your dietary preferences.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">🏋️</div>
          <div class="feat-title">Home & Gym Workouts</div>
          <div class="feat-desc">Structured workout programs for every fitness level — whether you train at home or the gym.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">📊</div>
          <div class="feat-title">Progress Tracking</div>
          <div class="feat-desc">Track your weight, measurements, and milestones with beautiful visual charts that keep you motivated.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">💬</div>
          <div class="feat-title">Nutritionist Chat</div>
          <div class="feat-desc">Get real answers from certified nutrition consultants dedicated to your success — available in-app.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="feat-card">
          <div class="feat-icon">🛒</div>
          <div class="feat-title">Smart Grocery List</div>
          <div class="feat-desc">Auto-generated shopping lists based on your weekly meal plan — organised by store section.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="divider-g"></div>

<!-- ══════════════ HOW IT WORKS ══════════════ -->
<section class="section hiw-bg" id="how-it-works">
  <div class="container">
    <div class="row gy-5 align-items-center">
      <div class="col-lg-5 reveal">
        <div class="section-eyebrow">Simple Process</div>
        <h2 class="section-title">From Quiz to Results in Minutes</h2>
        <p class="section-sub">No complicated setup. Just answer a few questions and your personalised plan is ready.</p>
        <a href="{{ route('quiz') }}" class="btn-g mt-4">
          Start Now — It's Free <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div class="col-lg-6 offset-lg-1 reveal">
        <div class="step-line">
          <div class="step-num">1</div>
          <div class="step-title">Take the Personalised Quiz</div>
          <div class="step-desc">Answer questions about your gender, body, lifestyle, goals, and food preferences. Takes under 3 minutes.</div>
        </div>
        <div class="step-line">
          <div class="step-num">2</div>
          <div class="step-title">Get Your Custom Plan</div>
          <div class="step-desc">Our AI generates a tailored keto diet plan and workout schedule — specific to your exact needs.</div>
        </div>
        <div class="step-line">
          <div class="step-num">3</div>
          <div class="step-title">Follow & Track Progress</div>
          <div class="step-desc">Use your dashboard to follow daily meals, log workouts, and watch the pounds drop week by week.</div>
        </div>
        <div class="step-line">
          <div class="step-num">4</div>
          <div class="step-title">Adjust & Optimise</div>
          <div class="step-desc">As you progress, your plan adapts. Our AI and nutritionists fine-tune your program for continued results.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ QUIZ CTA ══════════════ -->
<section class="section">
  <div class="container reveal">
    <div class="quiz-strip">
      <div class="d-flex align-items-center justify-content-between gap-4 position-relative" style="z-index:1">
        <div>
          <div class="section-eyebrow">Ready to Start?</div>
          <div class="quiz-strip-title">Don't postpone a<br>healthier you.</div>
          <p class="quiz-strip-sub">Take our free 3-minute quiz and get your personalised plan delivered to your inbox instantly.</p>
        </div>
        <div class="flex-shrink-0">
          <a href="{{ route('quiz') }}" class="btn-g btn-g-lg d-flex align-items-center gap-2" style="white-space:nowrap">
            <i class="bi bi-clipboard2-pulse-fill"></i> Take Free Quiz
          </a>
          <div style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:10px">No credit card required</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ TESTIMONIALS ══════════════ -->
<section class="section" id="results" style="padding-top:0">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="section-eyebrow">Real People. Real Results.</div>
      <h2 class="section-title">Success Stories</h2>
      <p class="section-sub mx-auto">Join 300K+ people who lost weight and gained healthy habits with FitGo.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 reveal">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"I was sceptical at first — another diet app? But the AI plan was genuinely different. Tailored to me. I lost 26 lbs and I've kept it off."</p>
          <div class="testi-author">
            <div class="testi-avatar">AK</div>
            <div>
              <div class="testi-name">Andrea K.</div>
              <div class="testi-loc">Orlando, FL</div>
            </div>
            <div class="testi-loss">−26 lbs</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"My wife did it first, then I decided to try. Went from 203 to 150 lbs. The workouts paired with keto were the game changer for me."</p>
          <div class="testi-author">
            <div class="testi-avatar">VH</div>
            <div>
              <div class="testi-name">Victor H.</div>
              <div class="testi-loc">Austin, TX</div>
            </div>
            <div class="testi-loss">−53 lbs</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"The recipes were incredible — I didn't feel like I was dieting at all! The nutritionist chat gave me the confidence to stay on track."</p>
          <div class="testi-author">
            <div class="testi-avatar">BD</div>
            <div>
              <div class="testi-name">B. Dorn</div>
              <div class="testi-loc">Houston, TX</div>
            </div>
            <div class="testi-loss">−51 lbs</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ ABOUT ══════════════ -->
<section class="section hiw-bg" id="about">
  <div class="container">
    <div class="row gy-5 align-items-center">
      <div class="col-lg-6 reveal">
        <div class="section-eyebrow">About FitGo</div>
        <h2 class="section-title">Built by Nutritionists & AI Engineers</h2>
        <p class="section-sub">FitGo is built on the belief that healthy living shouldn't be a luxury. We combine certified nutritionist expertise with cutting-edge AI to deliver plans that actually work — for real people with real lives.</p>
        <div class="row g-3 mt-3">
          <div class="col-6">
            <div class="feat-card" style="padding:20px">
              <div style="font-size:1.4rem;margin-bottom:8px">🏅</div>
              <div class="feat-title" style="font-size:.95rem">Certified Plans</div>
              <div class="feat-desc" style="font-size:.82rem">Prepared with certified nutritionists and trainers</div>
            </div>
          </div>
          <div class="col-6">
            <div class="feat-card" style="padding:20px">
              <div style="font-size:1.4rem;margin-bottom:8px">🔬</div>
              <div class="feat-title" style="font-size:.95rem">Science Backed</div>
              <div class="feat-desc" style="font-size:.82rem">Every recommendation is grounded in research</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1 reveal">
        <div style="background:var(--dark3);border:1px solid var(--border);border-radius:var(--r2);padding:36px">
          <div class="hcard-title mb-3">Our Mission</div>
          <p style="font-size:.95rem;color:var(--text);line-height:1.75;margin-bottom:0">
            FitGo is striving to ensure the fastest and safest weight loss that is individually tailored to each of our customers. We believe that a healthy lifestyle should not be a luxury — the tools needed for a happy and healthy life should be accessible to everyone.
          </p>
          <div class="d-flex gap-3 mt-4 flex-wrap">
            <div class="pill-tag"><i class="bi bi-shield-check"></i> Medically Reviewed</div>
            <div class="pill-tag"><i class="bi bi-people-fill"></i> 300K+ Members</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ FOOTER ══════════════ -->
<footer class="footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <div class="footer-brand-name">Fit<span>Go</span></div>
        <p class="footer-desc">Your AI-powered keto diet and fitness companion. Transform your body, transform your life.</p>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">App</div>
        <a href="{{ route('quiz') }}" class="footer-link">Take the Quiz</a>
        @auth
          <a href="{{ route('dashboard') }}" class="footer-link">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="footer-link">Log in</a>
        @endauth
        <a href="#features" class="footer-link">Features</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Company</div>
        <a href="#about" class="footer-link">About Us</a>
        <a href="#" class="footer-link">Help Center</a>
        <a href="#" class="footer-link">Contact</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Legal</div>
        <a href="#" class="footer-link">Privacy Policy</a>
        <a href="#" class="footer-link">Terms & Conditions</a>
        <a href="#" class="footer-link">Cookie Policy</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Follow</div>
        <a href="#" class="footer-link"><i class="bi bi-facebook me-1"></i> Facebook</a>
        <a href="#" class="footer-link"><i class="bi bi-twitter-x me-1"></i> Twitter/X</a>
        <a href="#" class="footer-link"><i class="bi bi-tiktok me-1"></i> TikTok</a>
      </div>
    </div>
    <div class="footer-copy d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span>© 2025 FitGo. All rights reserved.</span>
      <span>Made with <span style="color:var(--g)">♥</span> for healthier lives</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/fitgo-theme.js') }}" defer></script>
<script>
  // Sticky nav
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('stuck', window.scrollY > 60);
  });

  // Scroll reveal
  const revealEls = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('in'), i * 80);
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  revealEls.forEach(el => observer.observe(el));
</script>
</body>
</html>
