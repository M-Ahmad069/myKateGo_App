@extends('layouts.fitgo-app')

@section('title', 'FitGo — My Profile')
@section('pageTitle', 'My Profile')

@section('headerActions')
      <a href="{{ route('quiz') }}" class="btn-sm-g" style="background:transparent;border:1px solid var(--border);box-shadow:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px"><i class="bi bi-arrow-repeat"></i> Retake quiz</a>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @if(session('status') === 'profile-updated')
      <div class="mb-4" style="padding:12px 16px;border-radius:10px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:var(--g);font-size:.88rem">
        Profile updated successfully.
      </div>
    @endif

    <div class="card-dark mb-4" style="padding:14px 18px;font-size:.85rem;color:var(--muted)">
      <i class="bi bi-database" style="color:var(--g)"></i>
      Data shown here is from <strong style="color:var(--heading)">your account only</strong> (user #{{ $user->id }} in MySQL):
      {{ $mealCount }} meals · {{ $workoutCount }} workouts
      @if($latestProgress)
        · last progress log {{ $latestProgress->logged_date?->format('M j, Y') }}
      @endif
    </div>

    <!-- Profile hero -->
    <div class="card-dark mb-4" style="background:linear-gradient(135deg,#0a1f13,#061410);border-color:rgba(34,197,94,.2);padding:28px 32px">
      <div class="d-flex align-items-center flex-wrap gap-4">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--g),#16a34a);display:flex;align-items:center;justify-content:center;font-family:var(--ff-head);font-size:1.75rem;font-weight:800;color:#052e16;flex-shrink:0">
          {{ strtoupper(mb_substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-grow-1">
          <div style="font-size:.78rem;color:var(--g);font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Your FitGo account</div>
          <h2 style="font-size:1.6rem;font-weight:800;margin:0 0 6px;color:var(--heading)">{{ $user->name }}</h2>
          <div style="font-size:.88rem;color:var(--muted)">{{ $user->email }}</div>
        </div>
        <div class="text-end">
          @php
            $statusStyle = match($summary['planStatusTone']) {
              'success' => 'background:rgba(34,197,94,.15);color:var(--g);border-color:rgba(34,197,94,.35)',
              'danger' => 'background:rgba(239,68,68,.12);color:#fca5a5;border-color:rgba(239,68,68,.35)',
              'pending' => 'background:rgba(59,130,246,.12);color:#93c5fd;border-color:rgba(59,130,246,.35)',
              default => 'background:var(--dark4);color:var(--muted);border-color:var(--border)',
            };
          @endphp
          <span style="display:inline-block;padding:8px 14px;border-radius:999px;border:1px solid;font-size:.8rem;font-weight:600;{{ $statusStyle }}">
            {{ $summary['planStatusLabel'] }}
          </span>
          <div style="font-size:.82rem;color:var(--muted);margin-top:10px">Goal: <strong style="color:var(--heading)">{{ $summary['goalLabel'] }}</strong></div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <!-- Account -->
      <div class="col-lg-5">
        <div class="card-dark h-100">
          <div class="card-title-sm"><i class="bi bi-person-gear" style="margin-right:6px"></i> Account settings</div>
          <form method="POST" action="{{ route('fitgo.profile.update') }}" class="row g-3">
            @csrf
            @method('PATCH')
            <div class="col-12">
              <label class="card-label">Display name</label>
              <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
              @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="card-label">Email (sign-in)</label>
              <input type="email" value="{{ $user->email }}" disabled class="form-control" style="background:var(--dark2);border:1px solid var(--border);color:var(--muted);border-radius:10px;padding:10px 12px">
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
              <button type="submit" class="btn-sm-g">Save name</button>
              <a href="{{ route('profile.edit') }}" class="btn-sm-g" style="background:transparent;border:1px solid var(--border);box-shadow:none;text-decoration:none;font-size:.85rem">Change password</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Body stats -->
      <div class="col-lg-7">
        <div class="card-dark h-100">
          <div class="card-title-sm"><i class="bi bi-speedometer2" style="margin-right:6px"></i> Body &amp; targets</div>
          <div class="row g-3">
            <div class="col-6 col-md-3">
              <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px;text-align:center">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px">Age</div>
                <div style="font-family:var(--ff-head);font-size:1.35rem;font-weight:800;color:var(--heading)">{{ $user->age }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px;text-align:center">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px">Height</div>
                <div style="font-family:var(--ff-head);font-size:1.35rem;font-weight:800;color:var(--heading)">{{ number_format((float) $user->height_cm, 0) }}<span style="font-size:.75rem;font-weight:500"> cm</span></div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:14px;text-align:center">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px">Current</div>
                <div style="font-family:var(--ff-head);font-size:1.35rem;font-weight:800;color:var(--heading)">{{ number_format((float) $user->weight_kg, 1) }}<span style="font-size:.75rem;font-weight:500"> kg</span></div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background:var(--dark4);border:1px solid rgba(34,197,94,.25);border-radius:var(--r);padding:14px;text-align:center">
                <div style="font-size:.72rem;color:var(--g);margin-bottom:4px">Target</div>
                <div style="font-family:var(--ff-head);font-size:1.35rem;font-weight:800;color:var(--g)">{{ number_format((float) $user->target_weight_kg, 1) }}<span style="font-size:.75rem;font-weight:500"> kg</span></div>
              </div>
            </div>
          </div>
          <p style="font-size:.85rem;color:var(--muted);margin:16px 0 0">
            <i class="bi bi-bullseye" style="color:var(--g)"></i>
            {{ $summary['weightDeltaText'] }}
          </p>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <!-- Quiz summary -->
      <div class="col-lg-6">
        <div class="card-dark h-100">
          <div class="card-title-sm"><i class="bi bi-clipboard-check" style="margin-right:6px"></i> Your quiz answers</div>
          <div style="display:grid;gap:0;font-size:.88rem">
            <div class="profile-row">
              <span class="profile-row-label">Biological sex</span>
              <span class="profile-row-value">{{ ucfirst($user->gender->value) }}</span>
            </div>
            <div class="profile-row">
              <span class="profile-row-label">Activity level</span>
              <span class="profile-row-value">{{ $summary['activityLabel'] }}</span>
            </div>
            <div class="profile-row">
              <span class="profile-row-label">Workouts</span>
              <span class="profile-row-value">{{ $summary['workoutLabel'] }}</span>
            </div>
            <div class="profile-row">
              <span class="profile-row-label">Diet</span>
              <span class="profile-row-value">
                @foreach($summary['dietLabels'] as $d)
                  <span class="profile-chip">{{ $d }}</span>
                @endforeach
              </span>
            </div>
            @foreach($summary['focusLines'] as $line)
            <div class="profile-row">
              <span class="profile-row-label">{{ $line['label'] }}</span>
              <span class="profile-row-value">{{ $line['value'] }}</span>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Plan targets -->
      <div class="col-lg-6">
        <div class="card-dark h-100">
          <div class="card-title-sm"><i class="bi bi-fire" style="margin-right:6px"></i> Keto plan targets</div>
          @if($user->dietPlan)
            <div class="row g-3 mb-3">
              <div class="col-6">
                <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:16px">
                  <div style="font-size:.72rem;color:var(--muted)">Daily calories</div>
                  <div style="font-family:var(--ff-head);font-size:1.5rem;font-weight:800;color:var(--g)">{{ number_format($user->dietPlan->daily_calories) }} <span style="font-size:.8rem">kcal</span></div>
                </div>
              </div>
              <div class="col-6">
                <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--r);padding:16px">
                  <div style="font-size:.72rem;color:var(--muted)">Est. weeks to goal</div>
                  <div style="font-family:var(--ff-head);font-size:1.5rem;font-weight:800;color:var(--heading)">{{ $user->dietPlan->estimated_weeks_to_goal }} <span style="font-size:.8rem">wk</span></div>
                </div>
              </div>
            </div>
            <div class="macro-grid mb-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
              <div style="text-align:center;padding:12px;background:var(--dark4);border-radius:var(--r);border:1px solid var(--border)">
                <div style="font-size:.7rem;color:var(--muted)">Fat</div>
                <div style="font-weight:700;color:var(--g)">{{ $user->dietPlan->fat_pct }}%</div>
                <div style="font-size:.78rem;color:var(--muted)">{{ $user->dietPlan->fat_grams }}g</div>
              </div>
              <div style="text-align:center;padding:12px;background:var(--dark4);border-radius:var(--r);border:1px solid var(--border)">
                <div style="font-size:.7rem;color:var(--muted)">Protein</div>
                <div style="font-weight:700;color:var(--o)">{{ $user->dietPlan->protein_pct }}%</div>
                <div style="font-size:.78rem;color:var(--muted)">{{ $user->dietPlan->protein_grams }}g</div>
              </div>
              <div style="text-align:center;padding:12px;background:var(--dark4);border-radius:var(--r);border:1px solid var(--border)">
                <div style="font-size:.7rem;color:var(--muted)">Carbs</div>
                <div style="font-weight:700;color:var(--blue)">{{ $user->dietPlan->carb_pct }}%</div>
                <div style="font-size:.78rem;color:var(--muted)">{{ $user->dietPlan->carb_grams }}g</div>
              </div>
            </div>
            @if($user->dietPlan->plan_summary)
              <div style="font-size:.85rem;line-height:1.55;color:var(--text);padding:14px;background:var(--dark4);border-radius:var(--r);border:1px solid var(--border)">
                <div style="font-size:.72rem;font-weight:700;color:var(--g);margin-bottom:6px">PLAN SUMMARY</div>
                {{ $user->dietPlan->plan_summary }}
              </div>
            @endif
          @else
            <p style="font-size:.88rem;color:var(--muted);margin:0">No diet plan yet. <a href="{{ route('quiz') }}" style="color:var(--g)">Take the quiz</a> to generate your plan.</p>
          @endif
        </div>
      </div>
    </div>

    @if($latestProgress)
    <div class="card-dark mb-4">
      <div class="card-title-sm"><i class="bi bi-graph-up-arrow" style="margin-right:6px"></i> Latest progress log</div>
      <div class="row g-3" style="font-size:.88rem">
        <div class="col-md-3"><span style="color:var(--muted)">Date</span><br><strong>{{ $latestProgress->logged_date?->format('M j, Y') }}</strong></div>
        <div class="col-md-3"><span style="color:var(--muted)">Weight</span><br><strong style="color:var(--g)">{{ $latestProgress->weight_kg !== null ? number_format((float) $latestProgress->weight_kg, 1).' kg' : '—' }}</strong></div>
        <div class="col-md-3"><span style="color:var(--muted)">Water</span><br><strong>{{ $latestProgress->water_liters !== null ? number_format((float) $latestProgress->water_liters, 1).' L' : '—' }}</strong></div>
        <div class="col-md-3"><span style="color:var(--muted)">Steps</span><br><strong>{{ $latestProgress->steps !== null ? number_format($latestProgress->steps) : '—' }}</strong></div>
      </div>
      <a href="{{ route('fitgo.progress') }}" style="display:inline-block;margin-top:14px;font-size:.85rem;color:var(--g)">View full progress →</a>
    </div>
    @endif

    @if(count($summary['coachingLabels']) > 0)
    <div class="card-dark">
      <div class="card-title-sm"><i class="bi bi-chat-heart" style="margin-right:6px"></i> Coaching focus</div>
      <p style="font-size:.85rem;color:var(--muted);margin:0 0 12px">These tags personalise your workouts and coach replies.</p>
      <div class="d-flex flex-wrap gap-2">
        @foreach($summary['coachingLabels'] as $label)
          <span class="profile-chip profile-chip-g">{{ $label }}</span>
        @endforeach
      </div>
    </div>
    @endif

@endsection

@push('head')
<style>
  .profile-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,.06);
  }
  .profile-row:last-child { border-bottom: none; }
  .profile-row-label { color: var(--muted); flex-shrink: 0; }
  .profile-row-value { font-weight: 600; color: var(--heading); text-align: right; }
  .profile-chip {
    display: inline-block;
    margin: 2px 0 2px 6px;
    padding: 4px 10px;
    border-radius: var(--r3);
    border: 1px solid var(--border);
    font-size: .78rem;
    font-weight: 500;
    color: var(--text);
    background: var(--dark4);
  }
  .profile-chip-g {
    border-color: rgba(34,197,94,.3);
    color: var(--g);
    background: rgba(34,197,94,.08);
  }
</style>
@endpush
