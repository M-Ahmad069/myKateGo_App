@php($u = auth()->user())
<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="brand">Fit<span>Go</span></div>
  </div>

  <div class="user-mini">
    <div class="user-avatar" id="avatarInitials">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
    <div>
      <div class="user-name" id="sidebarName">{{ $u->name }}</div>
      <div class="user-plan">🔥 Keto Plan Active</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a href="{{ route('dashboard') }}" class="nav-item @if(request()->routeIs('dashboard')) active @endif"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="{{ route('fitgo.profile') }}" class="nav-item @if(request()->routeIs('fitgo.profile')) active @endif"><i class="bi bi-person-fill"></i> My Profile</a>
    <a href="{{ route('fitgo.progress') }}" class="nav-item @if(request()->routeIs('fitgo.progress')) active @endif"><i class="bi bi-graph-up-arrow"></i> Progress</a>

    <div class="nav-section-label">Nutrition</div>
    <a href="{{ route('fitgo.meals') }}" class="nav-item @if(request()->routeIs('fitgo.meals')) active @endif"><i class="bi bi-egg-fried"></i> Meal Plans</a>
    <a href="{{ route('fitgo.grocery') }}" class="nav-item @if(request()->routeIs('fitgo.grocery')) active @endif"><i class="bi bi-cart3"></i> Grocery List</a>

    <div class="nav-section-label">Fitness</div>
    <a href="{{ route('fitgo.workouts') }}" class="nav-item @if(request()->routeIs('fitgo.workouts')) active @endif"><i class="bi bi-lightning-charge-fill"></i> Workouts</a>

    <div class="nav-section-label">Support</div>
    <a href="{{ route('fitgo.coach') }}" class="nav-item @if(request()->routeIs('fitgo.coach')) active @endif"><i class="bi bi-chat-dots-fill"></i> FitGo Coach</a>
  </nav>

  <div class="sidebar-footer">
    <a href="{{ route('home') }}" style="font-size:.82rem;color:var(--muted);display:flex;align-items:center;gap:6px">
      <i class="bi bi-arrow-left-circle"></i> Back to Home
    </a>
    <form method="POST" action="{{ route('logout') }}" class="mt-2">
      @csrf
      <button type="submit" style="font-size:.82rem;color:var(--muted);background:none;border:none;padding:0;cursor:pointer;display:flex;align-items:center;gap:6px">
        <i class="bi bi-box-arrow-right"></i> Log out
      </button>
    </form>
  </div>
</aside>
