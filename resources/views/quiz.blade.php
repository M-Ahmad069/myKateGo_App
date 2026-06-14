<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script>
    document.documentElement.setAttribute('data-theme','dark');document.documentElement.style.colorScheme='dark';
  </script>
  <title>FitGo — Your Personalised Plan Quiz</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet" />
  <link href="{{ asset('css/fitgo-theme.css') }}" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--ff-body);
      background: var(--dark);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
    }
    h1,h2,h3,h4 { font-family: var(--ff-head); color: var(--heading); line-height: 1.2; }

    /* ─── BACKGROUND GLOW ─── */
    .bg-glow {
      position: fixed; pointer-events: none; z-index: 0;
      border-radius: 50%;
      filter: blur(100px); opacity: .12;
    }
    .glow-1 { top: -150px; left: -150px; width: 500px; height: 500px; background: var(--g); }
    .glow-2 { bottom: -150px; right: -150px; width: 400px; height: 400px; background: #3b82f6; }

    /* ─── TOPBAR ─── */
    .quiz-topbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 16px 0 0;
      background: var(--quiz-topbar-bg);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
    }
    .brand { font-family: var(--ff-head); font-size: 1.3rem; font-weight: 800; }
    .brand span { color: var(--g); }

    /* ─── PROGRESS BAR ─── */
    .progress-wrap { margin-top: 14px; }
    .progress-track {
      height: 4px;
      background: rgba(255,255,255,.08);
      border-radius: 2px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--g), var(--g3));
      border-radius: 2px;
      transition: width .5s cubic-bezier(.4,0,.2,1);
    }
    .progress-text {
      display: flex; justify-content: space-between;
      font-size: .75rem; color: var(--muted);
      padding: 6px 0 14px;
    }
    .progress-text strong { color: var(--g); }

    /* ─── QUIZ WRAPPER ─── */
    .quiz-wrapper {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 140px 16px 60px;
      position: relative; z-index: 1;
    }
    .quiz-box {
      width: 100%; max-width: 580px;
    }

    /* ─── STEP ─── */
    .step { display: none; animation: stepIn .4s ease both; }
    .step.active { display: block; }
    @keyframes stepIn {
      from { opacity:0; transform: translateX(30px); }
      to { opacity:1; transform: none; }
    }
    .step-label {
      font-size: .72rem; font-weight: 700; letter-spacing: 1.2px;
      text-transform: uppercase; color: var(--g); margin-bottom: 10px;
    }
    .step-q {
      font-size: clamp(1.5rem, 3.5vw, 2.1rem);
      font-weight: 800; letter-spacing: -.4px;
      margin-bottom: 10px;
    }
    .step-hint { font-size: .88rem; color: var(--muted); margin-bottom: 32px; line-height: 1.6; }

    /* ─── OPTION CARDS ─── */
    .opt-grid { display: grid; gap: 12px; }
    .opt-grid-2 { grid-template-columns: 1fr 1fr; }
    .opt-grid-1 { grid-template-columns: 1fr; }

    .opt-card {
      background: var(--dark3);
      border: 2px solid var(--border);
      border-radius: var(--r2);
      padding: 18px 20px;
      cursor: pointer;
      transition: all .22s ease;
      display: flex; align-items: center; gap: 14px;
      user-select: none;
    }
    .opt-card:hover {
      border-color: rgba(34,197,94,.4);
      background: rgba(34,197,94,.06);
      transform: translateY(-1px);
    }
    .opt-card.selected {
      border-color: var(--g);
      background: rgba(34,197,94,.1);
      box-shadow: 0 0 0 3px rgba(34,197,94,.15);
    }
    .opt-card.selected .opt-check { background: var(--g); border-color: var(--g); }
    .opt-card.selected .opt-check i { opacity: 1; color: #fff; }

    .opt-emoji { font-size: 1.9rem; line-height: 1; flex-shrink: 0; }
    .opt-emoji-sm { font-size: 1.5rem; }

    .opt-body { flex: 1; }
    .opt-title { font-family: var(--ff-head); font-weight: 700; font-size: 1rem; color: var(--heading); }
    .opt-sub { font-size: .78rem; color: var(--muted); margin-top: 2px; }

    .opt-check {
      width: 26px; height: 26px; border-radius: 50%;
      border: 2px solid var(--border);
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      transition: all .2s;
    }
    .opt-check i { font-size: .7rem; color: var(--muted); opacity: 0; transition: opacity .15s, color .15s; }

    /* large gender cards */
    .gender-card {
      background: var(--dark3);
      border: 2px solid var(--border);
      border-radius: var(--r2);
      padding: 36px 24px;
      cursor: pointer;
      transition: all .25s ease;
      text-align: center;
    }
    .gender-card:hover { border-color: rgba(34,197,94,.4); background: rgba(34,197,94,.06); transform: translateY(-3px); }
    .gender-card.selected { border-color: var(--g); background: rgba(34,197,94,.1); }
    .gender-emoji { font-size: 3.5rem; margin-bottom: 14px; }
    .gender-title { font-family: var(--ff-head); font-size: 1.25rem; font-weight: 800; }
    .gender-sub { font-size: .82rem; color: var(--muted); margin-top: 6px; }

    /* ─── INPUT FIELDS ─── */
    .fitgo-input {
      width: 100%;
      background: var(--dark3);
      border: 2px solid var(--border);
      border-radius: var(--r);
      color: var(--heading);
      font-family: var(--ff-body);
      font-size: 1rem;
      padding: 15px 18px;
      transition: border-color .2s;
      outline: none;
    }
    .fitgo-input:focus { border-color: var(--g); background: rgba(34,197,94,.04); }
    .fitgo-input::placeholder { color: var(--muted); }
    .input-group-fit { position: relative; }
    .input-unit {
      position: absolute; right: 16px; top: 50%;
      transform: translateY(-50%);
      font-size: .85rem; color: var(--muted); font-weight: 600;
      pointer-events: none;
    }

    .range-wrap { padding: 8px 0; }
    .fitgo-range {
      -webkit-appearance: none;
      width: 100%;
      height: 6px;
      background: var(--dark4);
      border-radius: 3px;
      outline: none;
      cursor: pointer;
    }
    .fitgo-range::-webkit-slider-thumb {
      -webkit-appearance: none;
      width: 22px; height: 22px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--g), var(--g2));
      box-shadow: 0 0 0 4px rgba(34,197,94,.2);
      cursor: pointer;
    }
    .range-val {
      font-family: var(--ff-head); font-size: 2.2rem;
      font-weight: 800; color: var(--heading); text-align: center;
      margin-top: 14px;
    }
    .range-val span { font-size: 1rem; color: var(--muted); font-family: var(--ff-body); font-weight: 400; }

    /* ─── EMAIL CAPTURE ─── */
    .email-highlight {
      background: linear-gradient(135deg, #0a1f13, #061410);
      border: 1px solid rgba(34,197,94,.2);
      border-radius: var(--r2);
      padding: 28px 24px;
      margin-bottom: 24px;
    }
    .email-highlight-title { font-family: var(--ff-head); font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
    .email-highlight-list { list-style: none; }
    .email-highlight-list li {
      font-size: .88rem; color: var(--text);
      padding: 5px 0;
      display: flex; align-items: center; gap: 8px;
    }
    .email-highlight-list li::before {
      content: '✓';
      color: var(--g); font-weight: 700; font-size: .8rem;
      flex-shrink: 0;
    }

    /* ─── LOADING SCREEN ─── */
    .loading-screen {
      display: none;
      text-align: center;
    }
    .loading-screen.active { display: block; }
    .loader-ring {
      width: 80px; height: 80px;
      border: 4px solid rgba(34,197,94,.15);
      border-top-color: var(--g);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 28px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loader-step {
      font-size: .88rem; color: var(--muted);
      min-height: 28px; transition: all .4s;
    }
    .loader-bar-wrap { margin: 24px auto 0; max-width: 300px; }
    .loader-track { height: 6px; background: rgba(255,255,255,.08); border-radius: 3px; overflow: hidden; }
    .loader-fill {
      height: 100%; width: 0%;
      background: linear-gradient(90deg, var(--g), var(--g3));
      transition: width .6s ease;
      border-radius: 3px;
    }

    /* ─── NAVIGATION BUTTONS ─── */
    .quiz-nav {
      display: flex; gap: 12px; align-items: center;
      margin-top: 32px;
    }
    .btn-next {
      flex: 1;
      background: linear-gradient(135deg, var(--g), var(--g2));
      color: #fff;
      font-family: var(--ff-head); font-weight: 700; font-size: 1rem;
      padding: 16px 28px;
      border-radius: var(--r3); border: none; cursor: pointer;
      box-shadow: 0 4px 20px rgba(34,197,94,.3);
      transition: all .25s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-next:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(34,197,94,.45); }
    .btn-next:disabled { opacity: .45; cursor: not-allowed; transform: none; }
    .btn-back {
      background: var(--dark3);
      border: 1.5px solid var(--border);
      color: var(--muted);
      font-family: var(--ff-body); font-weight: 500;
      padding: 16px 20px; border-radius: var(--r3);
      cursor: pointer; transition: all .2s;
    }
    .btn-back:hover { color: var(--heading); border-color: var(--text); }

    /* ─── STEP COUNTER DOTS ─── */
    .step-dots {
      display: flex; gap: 6px; justify-content: center;
      margin-bottom: 32px;
    }
    .dot {
      width: 8px; height: 8px; border-radius: 4px;
      background: rgba(255,255,255,.12);
      transition: all .3s;
    }
    .dot.done { background: var(--g); opacity: .5; }
    .dot.active { background: var(--g); width: 22px; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 500px) {
      .opt-grid-2 { grid-template-columns: 1fr; }
      .gender-card { padding: 28px 16px; }
    }
  </style>
</head>
<body>

<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>

<!-- TOPBAR -->
<div class="quiz-topbar">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
      <a href="{{ route('home') }}" class="brand">Fit<span>Go</span></a>
      <span id="stepCounter" class="ms-auto" style="font-size:.82rem;color:var(--muted);white-space:nowrap">Step 1 of 14</span>
    </div>
  </div>
  <div class="progress-wrap">
    <div class="container">
      <div class="progress-track">
        <div class="progress-fill" id="progressFill" style="width:8%"></div>
      </div>
      <div class="progress-text">
        <span id="progressLabel">Getting started...</span>
        <strong id="progressPct">8%</strong>
      </div>
    </div>
  </div>
</div>

<!-- QUIZ -->
<div class="quiz-wrapper">
  <div class="quiz-box">

    <!-- STEP DOTS -->
    <div class="step-dots" id="stepDots"></div>

    <!-- ── STEP 1: Gender ── -->
    <div class="step active" data-step="1">
      <div class="step-label">Step 1 — Let's get personal</div>
      <h2 class="step-q">What's your biological sex?</h2>
      <p class="step-hint">We use this to calculate your precise caloric needs and hormonal factors that affect fat loss.</p>
      <div class="opt-grid opt-grid-2">
        <div class="gender-card" data-val="male" onclick="selectGender(this,'male')">
          <div class="gender-emoji">♂️</div>
          <div class="gender-title">Male</div>
          <div class="gender-sub">Testosterone-based metabolism</div>
        </div>
        <div class="gender-card" data-val="female" onclick="selectGender(this,'female')">
          <div class="gender-emoji">♀️</div>
          <div class="gender-title">Female</div>
          <div class="gender-sub">Oestrogen-based metabolism</div>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-next" id="nextBtn1" disabled onclick="nextStep()">
          Continue <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </div>

    <!-- ── STEP 2: Goal ── -->
    <div class="step" data-step="2">
      <div class="step-label">Step 2 — Your goal</div>
      <h2 class="step-q">What's your main goal?</h2>
      <p class="step-hint">We'll build your plan around this primary objective.</p>
      <div class="opt-grid opt-grid-1">
        <div class="opt-card" onclick="selectOpt(this,'goal','lose_weight')">
          <div class="opt-emoji">⚖️</div>
          <div class="opt-body">
            <div class="opt-title">Lose Weight</div>
            <div class="opt-sub">Burn fat, get leaner, feel lighter</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'goal','build_muscle')">
          <div class="opt-emoji">💪</div>
          <div class="opt-body">
            <div class="opt-title">Build Muscle</div>
            <div class="opt-sub">Gain strength and lean mass</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'goal','get_fit')">
          <div class="opt-emoji">🏃</div>
          <div class="opt-body">
            <div class="opt-title">Get Fit & Energised</div>
            <div class="opt-sub">Improve stamina, energy, and health</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'goal','maintain')">
          <div class="opt-emoji">🎯</div>
          <div class="opt-body">
            <div class="opt-title">Maintain & Tone</div>
            <div class="opt-sub">Keep current weight, improve body composition</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn2" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 3: Age ── -->
    <div class="step" data-step="3">
      <div class="step-label">Step 3 — About you</div>
      <h2 class="step-q">How old are you?</h2>
      <p class="step-hint">Age affects your metabolic rate and nutritional needs.</p>
      <div class="range-wrap">
        <input type="range" class="fitgo-range" id="ageRange" min="16" max="80" value="28" oninput="updateRange('age')">
        <div class="range-val"><span id="ageVal">28</span> <span>years old</span></div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" onclick="quizData.age=document.getElementById('ageRange').value;nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 4: Height ── -->
    <div class="step" data-step="4">
      <div class="step-label">Step 4 — Body metrics</div>
      <h2 class="step-q">What's your height?</h2>
      <p class="step-hint">Used to calculate your ideal weight and caloric targets.</p>
      <div class="row g-3 mb-3">
        <div class="col-8">
          <div class="input-group-fit">
            <input type="number" class="fitgo-input" id="heightVal" placeholder="170" min="100" max="250" oninput="validateStep4()">
            <span class="input-unit" id="heightUnit">cm</span>
          </div>
        </div>
        <div class="col-4">
          <select class="fitgo-input" id="heightSystem" onchange="toggleHeightUnit()">
            <option value="cm">cm</option>
            <option value="ft">ft/in</option>
          </select>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn4" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 5: Weight ── -->
    <div class="step" data-step="5">
      <div class="step-label">Step 5 — Body metrics</div>
      <h2 class="step-q">What's your current weight?</h2>
      <p class="step-hint">Don't worry — this is just a starting point, not a judgement.</p>
      <div class="row g-3 mb-3">
        <div class="col-8">
          <div class="input-group-fit">
            <input type="number" class="fitgo-input" id="weightVal" placeholder="75" min="30" max="300" oninput="validateStep5()">
            <span class="input-unit" id="weightUnit">kg</span>
          </div>
        </div>
        <div class="col-4">
          <select class="fitgo-input" id="weightSystem" onchange="document.getElementById('weightUnit').textContent=this.value">
            <option value="kg">kg</option>
            <option value="lbs">lbs</option>
          </select>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn5" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 6: Target Weight ── -->
    <div class="step" data-step="6">
      <div class="step-label">Step 6 — Your target</div>
      <h2 class="step-q">What's your target weight?</h2>
      <p class="step-hint">We'll calculate a realistic timeline to get you there safely.</p>
      <div class="input-group-fit">
        <input type="number" class="fitgo-input" id="targetWeightVal" placeholder="65" min="30" max="300" oninput="validateStep6()">
        <span class="input-unit">kg</span>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn6" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 7: Activity Level ── -->
    <div class="step" data-step="7">
      <div class="step-label">Step 7 — Lifestyle</div>
      <h2 class="step-q">How active are you currently?</h2>
      <p class="step-hint">Be honest — this helps us set the right calorie targets.</p>
      <div class="opt-grid opt-grid-1">
        <div class="opt-card" onclick="selectOpt(this,'activity','sedentary')">
          <div class="opt-emoji opt-emoji-sm">🛋️</div>
          <div class="opt-body">
            <div class="opt-title">Sedentary</div>
            <div class="opt-sub">Desk job, little to no exercise</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'activity','light')">
          <div class="opt-emoji opt-emoji-sm">🚶</div>
          <div class="opt-body">
            <div class="opt-title">Lightly Active</div>
            <div class="opt-sub">Light exercise 1–3 days/week</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'activity','moderate')">
          <div class="opt-emoji opt-emoji-sm">🏊</div>
          <div class="opt-body">
            <div class="opt-title">Moderately Active</div>
            <div class="opt-sub">Moderate exercise 3–5 days/week</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" onclick="selectOpt(this,'activity','very_active')">
          <div class="opt-emoji opt-emoji-sm">🏋️</div>
          <div class="opt-body">
            <div class="opt-title">Very Active</div>
            <div class="opt-sub">Hard exercise 6–7 days/week</div>
          </div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn7" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 8: Workout Type (gender adaptive text) ── -->
    <div class="step" data-step="8">
      <div class="step-label">Step 8 — Training preferences</div>
      <h2 class="step-q">Where do you prefer to work out?</h2>
      <p class="step-hint">We'll build your workout plan around your preferred environment.</p>
      <div class="opt-grid opt-grid-2">
        <div class="opt-card" style="flex-direction:column;text-align:center;padding:28px 16px" onclick="selectOpt(this,'workout','home')">
          <div class="opt-emoji" style="margin-bottom:10px">🏠</div>
          <div class="opt-title">Home</div>
          <div class="opt-sub">Bodyweight & minimal equipment</div>
          <div class="opt-check" style="margin:12px auto 0"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" style="flex-direction:column;text-align:center;padding:28px 16px" onclick="selectOpt(this,'workout','gym')">
          <div class="opt-emoji" style="margin-bottom:10px">🏋️</div>
          <div class="opt-title">Gym</div>
          <div class="opt-sub">Full equipment access</div>
          <div class="opt-check" style="margin:12px auto 0"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" style="flex-direction:column;text-align:center;padding:28px 16px" onclick="selectOpt(this,'workout','both')">
          <div class="opt-emoji" style="margin-bottom:10px">🔄</div>
          <div class="opt-title">Both</div>
          <div class="opt-sub">Mix of home and gym</div>
          <div class="opt-check" style="margin:12px auto 0"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card" style="flex-direction:column;text-align:center;padding:28px 16px" onclick="selectOpt(this,'workout','none')">
          <div class="opt-emoji" style="margin-bottom:10px">🚫</div>
          <div class="opt-title">Diet Only</div>
          <div class="opt-sub">Focus on nutrition, no workouts</div>
          <div class="opt-check" style="margin:12px auto 0"><i class="bi bi-check"></i></div>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn8" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 9: Diet Restrictions ── -->
    <div class="step" data-step="9">
      <div class="step-label">Step 9 — Food preferences</div>
      <h2 class="step-q">Any dietary restrictions?</h2>
      <p class="step-hint">Select all that apply — we'll make sure your plan fits perfectly.</p>
      <div class="opt-grid opt-grid-1" id="dietOpts">
        <div class="opt-card multi-sel" onclick="toggleMulti(this,'diet','none')">
          <div class="opt-emoji opt-emoji-sm">✅</div>
          <div class="opt-body"><div class="opt-title">No Restrictions</div><div class="opt-sub">I eat everything</div></div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card multi-sel" onclick="toggleMulti(this,'diet','vegetarian')">
          <div class="opt-emoji opt-emoji-sm">🥦</div>
          <div class="opt-body"><div class="opt-title">Vegetarian</div><div class="opt-sub">No meat or fish</div></div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card multi-sel" onclick="toggleMulti(this,'diet','dairy_free')">
          <div class="opt-emoji opt-emoji-sm">🥛</div>
          <div class="opt-body"><div class="opt-title">Dairy Free</div><div class="opt-sub">No milk products</div></div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
        <div class="opt-card multi-sel" onclick="toggleMulti(this,'diet','gluten_free')">
          <div class="opt-emoji opt-emoji-sm">🌾</div>
          <div class="opt-body"><div class="opt-title">Gluten Free</div><div class="opt-sub">No wheat, barley, or rye</div></div>
          <div class="opt-check"><i class="bi bi-check"></i></div>
        </div>
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 10: GENDER-SPECIFIC (female: hormones / male: training intensity) ── -->
    <div class="step" data-step="10">
      <div class="step-label">Step 10 — <span id="genderStepLabel">Specific factors</span></div>
      <h2 class="step-q" id="genderStepQ">Loading...</h2>
      <p class="step-hint" id="genderStepHint"></p>
      <div class="opt-grid opt-grid-1" id="genderStepOpts">
        <!-- Injected by JS based on gender -->
      </div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn10" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 11: GENDER SECONDARY (male focus / female cycle) ── -->
    <div class="step" data-step="11">
      <div class="step-label">Step 11 — <span id="genderStep2Label">Details</span></div>
      <h2 class="step-q" id="genderStep2Q"></h2>
      <p class="step-hint" id="genderStep2Hint"></p>
      <div class="opt-grid opt-grid-1" id="genderStep2Opts"></div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtnBranch11" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 12: GENDER TERTIARY (male recovery / female energy) ── -->
    <div class="step" data-step="12">
      <div class="step-label">Step 12 — <span id="genderStep3Label">Details</span></div>
      <h2 class="step-q" id="genderStep3Q"></h2>
      <p class="step-hint" id="genderStep3Hint"></p>
      <div class="opt-grid opt-grid-1" id="genderStep3Opts"></div>
      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtnBranch12" disabled onclick="nextStep()">Continue <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>

    <!-- ── STEP 13: ACCOUNT ── -->
    <div class="step" data-step="13">
      <div class="step-label">Almost done! 🎉</div>
      <h2 class="step-q">Create your FitGo account</h2>
      <p class="step-hint">Use your email to sign in. Your personalised plan will be ready on your dashboard as soon as it finishes generating — no inbox delivery.</p>

      <div class="email-highlight">
        <div class="email-highlight-title">Your plan will include:</div>
        <ul class="email-highlight-list">
          <li>7-day personalised keto meal plan</li>
          <li>Daily calorie & macro targets</li>
          <li>4-week workout schedule</li>
          <li>Grocery shopping list</li>
          <li>Progress tracking dashboard access</li>
        </ul>
      </div>

      <div style="display:flex;flex-direction:column;gap:12px">
        <div class="input-group-fit">
          <input type="text" class="fitgo-input" id="userNameInput" placeholder="Your first name" oninput="validateStepAccount()">
        </div>
        <div class="input-group-fit">
          <input type="email" class="fitgo-input" id="userEmailInput" placeholder="your@email.com" oninput="validateStepAccount()">
        </div>
        <div class="input-group-fit">
          <input type="password" class="fitgo-input" id="userPasswordInput" placeholder="Password (min 8 characters)" autocomplete="new-password" oninput="validateStepAccount()">
        </div>
        <div class="input-group-fit">
          <input type="password" class="fitgo-input" id="userPasswordConfirmInput" placeholder="Confirm password" autocomplete="new-password" oninput="validateStepAccount()">
        </div>
      </div>
      <div style="font-size:.75rem;color:var(--muted);margin-top:10px">
        🔒 We respect your privacy. No spam, ever. Unsubscribe anytime.
      </div>

      <div class="quiz-nav">
        <button class="btn-back" onclick="prevStep()"><i class="bi bi-arrow-left"></i></button>
        <button class="btn-next" id="nextBtn13" disabled onclick="nextStep()">
          <i class="bi bi-send-fill"></i> Get My Plan
        </button>
      </div>
    </div>

    <!-- ── STEP 14: LOADING ── -->
    <div class="step" data-step="14">
      <div class="loading-screen active" id="loadingScreen">
        <div class="loader-ring"></div>
        <h3 style="font-size:1.4rem;margin-bottom:10px">Building Your Plan...</h3>
        <div class="loader-step" id="loaderStepText">Analysing your body metrics...</div>
        <div class="loader-bar-wrap">
          <div class="loader-track">
            <div class="loader-fill" id="loaderFill"></div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /quiz-box -->
