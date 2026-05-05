  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: var(--ff-body); background: var(--dark); color: var(--text); overflow-x: hidden; }
    h1,h2,h3,h4,h5 { font-family: var(--ff-head); color: var(--heading); line-height: 1.2; }
    a { text-decoration: none; color: inherit; }

    /* ─── SIDEBAR ─── */
    .sidebar {
      position: fixed; top: 0; left: 0; bottom: 0;
      width: var(--sidebar);
      background: var(--dark2);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      z-index: 200;
      transition: transform .3s ease;
    }
    .sidebar-header {
      padding: 24px 24px 20px;
      border-bottom: 1px solid var(--border);
    }
    .brand { font-family: var(--ff-head); font-size: 1.4rem; font-weight: 800; }
    .brand span { color: var(--g); }

    .user-mini {
      display: flex; align-items: center; gap: 10px;
      padding: 16px 24px;
      border-bottom: 1px solid var(--border);
    }
    .user-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: linear-gradient(135deg, var(--g2), var(--g));
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .85rem; color: var(--heading); flex-shrink: 0;
    }
    .user-name { font-weight: 600; font-size: .88rem; color: var(--heading); }
    .user-plan { font-size: .72rem; color: var(--g); }

    .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
    .nav-section-label {
      font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
      color: var(--muted); padding: 0 12px; margin: 16px 0 6px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 11px 14px; border-radius: 10px;
      font-size: .88rem; font-weight: 500; color: var(--muted);
      cursor: pointer; transition: all .2s; margin-bottom: 2px;
    }
    a.nav-item { text-decoration: none; }
    .nav-item:hover { background: rgba(255,255,255,.05); color: var(--heading); }
    .nav-item.active { background: rgba(34,197,94,.12); color: var(--g); font-weight: 600; }
    .nav-item i { font-size: 1.1rem; width: 20px; }

    .sidebar-footer { padding: 16px 24px; border-top: 1px solid var(--border); }

    /* ─── MAIN CONTENT ─── */
    .main-content {
      margin-left: var(--sidebar);
      padding: 0;
      min-height: 100vh;
    }

    /* ─── TOP HEADER ─── */
    .top-header {
      position: sticky; top: 0; z-index: 100;
      background: var(--navbar-stuck-bg);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      padding: 18px 32px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .page-title { font-family: var(--ff-head); font-size: 1.3rem; font-weight: 800; }
    .header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .btn-sm-g {
      background: linear-gradient(135deg, var(--g), var(--g2));
      color: #fff; font-family: var(--ff-head); font-weight: 700;
      font-size: .82rem; padding: 9px 18px; border-radius: var(--r3); border: none; cursor: pointer;
      transition: all .2s;
    }
    .btn-sm-g:hover { opacity: .88; transform: translateY(-1px); }
    .icon-btn {
      width: 38px; height: 38px; border-radius: 10px;
      background: var(--dark3); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--muted); transition: all .2s;
    }
    .icon-btn:hover { color: var(--heading); border-color: var(--text); }

    /* ─── CONTENT AREA ─── */
    .content-area { padding: 28px 32px 40px; }

    /* ─── CARDS ─── */
    .card-dark {
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: var(--r2);
      padding: 24px;
    }
    .card-dark-sm { padding: 18px 20px; }
    .card-title-sm {
      font-family: var(--ff-head); font-size: 1rem; font-weight: 700; margin-bottom: 16px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .card-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); margin-bottom: 8px; }

    /* ─── SUMMARY STATS ─── */
    .stat-card {
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: var(--r2);
      padding: 22px 20px;
      transition: all .25s;
    }
    .stat-card:hover { border-color: rgba(34,197,94,.25); transform: translateY(-2px); }
    .stat-icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; margin-bottom: 14px;
    }
    .stat-icon-g { background: rgba(34,197,94,.12); }
    .stat-icon-o { background: rgba(249,115,22,.12); }
    .stat-icon-b { background: rgba(59,130,246,.12); }
    .stat-icon-p { background: rgba(168,85,247,.12); }
    .stat-value { font-family: var(--ff-head); font-size: 1.8rem; font-weight: 800; color: var(--heading); }
    .stat-label { font-size: .8rem; color: var(--muted); margin-top: 2px; }
    .stat-change { font-size: .75rem; color: var(--g); margin-top: 6px; display: flex; align-items: center; gap: 4px; }
    .stat-change.neg { color: var(--o); }

    /* ─── MACRO CIRCLES ─── */
    .macro-grid { display: flex; gap: 16px; justify-content: space-between; }
    .macro-item { text-align: center; flex: 1; }
    .macro-circle {
      width: 58px; height: 58px; border-radius: 50%;
      margin: 0 auto 8px;
      display: flex; align-items: center; justify-content: center;
      font-family: var(--ff-head); font-size: .95rem; font-weight: 800; color: var(--heading);
      position: relative;
    }
    .macro-circle svg { position: absolute; inset: 0; transform: rotate(-90deg); }
    .macro-name { font-size: .72rem; color: var(--muted); }
    .macro-val { font-size: .8rem; font-weight: 600; color: var(--heading); margin-top: 2px; }

    /* ─── PROGRESS BAR ─── */
    .prog-bar-wrap { margin-bottom: 14px; }
    .prog-bar-top { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .prog-bar-label { font-size: .82rem; color: var(--text); }
    .prog-bar-val { font-size: .82rem; color: var(--muted); }
    .prog-bar { height: 7px; background: rgba(255,255,255,.07); border-radius: 4px; overflow: hidden; }
    .prog-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }

    /* ─── MEAL PLAN ─── */
    .day-tabs { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
    .day-tab {
      padding: 7px 16px; border-radius: var(--r3);
      font-size: .8rem; font-weight: 600; cursor: pointer;
      border: 1.5px solid var(--border); color: var(--muted);
      transition: all .2s;
    }
    .day-tab.active { background: var(--g); border-color: var(--g); color: #fff; }
    .day-tab:hover:not(.active) { border-color: rgba(34,197,94,.3); color: var(--heading); }

    .meal-row {
      display: flex; align-items: center; gap: 14px;
      padding: 14px 0;
      border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .meal-row:last-child { border-bottom: none; padding-bottom: 0; }
    .meal-time {
      font-size: .72rem; color: var(--muted); font-weight: 600;
      width: 60px; flex-shrink: 0; text-align: right;
    }
    .meal-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--g); flex-shrink: 0;
    }
    .meal-info { flex: 1; }
    .meal-name { font-size: .9rem; font-weight: 600; color: var(--heading); }
    .meal-macro { font-size: .72rem; color: var(--muted); margin-top: 2px; }
    .meal-kcal {
      font-family: var(--ff-head); font-weight: 700; font-size: .88rem;
      color: var(--heading); flex-shrink: 0;
    }
    .meal-logged {
      width: 26px; height: 26px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: .7rem; flex-shrink: 0;
    }
    .meal-logged.done { background: var(--g); color: #fff; }
    .meal-logged.pending { border: 1.5px solid var(--border); color: transparent; }

    /* ─── WORKOUT PLAN ─── */
    .workout-card {
      background: var(--dark4);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 16px 18px;
      margin-bottom: 10px;
      display: flex; align-items: center; gap: 14px;
      cursor: pointer; transition: all .2s;
    }
    .workout-card:hover { border-color: rgba(34,197,94,.25); background: rgba(34,197,94,.04); }
    .workout-card.rest { opacity: .55; cursor: default; }
    .workout-icon {
      width: 46px; height: 46px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; flex-shrink: 0;
    }
    .workout-info { flex: 1; }
    .workout-name { font-size: .9rem; font-weight: 700; color: var(--heading); }
    .workout-meta { font-size: .75rem; color: var(--muted); margin-top: 2px; }
    .workout-badge {
      font-size: .7rem; font-weight: 700; padding: 4px 10px; border-radius: var(--r3);
    }
    .badge-g { background: rgba(34,197,94,.15); color: var(--g); }
    .badge-o { background: rgba(249,115,22,.15); color: var(--o); }
    .badge-b { background: rgba(59,130,246,.15); color: var(--blue); }
    .badge-muted { background: rgba(255,255,255,.06); color: var(--muted); }

    /* ─── WEIGHT CHART (css only) ─── */
    .chart-bars {
      display: flex; align-items: flex-end;
      gap: 8px; height: 100px;
      padding: 0 4px;
    }
    .chart-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .chart-bar {
      width: 100%; border-radius: 4px 4px 0 0;
      transition: height 1s ease;
    }
    .chart-col-label { font-size: .65rem; color: var(--muted); }

    /* ─── KETOSIS METER ─── */
    .keto-meter { position: relative; margin: 8px 0 16px; }
    .keto-track { height: 10px; border-radius: 5px; background: rgba(255,255,255,.07); overflow: hidden; }
    .keto-fill {
      height: 100%; border-radius: 5px;
      background: linear-gradient(90deg, var(--g2), var(--g), var(--g3));
      position: relative;
    }
    .keto-fill::after {
      content: '';
      position: absolute; right: 0; top: -2px;
      width: 14px; height: 14px; border-radius: 50%;
      background: #fff;
      box-shadow: 0 0 0 3px var(--g), 0 2px 8px rgba(0,0,0,.5);
    }
    .keto-labels { display: flex; justify-content: space-between; margin-top: 6px; }
    .keto-label { font-size: .68rem; color: var(--muted); }

    /* ─── COACH PANEL ─── */
    .coach-msg { font-size: .9rem; line-height: 1.55; color: var(--text); }
    .coach-typing-dot {
      display: inline-block; width: 6px; height: 6px; border-radius: 50%;
      background: var(--muted); margin: 0 2px;
      animation: coachBounce 1.2s ease-in-out infinite;
    }
    .coach-typing-dot:nth-child(2) { animation-delay: .15s; }
    .coach-typing-dot:nth-child(3) { animation-delay: .3s; }
    @keyframes coachBounce {
      0%, 80%, 100% { transform: translateY(0); opacity: .4; }
      40% { transform: translateY(-4px); opacity: 1; }
    }
    .coach-chip {
      display: inline-block; padding: 8px 14px; margin: 4px 6px 0 0;
      border-radius: var(--r3); border: 1px solid var(--border); font-size: .82rem;
      cursor: pointer; color: var(--muted); transition: .2s;
    }
    .coach-chip:hover { border-color: var(--g); color: var(--g); }

    /* ─── MOBILE SIDEBAR TOGGLE ─── */
    .mob-toggle {
      display: none;
      position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
      z-index: 300;
      background: linear-gradient(135deg, var(--g), var(--g2));
      color: #fff; border: none; border-radius: var(--r3);
      padding: 12px 24px; font-family: var(--ff-head); font-weight: 700;
      cursor: pointer; box-shadow: 0 4px 20px rgba(34,197,94,.4);
    }

    /* ─── SCROLLBAR ─── */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.open { transform: none; }
      .main-content { margin-left: 0; }
      .mob-toggle { display: flex; align-items: center; gap: 8px; }
      .content-area { padding: 20px 16px 100px; }
      .top-header { padding: 14px 16px; }
    }
  </style>
