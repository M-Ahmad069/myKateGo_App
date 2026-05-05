@extends('layouts.fitgo-app')

@section('title', 'FitGo — My Dashboard')
@section('pageTitle', 'My Dashboard')

@section('headerActions')
      <div style="font-size:.82rem;color:var(--muted)">Today: <strong style="color:var(--heading)" id="todayDate"></strong></div>
      <div class="icon-btn"><i class="bi bi-bell"></i></div>
      <button type="button" class="btn-sm-g" onclick="window.location.href='{{ route('quiz') }}'"><i class="bi bi-arrow-repeat"></i> Retake Quiz</button>
@endsection

@section('content')

    <!-- GREETING BANNER -->
    <div class="card-dark mb-4" style="background:linear-gradient(135deg,#0a1f13,#061410);border-color:rgba(34,197,94,.2);padding:28px 32px;position:relative;overflow:hidden">
      <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(34,197,94,.15),transparent 70%);pointer-events:none"></div>
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div style="font-size:.78rem;color:var(--g);font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">☀ Good morning</div>
          <h2 style="font-size:1.7rem;font-weight:800;margin-bottom:6px" id="greetingName">Welcome back, {{ $user->name }}!</h2>
          <p style="font-size:.9rem;color:var(--muted);margin:0">Stay consistent — your personalised plan is ready.</p>
        </div>
        <div style="text-align:center">
          <div style="font-family:var(--ff-head);font-size:2.8rem;font-weight:800;color:var(--g)">{{ $lostKg >= 0 ? '-'.number_format(abs($lostKg), 1) : '+'.number_format(abs($lostKg), 1) }}</div>
          <div style="font-size:.78rem;color:var(--muted)">kg change (logged)</div>
        </div>
      </div>
    </div>

    @if(($user->plan_status ?? null) === 'generating')
    <div class="card-dark mb-4" style="border-color:rgba(59,130,246,.35);background:linear-gradient(135deg,#0a1523,#07111e);padding:16px 20px">
      <div style="display:flex;align-items:center;gap:12px;font-size:.9rem;color:var(--heading)">
        <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" style="border-width:.15em"></span>
        <span>Generating your personalised meal and workout plans… You can keep browsing; refresh shortly or wait for the quiz confirmation.</span>
      </div>
    </div>
    @endif

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon stat-icon-g">🔥</div>
          <div class="stat-value">{{ $user->dietPlan ? number_format($user->dietPlan->daily_calories) : '—' }}</div>
          <div class="stat-label">Daily Calorie Target</div>
          <div class="stat-change"><i class="bi bi-arrow-down-right"></i> Keto plan</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon stat-icon-o">💧</div>
          <div class="stat-value">{{ $latestLog && $latestLog->water_liters !== null ? number_format($latestLog->water_liters, 1).'L' : '—' }}</div>
          <div class="stat-label">Water Intake</div>
          <div class="stat-change"><i class="bi bi-arrow-up-right"></i> Log in tracker below</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon stat-icon-b">⚡</div>
          <div class="stat-value">{{ $user->dietPlan ? $user->dietPlan->carb_pct.'%' : '—' }}</div>
          <div class="stat-label">Carb Target</div>
          <div class="stat-change"><i class="bi bi-arrow-up-right"></i> Net carbs focus</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon stat-icon-p">🏃</div>
          <div class="stat-value">{{ $latestLog && $latestLog->steps !== null ? number_format($latestLog->steps) : '—' }}</div>
          <div class="stat-label">Steps Today</div>
          <div class="stat-change"><i class="bi bi-arrow-up-right"></i> Log in tracker below</div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">

      <!-- MACRO BREAKDOWN -->
      <div class="col-lg-4">
        <div class="card-dark h-100">
          <div class="card-title-sm">
            Today's Macros
            <span style="font-size:.72rem;color:var(--muted);font-family:var(--ff-body);font-weight:400">{{ $user->dietPlan ? number_format($user->dietPlan->daily_calories).' kcal target' : '—' }}</span>
          </div>
          <div class="macro-grid mb-4">
            <div class="macro-item">
              <div class="macro-circle" style="background:conic-gradient(var(--g) 0% {{ $user->dietPlan ? $user->dietPlan->fat_pct : 0 }}%, rgba(255,255,255,.07) {{ $user->dietPlan ? $user->dietPlan->fat_pct : 0 }}% 100%)">
                <span style="font-size:.78rem">{{ $user->dietPlan ? $user->dietPlan->fat_pct : '—' }}%</span>
              </div>
              <div class="macro-name">Fats</div>
              <div class="macro-val">{{ $user->dietPlan ? $user->dietPlan->fat_grams.'g' : '—' }}</div>
            </div>
            <div class="macro-item">
              <div class="macro-circle" style="background:conic-gradient(var(--o) 0% {{ $user->dietPlan ? $user->dietPlan->protein_pct : 0 }}%, rgba(255,255,255,.07) {{ $user->dietPlan ? $user->dietPlan->protein_pct : 0 }}% 100%)">
                <span style="font-size:.78rem">{{ $user->dietPlan ? $user->dietPlan->protein_pct : '—' }}%</span>
              </div>
              <div class="macro-name">Protein</div>
              <div class="macro-val">{{ $user->dietPlan ? $user->dietPlan->protein_grams.'g' : '—' }}</div>
            </div>
            <div class="macro-item">
              <div class="macro-circle" style="background:conic-gradient(var(--blue) 0% {{ $user->dietPlan ? $user->dietPlan->carb_pct : 0 }}%, rgba(255,255,255,.07) {{ $user->dietPlan ? $user->dietPlan->carb_pct : 0 }}% 100%)">
                <span style="font-size:.78rem">{{ $user->dietPlan ? $user->dietPlan->carb_pct : '—' }}%</span>
              </div>
              <div class="macro-name">Carbs</div>
              <div class="macro-val">{{ $user->dietPlan ? $user->dietPlan->carb_grams.'g' : '—' }}</div>
            </div>
          </div>

          <div class="card-label">Daily Progress</div>
          @php
            $dc = $user->dietPlan?->daily_calories ?? 0;
            $tg = $user->dietPlan?->protein_grams ?? 0;
            $cg = $user->dietPlan?->carb_grams ?? 0;
            $mealCal = $todayMeals->sum('calories');
            $calPct = $dc > 0 ? min(100, round($mealCal / $dc * 100)) : 0;
          @endphp
          <div class="prog-bar-wrap">
            <div class="prog-bar-top"><span class="prog-bar-label">Planned meals (kcal)</span><span class="prog-bar-val">{{ number_format($mealCal) }} / {{ $dc ? number_format($dc) : '—' }}</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:{{ $calPct }}%;background:linear-gradient(90deg,var(--g),var(--g3))"></div></div>
          </div>
          <div class="prog-bar-wrap">
            <div class="prog-bar-top"><span class="prog-bar-label">Protein (planned)</span><span class="prog-bar-val">{{ $todayMeals->sum('protein_g') }} / {{ $tg }}g</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:{{ $tg > 0 ? min(100, round($todayMeals->sum('protein_g') / $tg * 100)) : 0 }}%;background:linear-gradient(90deg,var(--o),#fb923c)"></div></div>
          </div>
          <div class="prog-bar-wrap">
            <div class="prog-bar-top"><span class="prog-bar-label">Water</span><span class="prog-bar-val">{{ $latestLog && $latestLog->water_liters !== null ? $latestLog->water_liters.' / 2.5L' : '— / 2.5L' }}</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:{{ $latestLog && $latestLog->water_liters ? min(100, round($latestLog->water_liters / 2.5 * 100)) : 0 }}%;background:linear-gradient(90deg,var(--blue),#93c5fd)"></div></div>
          </div>
          <div class="prog-bar-wrap">
            <div class="prog-bar-top"><span class="prog-bar-label">Net Carbs (planned)</span><span class="prog-bar-val">{{ $todayMeals->sum('carb_g') }} / {{ $cg }}g</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:{{ $cg > 0 ? min(100, round($todayMeals->sum('carb_g') / $cg * 100)) : 0 }}%;background:linear-gradient(90deg,#a855f7,#c084fc)"></div></div>
          </div>

          <!-- KETOSIS METER -->
          <div class="card-label mt-3">Ketosis Meter</div>
          <div class="keto-meter">
            <div class="keto-track">
              <div class="keto-fill" style="width:{{ $user->dietPlan ? min(100, max(5, 100 - $user->dietPlan->carb_pct * 8)) : 30 }}%"></div>
            </div>
            <div class="keto-labels">
              <span class="keto-label">Low</span>
              <span class="keto-label" style="color:var(--g);font-weight:600">On track</span>
              <span class="keto-label">Deep</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TODAY'S MEALS -->
      <div class="col-lg-4">
        <div class="card-dark h-100">
          <div class="card-title-sm">Today's Meal Plan</div>
          <div class="day-tabs">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $label)
              <div class="day-tab {{ ($i + 1) === $todayDow ? 'active' : '' }}">{{ $label }}</div>
            @endforeach
          </div>
          <div>
            @forelse($todayMeals as $meal)
            <div class="meal-row">
              <div class="meal-time">{{ $mealTimeLabel[$meal->meal_type->value] ?? '' }}</div>
              <div class="meal-dot" style="{{ $meal->meal_type->value === 'snack_am' ? 'background:var(--o)' : (in_array($meal->meal_type->value, ['snack_pm','dinner'], true) ? 'background:rgba(255,255,255,.2)' : '') }}"></div>
              <div class="meal-info">
                <div class="meal-name">{{ $meal->meal_name }}</div>
                <div class="meal-macro">F: {{ $meal->fat_g }}g · P: {{ $meal->protein_g }}g · C: {{ $meal->carb_g }}g</div>
              </div>
              <div class="meal-kcal">{{ $meal->calories }}</div>
              <div class="meal-logged pending"></div>
            </div>
            @empty
            <p style="font-size:.85rem;color:var(--muted);margin:0">No meals for today yet. Complete the quiz and wait for generation.</p>
            @endforelse
          </div>
          <div style="text-align:center;margin-top:16px">
            <button class="btn-sm-g" style="width:100%">
              <i class="bi bi-plus-circle"></i> Log a Meal
            </button>
          </div>
        </div>
      </div>

      <!-- WEIGHT CHART -->
      <div class="col-lg-4">
        <div class="card-dark mb-4">
          <div class="card-title-sm">Weight Progress</div>
          <div style="height:200px;position:relative">
            <canvas id="weightChartCanvas"></canvas>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:6px">
            <span style="font-size:.65rem;color:var(--muted)">Oldest log</span>
            <span style="font-size:.65rem;color:var(--muted)">Latest</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.05)">
            <div>
              <div style="font-size:.72rem;color:var(--muted)">Starting</div>
              <div style="font-family:var(--ff-head);font-weight:800;font-size:1.2rem" id="startWeight">{{ number_format($startWeight, 1) }} kg</div>
            </div>
            <div style="text-align:center">
              <div style="font-size:.72rem;color:var(--muted)">Change</div>
              <div style="font-family:var(--ff-head);font-weight:800;font-size:1.2rem;color:var(--g)">{{ $lostKg >= 0 ? '-'.number_format(abs($lostKg), 1) : '+'.number_format(abs($lostKg), 1) }} kg</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:.72rem;color:var(--muted)">Current</div>
              <div style="font-family:var(--ff-head);font-weight:800;font-size:1.2rem" id="curWeight">{{ number_format($currentWeight, 1) }} kg</div>
            </div>
          </div>
        </div>

        <!-- PROFILE QUICK VIEW -->
        <div class="card-dark">
          <div class="card-title-sm">My Profile</div>
          <div id="profileDetails">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
              <span style="font-size:.82rem;color:var(--muted)">Goal</span>
              <span style="font-size:.82rem;font-weight:600;color:var(--heading)">{{ $goalLabel }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
              <span style="font-size:.82rem;color:var(--muted)">Daily Calories</span>
              <span style="font-size:.82rem;font-weight:600;color:var(--heading)">{{ $user->dietPlan ? number_format($user->dietPlan->daily_calories).' kcal' : '—' }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
              <span style="font-size:.82rem;color:var(--muted)">Plan Type</span>
              <span style="font-size:.82rem;font-weight:600;color:var(--g)">{{ $user->dietPlan?->plan_type ?? '—' }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0">
              <span style="font-size:.82rem;color:var(--muted)">Est. weeks to goal</span>
              <span style="font-size:.82rem;font-weight:600;color:var(--heading)">{{ $user->dietPlan ? $user->dietPlan->estimated_weeks_to_goal.' wk' : '—' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- WORKOUT PLAN -->
    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="card-dark">
          <div class="card-title-sm">
            This Week's Workouts
            <span style="font-size:.72rem;background:rgba(34,197,94,.12);color:var(--g);padding:4px 10px;border-radius:var(--r3);font-family:var(--ff-body);font-weight:600">Week {{ $weekNum }} of 4</span>
          </div>

          @php
            $dayFull = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            $wIcons = [
              'strength' => ['💪', 'rgba(34,197,94,.1)', 'var(--g)'],
              'cardio' => ['🏃', 'rgba(249,115,22,.1)', 'var(--o)'],
              'hiit' => ['⚡', 'rgba(249,115,22,.12)', 'var(--o)'],
              'rest' => ['😴', 'rgba(255,255,255,.05)', 'var(--muted)'],
              'flexibility' => ['🧘', 'rgba(59,130,246,.1)', 'var(--blue)'],
            ];
          @endphp
          @foreach($weekWorkouts as $workout)
          @php
            $wd = $weekStart->copy()->addDays($workout->day_of_week - 1);
            $isToday = $wd->isSameDay(now());
            $isPast = $wd->lt(now()->startOfDay()) && ! $isToday;
            $exN = is_array($workout->exercises) ? count($workout->exercises) : 0;
            $ic = $wIcons[$workout->workout_type->value] ?? $wIcons['strength'];
          @endphp
          <div class="workout-card {{ $workout->workout_type->value === 'rest' ? 'rest' : '' }}">
            <div class="workout-icon" style="background:{{ $ic[1] }};color:{{ $ic[2] }}">{{ $ic[0] }}</div>
            <div class="workout-info">
              <div class="workout-name">{{ $workout->workout_name }}</div>
              <div class="workout-meta">{{ $dayFull[$workout->day_of_week - 1] }} · {{ $workout->duration_minutes }} min · {{ $exN }} exercises</div>
            </div>
            @if($workout->workout_type->value === 'rest')
              <span class="workout-badge badge-muted">Rest</span>
            @elseif($isToday)
              <span class="workout-badge badge-o">Today</span>
            @elseif($isPast)
              <span class="workout-badge badge-g">âœ“ Done</span>
            @else
              <span class="workout-badge badge-b">Upcoming</span>
            @endif
          </div>
          @endforeach
        </div>
      </div>

      <!-- QUICK TIPS -->
      <div class="col-lg-4">
        <div class="card-dark h-100">
          <div class="card-title-sm">Today's Tips 💡</div>
          <div style="display:flex;flex-direction:column;gap:12px">
            <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px">
              <div style="font-size:.75rem;font-weight:700;color:var(--g);margin-bottom:5px">🥑 NUTRITION</div>
              <div style="font-size:.85rem;line-height:1.55">{{ $user->dietPlan?->daily_tip ?: 'Start your morning with MCT oil in your coffee to boost ketosis and mental clarity.' }}</div>
            </div>
            <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px">
              <div style="font-size:.75rem;font-weight:700;color:var(--o);margin-bottom:5px">⚡ WORKOUT</div>
              <div style="font-size:.85rem;line-height:1.55">Do your HIIT session in a fasted state today to maximise fat burning.</div>
            </div>
            <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px">
              <div style="font-size:.75rem;font-weight:700;color:var(--blue);margin-bottom:5px">💧 HYDRATION</div>
              <div style="font-size:.85rem;line-height:1.55">Add electrolytes to your water — keto can deplete sodium, potassium, and magnesium.</div>
            </div>
            <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px">
              <div style="font-size:.75rem;font-weight:700;color:#a855f7;margin-bottom:5px">😴 RECOVERY</div>
              <div style="font-size:.85rem;line-height:1.55">Aim for 7–9 hours of sleep. Poor sleep raises cortisol and stalls fat loss.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DAILY PROGRESS LOG -->
    <div class="card-dark mb-4">
      <div class="card-title-sm">Log Today</div>
      <form method="POST" action="{{ route('progress.store') }}" class="row g-3 align-items-end">
        @csrf
        <input type="hidden" name="logged_date" value="{{ now()->toDateString() }}">
        <div class="col-md-3 col-6">
          <label class="card-label">Weight (kg)</label>
          <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg', $latestLog?->weight_kg) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-3 col-6">
          <label class="card-label">Water (L)</label>
          <input type="number" step="0.1" name="water_liters" value="{{ old('water_liters', $latestLog?->water_liters) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-3 col-6">
          <label class="card-label">Steps</label>
          <input type="number" name="steps" value="{{ old('steps', $latestLog?->steps) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-3 col-12">
          <button type="submit" class="btn-sm-g" style="width:100%">Save log</button>
        </div>
      </form>
    </div>

    <!-- CHAT STRIP -->
    <div class="card-dark" style="background:linear-gradient(135deg,#0a1323,#07111e);border-color:rgba(59,130,246,.2);padding:28px 32px">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-16" style="gap:16px">
          <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">🧑‍🏫</div>
          <div>
            <div style="font-weight:700;color:var(--heading)">FitGo Coach</div>
            <div style="font-size:.82rem;color:var(--muted)">OpenAI coach with rule fallback — same brain as the floating chat widget.</div>
          </div>
        </div>
        <a href="{{ route('fitgo.coach') }}" class="btn-sm-g" style="background:linear-gradient(135deg,var(--blue),#1d4ed8);box-shadow:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none">
          <i class="bi bi-chat-dots-fill"></i> Open FitGo Coach
        </a>
      </div>
    </div>

    @php
      $aiChatUrl = route('ai.chat');
      $aiHistoryUrl = route('ai.chat.history');
    @endphp

    <div id="aiChatDock" style="position:fixed;bottom:24px;right:24px;z-index:1080;font-family:var(--ff-body);display:flex;flex-direction:column;align-items:flex-end;gap:12px">
      <div id="aiChatPanel" style="display:none;flex-direction:column;width:min(380px,calc(100vw - 48px));max-height:min(520px,70vh);background:var(--dark3);border:1px solid var(--border);border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.45);overflow:hidden">
        <div style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#0a1523,#07111e)">
          <span style="font-weight:700;font-size:.9rem;color:var(--heading)">FitGo AI Chat</span>
          <button type="button" id="aiChatClose" class="btn btn-sm btn-link" style="color:var(--muted);text-decoration:none;padding:0">✕</button>
        </div>
        <div id="aiChatMessages" style="flex:1;overflow-y:auto;padding:12px 14px;max-height:360px;font-size:.85rem;line-height:1.45;color:var(--heading)"></div>
        <div id="aiChatTyping" style="display:none;padding:0 14px 8px;font-size:.82rem;color:var(--muted)"><span class="coach-typing-dot"></span><span class="coach-typing-dot"></span><span class="coach-typing-dot"></span></div>
        <form id="aiChatForm" style="padding:10px 12px;border-top:1px solid rgba(255,255,255,.06);display:flex;gap:8px;background:var(--dark4)">
          @csrf
          <input type="text" id="aiChatInput" maxlength="2000" placeholder="Ask your coach…" class="form-control" style="flex:1;background:var(--dark3);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px;font-size:.85rem">
          <button type="submit" class="btn-sm-g" id="aiChatSend">Send</button>
        </form>
      </div>
      <button type="button" id="aiChatToggle" class="btn-sm-g" style="border-radius:999px;width:56px;height:56px;padding:0;display:flex;align-items:center;justify-content:center;font-size:1.35rem;box-shadow:0 8px 24px rgba(59,130,246,.35);flex-shrink:0" aria-label="Open AI chat">
        💬
      </button>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  document.getElementById('todayDate').textContent = new Date().toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});

  const chartLabels = @json($chartLabels);
  const chartWeights = @json($chartWeights);
  const ctx = document.getElementById('weightChartCanvas');
  if (ctx && chartLabels.length) {
    const st = getComputedStyle(document.documentElement);
    const tick = st.getPropertyValue('--muted').trim() || '#7a90a4';
    const grid = 'rgba(255,255,255,.06)';
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Weight (kg)',
          data: chartWeights,
          borderColor: st.getPropertyValue('--g').trim() || '#22c55e',
          backgroundColor: 'rgba(34,197,94,.12)',
          fill: true,
          tension: 0.35,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: tick, maxRotation: 0 }, grid: { color: grid } },
          y: { ticks: { color: tick }, grid: { color: grid } }
        }
      }
    });
  }

  document.querySelectorAll('.day-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  (function () {
    const chatUrl = @json($aiChatUrl);
    const historyUrl = @json($aiHistoryUrl);
    const panel = document.getElementById('aiChatPanel');
    const toggle = document.getElementById('aiChatToggle');
    const closeBtn = document.getElementById('aiChatClose');
    const messagesEl = document.getElementById('aiChatMessages');
    const typingEl = document.getElementById('aiChatTyping');
    const form = document.getElementById('aiChatForm');
    const input = document.getElementById('aiChatInput');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function appendBubble(role, text) {
      const wrap = document.createElement('div');
      wrap.style.marginBottom = '10px';
      wrap.style.textAlign = role === 'user' ? 'right' : 'left';
      const bubble = document.createElement('div');
      bubble.style.display = 'inline-block';
      bubble.style.maxWidth = '92%';
      bubble.style.padding = '10px 12px';
      bubble.style.borderRadius = '12px';
      bubble.style.fontSize = '.84rem';
      bubble.style.lineHeight = '1.45';
      if (role === 'user') {
        bubble.style.background = 'rgba(59,130,246,.18)';
        bubble.style.border = '1px solid rgba(59,130,246,.25)';
      } else {
        bubble.style.background = 'var(--dark4)';
        bubble.style.border = '1px solid var(--border)';
      }
      bubble.textContent = text;
      wrap.appendChild(bubble);
      messagesEl.appendChild(wrap);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function loadHistory() {
      try {
        const res = await fetch(historyUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const data = await res.json().catch(function () { return {}; });
        messagesEl.innerHTML = '';
        (data.messages || []).forEach(function (m) {
          if (m.role === 'user' || m.role === 'assistant') {
            appendBubble(m.role, m.content || '');
          }
        });
      } catch (e) {
        messagesEl.innerHTML = '<div style="color:var(--muted);font-size:.82rem">Could not load history.</div>';
      }
    }

    function setTyping(on) {
      typingEl.style.display = on ? 'block' : 'none';
    }

    toggle.addEventListener('click', function () {
      const open = panel.style.display !== 'none';
      panel.style.display = open ? 'none' : 'flex';
      if (!open) {
        loadHistory();
        setTimeout(function () { input.focus(); }, 50);
      }
    });
    closeBtn.addEventListener('click', function () { panel.style.display = 'none'; });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const msg = (input.value || '').trim();
      if (!msg) return;
      input.value = '';
      appendBubble('user', msg);
      setTyping(true);
      try {
        const res = await fetch(chatUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ message: msg }),
        });
        const data = await res.json().catch(function () { return {}; });
        setTyping(false);
        appendBubble('assistant', data.reply || 'Something went wrong — try again.');
      } catch (err) {
        setTyping(false);
        appendBubble('assistant', 'Network issue. Check your connection and retry.');
      }
    });
  })();
</script>
@endpush