</div><!-- /quiz-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/fitgo-theme.js') }}" defer></script>
<script>
  /* ─── STATE ─── */
  let currentStep = 1;
  const totalSteps = 14;
  const quizData = { gender: null, goal: null, age: 28, height: null, weight: null, targetWeight: null, activity: null, workout: null, diet: [], genderSpecific: null, trainingFocus: null, recoveryStress: null, cycleRegularity: null, energyLevel: null, name: null, email: null, password: '', password_confirmation: '', heightSystem: 'cm', weightSystem: 'kg' };

  /* ─── GENDER-SPECIFIC QUESTIONS ─── */
  const genderQuestions = {
    male: {
      label: "Training intensity",
      q: "What's your current fitness experience?",
      hint: "This helps us pitch your workout intensity at the right level from day one.",
      opts: [
        { val: 'beginner', emoji: '🌱', title: 'Complete Beginner', sub: 'Never trained consistently before' },
        { val: 'some_exp', emoji: '🏃', title: 'Some Experience', sub: '1–2 years of on/off training' },
        { val: 'intermediate', emoji: '💪', title: 'Intermediate', sub: '2–4 years of regular training' },
        { val: 'advanced', emoji: '🏆', title: 'Advanced', sub: '4+ years of consistent training' },
      ]
    },
    female: {
      label: "Hormonal health",
      q: "Do any of these apply to you?",
      hint: "These factors significantly influence female metabolism and the best dietary approach for you.",
      opts: [
        { val: 'none', emoji: '✅', title: 'None of the above', sub: 'Regular cycle, no specific conditions' },
        { val: 'pcos', emoji: '🔄', title: 'PCOS', sub: 'Polycystic ovary syndrome' },
        { val: 'thyroid', emoji: '🦋', title: 'Thyroid Issues', sub: 'Hypo or hyperthyroidism' },
        { val: 'menopause', emoji: '🌸', title: 'Menopause / Perimenopause', sub: 'Hormonal transition phase' },
      ]
    }
  };

  /* ─── INIT DOTS ─── */
  function initDots() {
    const container = document.getElementById('stepDots');
    container.innerHTML = '';
    for (let i = 1; i <= totalSteps; i++) {
      const d = document.createElement('div');
      d.className = 'dot' + (i < currentStep ? ' done' : i === currentStep ? ' active' : '');
      container.appendChild(d);
    }
  }

  /* ─── UPDATE UI ─── */
  function updateUI() {
    const pct = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressPct').textContent = pct + '%';
    document.getElementById('stepCounter').textContent = `Step ${currentStep} of ${totalSteps}`;
    const labels = ['','Getting started...','Setting your goal...','About your age...','Your height...','Your weight...','Your target...','Your lifestyle...','Workout prefs...','Dietary needs...','Specific factors...','Tailoring details...','Recovery & rhythm...','Almost done!','Building your plan...'];
    document.getElementById('progressLabel').textContent = labels[currentStep] || '';
    initDots();
  }

  /* ─── NAVIGATION ─── */
  function showStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const el = document.querySelector(`[data-step="${n}"]`);
    if (el) el.classList.add('active');
    window.scrollTo(0, 0);
    updateUI();

    if (n === 10) injectGenderStep();
    if (n === 11) injectGenderStep2();
    if (n === 12) injectGenderStep3();
  }

  function nextStep() {
    if (currentStep < totalSteps) {
      currentStep++;
      showStep(currentStep);
      if (currentStep === totalSteps) startLoading();
    }
  }

  function prevStep() {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  }

  /* ─── GENDER SELECT ─── */
  function selectGender(card, val) {
    document.querySelectorAll('.gender-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    quizData.gender = val;
    document.getElementById('nextBtn1').disabled = false;
  }

  /* ─── SINGLE SELECT OPT ─── */
  function selectOpt(card, field, val) {
    const siblings = card.closest('.opt-grid').querySelectorAll('.opt-card:not(.multi-sel)');
    siblings.forEach(s => s.classList.remove('selected'));
    card.classList.add('selected');
    quizData[field] = val;
    const step = card.closest('.step');
    const btn = step.querySelector('.btn-next');
    if (btn) btn.disabled = false;
  }

  /* ─── MULTI SELECT ─── */
  function toggleMulti(card, field, val) {
    card.classList.toggle('selected');
    if (!quizData[field]) quizData[field] = [];
    if (card.classList.contains('selected')) {
      quizData[field].push(val);
    } else {
      quizData[field] = quizData[field].filter(v => v !== val);
    }
  }

  /* ─── RANGE ─── */
  function updateRange(field) {
    const val = document.getElementById(field+'Range').value;
    document.getElementById(field+'Val').textContent = val;
    quizData[field] = val;
  }

  /* ─── VALIDATION ─── */
  function validateStep4() {
    const v = document.getElementById('heightVal').value;
    quizData.height = v;
    document.getElementById('nextBtn4').disabled = !v || v < 100 || v > 250;
  }
  function validateStep5() {
    const v = document.getElementById('weightVal').value;
    quizData.weight = v;
    quizData.weightSystem = document.getElementById('weightSystem').value;
    document.getElementById('nextBtn5').disabled = !v || v < 30 || v > 300;
  }
  function validateStep6() {
    const v = document.getElementById('targetWeightVal').value;
    quizData.targetWeight = v;
    document.getElementById('nextBtn6').disabled = !v || v < 30 || v > 300;
  }
  function validateStepAccount() {
    const name = document.getElementById('userNameInput').value.trim();
    const email = document.getElementById('userEmailInput').value.trim();
    const password = document.getElementById('userPasswordInput').value;
    const password2 = document.getElementById('userPasswordConfirmInput').value;
    const valid = name.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && password.length >= 8 && password === password2;
    document.getElementById('nextBtn13').disabled = !valid;
    quizData.name = name; quizData.email = email;
    quizData.password = password;
    quizData.password_confirmation = password2;
    quizData.heightSystem = document.getElementById('heightSystem').value;
    quizData.weightSystem = document.getElementById('weightSystem').value;
  }

  /* ─── HEIGHT TOGGLE ─── */
  function toggleHeightUnit() {
    const sys = document.getElementById('heightSystem').value;
    document.getElementById('heightUnit').textContent = sys === 'cm' ? 'cm' : 'ft';
    document.getElementById('heightVal').placeholder = sys === 'cm' ? '170' : '5.9';
    quizData.heightSystem = sys;
  }

  /* ─── GENDER STEP INJECT ─── */
  function injectGenderStep() {
    const gender = quizData.gender || 'male';
    const data = genderQuestions[gender];
    document.getElementById('genderStepLabel').textContent = data.label;
    document.getElementById('genderStepQ').textContent = data.q;
    document.getElementById('genderStepHint').textContent = data.hint;
    const container = document.getElementById('genderStepOpts');
    container.innerHTML = data.opts.map(o => `
      <div class="opt-card" onclick="selectOpt(this,'genderSpecific','${o.val}')">
        <div class="opt-emoji opt-emoji-sm">${o.emoji}</div>
        <div class="opt-body">
          <div class="opt-title">${o.title}</div>
          <div class="opt-sub">${o.sub}</div>
        </div>
        <div class="opt-check"><i class="bi bi-check"></i></div>
      </div>
    `).join('');
  }

  const genderQuestions2 = {
    male: {
      label: 'Body-composition focus',
      q: 'What do you want to prioritise in the next phase?',
      hint: 'We tune calories and training emphasis around this — you can still work on everything over time.',
      field: 'trainingFocus',
      opts: [
        { val: 'lose_fat', emoji: '🔥', title: 'Lose fat', sub: 'Leaner, definition, fat loss first' },
        { val: 'build_muscle', emoji: '💪', title: 'Build muscle', sub: 'Strength and size as the main driver' },
        { val: 'recomp', emoji: '⚖️', title: 'Recomp', sub: 'Swap fat for muscle at a steady pace' },
      ],
    },
    female: {
      label: 'Cycle & rhythm',
      q: 'How regular is your menstrual cycle?',
      hint: 'Helps us bias recovery and cardio sensibly — pick N/A if not applicable.',
      field: 'cycleRegularity',
      opts: [
        { val: 'regular', emoji: '📅', title: 'Mostly regular', sub: 'Predictable within a few days' },
        { val: 'irregular', emoji: '〰️', title: 'Irregular', sub: 'Hard to predict timing' },
        { val: 'na', emoji: '—', title: 'Not applicable', sub: 'Menopause, hormonal treatment, or other' },
      ],
    },
  };

  const genderQuestions3 = {
    male: {
      label: 'Stress & recovery',
      q: 'How would you describe sleep and day-to-day stress lately?',
      hint: 'Higher stress means we keep workout volume a bit gentler so you can recover.',
      field: 'recoveryStress',
      opts: [
        { val: 'low', emoji: '😌', title: 'Low', sub: 'Sleeping well, manageable stress' },
        { val: 'med', emoji: '😐', title: 'Moderate', sub: 'Some rough nights or busy periods' },
        { val: 'high', emoji: '😣', title: 'High', sub: 'Poor sleep or high chronic stress' },
      ],
    },
    female: {
      label: 'Daily energy',
      q: 'What is your typical energy level most days?',
      hint: 'We adjust workout density and walking volume to match how you feel day to day.',
      field: 'energyLevel',
      opts: [
        { val: 'low', emoji: '🔋', title: 'Often low', sub: 'Tired, need more rest days' },
        { val: 'moderate', emoji: '⚡', title: 'Moderate', sub: 'Okay most days, dips sometimes' },
        { val: 'high', emoji: '✨', title: 'High', sub: 'Generally energised' },
      ],
    },
  };

  function injectGenderStep2() {
    const gender = quizData.gender || 'male';
    const data = genderQuestions2[gender];
    document.getElementById('genderStep2Label').textContent = data.label;
    document.getElementById('genderStep2Q').textContent = data.q;
    document.getElementById('genderStep2Hint').textContent = data.hint;
    const container = document.getElementById('genderStep2Opts');
    const field = data.field;
    container.innerHTML = data.opts.map(o => `
      <div class="opt-card" onclick="selectOpt(this,'${field}','${o.val}')">
        <div class="opt-emoji opt-emoji-sm">${o.emoji}</div>
        <div class="opt-body">
          <div class="opt-title">${o.title}</div>
          <div class="opt-sub">${o.sub}</div>
        </div>
        <div class="opt-check"><i class="bi bi-check"></i></div>
      </div>
    `).join('');
    document.getElementById('nextBtnBranch11').disabled = !quizData[field];
  }

  function injectGenderStep3() {
    const gender = quizData.gender || 'male';
    const data = genderQuestions3[gender];
    document.getElementById('genderStep3Label').textContent = data.label;
    document.getElementById('genderStep3Q').textContent = data.q;
    document.getElementById('genderStep3Hint').textContent = data.hint;
    const container = document.getElementById('genderStep3Opts');
    const field = data.field;
    container.innerHTML = data.opts.map(o => `
      <div class="opt-card" onclick="selectOpt(this,'${field}','${o.val}')">
        <div class="opt-emoji opt-emoji-sm">${o.emoji}</div>
        <div class="opt-body">
          <div class="opt-title">${o.title}</div>
          <div class="opt-sub">${o.sub}</div>
        </div>
        <div class="opt-check"><i class="bi bi-check"></i></div>
      </div>
    `).join('');
    document.getElementById('nextBtnBranch12').disabled = !quizData[field];
  }

  /* ─── LOADING ANIMATION ─── */
  function startLoading() {
    const steps = [
      'Analysing your body metrics...',
      'Calculating your caloric targets...',
      'Selecting your personalised recipes...',
      'Building your workout schedule...',
      'Generating your meal plan...',
      'Preparing your dashboard...',
      'Almost ready...',
    ];
    let i = 0; let pct = 0;
    const fill = document.getElementById('loaderFill');
    const text = document.getElementById('loaderStepText');

    const iv = setInterval(() => {
      if (i < steps.length) {
        text.textContent = steps[i];
        pct = Math.round(((i+1)/steps.length)*100);
        fill.style.width = pct + '%';
        i++;
      } else {
        clearInterval(iv);
        submitQuiz();
      }
    }, 900);
  }

  const quizSubmitUrl = @json(route('quiz.submit'));
  const planStatusUrl = @json(route('plan.status'));
  const dashboardUrl = @json(route('dashboard'));

  async function submitQuiz() {
    const text = document.getElementById('loaderStepText');
    quizData.weightSystem = document.getElementById('weightSystem').value;
    quizData.heightSystem = document.getElementById('heightSystem').value;
    try {
      const res = await fetch(quizSubmitUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(quizData),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save your quiz.');
        text.textContent = msg;
        return;
      }
      text.textContent = 'Generating your personalised plan...';
      await pollPlanReady(text);
      window.location.href = dashboardUrl;
    } catch (e) {
      text.textContent = 'Network error. Please try again.';
    }
  }

  async function pollPlanReady(textEl) {
    for (let n = 0; n < 150; n++) {
      const r = await fetch(planStatusUrl, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const j = await r.json().catch(() => ({}));
      if (j.ready) return;
      if (j.plan_status === 'failed') {
        throw new Error('Plan generation failed. Check Laragon MySQL is running, then try the quiz again.');
      }
      textEl.textContent = 'Almost ready... (' + (n + 1) + ')';
      await new Promise(function (x) { setTimeout(x, 2000); });
    }
  }

  /* ─── BOOT ─── */
  showStep(1);
</script>
</body>
</html>
