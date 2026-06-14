@extends('layouts.fitgo-app')

@section('title', 'FitGo — Workouts')
@section('pageTitle', 'Workouts')

@section('headerActions')
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    <div class="card-dark mb-4" style="padding:14px 18px;font-size:.85rem;color:var(--muted)">
      <i class="bi bi-database" style="color:var(--g)"></i>
      Loaded from <strong style="color:var(--heading)">workout_plans</strong> — {{ $workoutCount }} sessions (4-week plan). You are on <strong style="color:var(--heading)">week {{ $currentWeek }}</strong>.
    </div>

    @if($workoutCount === 0)
      <div class="card-dark text-center" style="padding:48px 24px">
        <div style="font-size:2.5rem;margin-bottom:12px">💪</div>
        <h3 style="color:var(--heading);font-size:1.1rem;margin-bottom:8px">No workouts yet</h3>
        <p style="color:var(--muted);font-size:.88rem;max-width:420px;margin:0 auto 20px">Complete the quiz with a workout preference (not nutrition-only) to generate your schedule.</p>
        <a href="{{ route('quiz') }}" class="btn-sm-g" style="text-decoration:none">Take the quiz</a>
      </div>
    @else
      @foreach($workoutsByWeek as $week => $weekWorkouts)
        <div class="card-dark mb-4" style="@if((int)$week === $currentWeek) border-color:rgba(34,197,94,.35); @endif">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="card-title-sm mb-0">Week {{ $week }}</div>
            @if((int)$week === $currentWeek)
              <span style="font-size:.72rem;font-weight:700;color:var(--g);padding:4px 10px;border-radius:999px;border:1px solid rgba(34,197,94,.35);background:rgba(34,197,94,.1)">Current week</span>
            @endif
          </div>
          <div class="row g-3">
            @foreach($weekWorkouts as $workout)
              @php
                $type = $workout->workout_type->value;
                $icon = $typeIcons[$type][0] ?? '💪';
                $isToday = (int)$week === $currentWeek && (int)$workout->day_of_week === $todayDow;
                $exercises = is_array($workout->exercises) ? $workout->exercises : [];
              @endphp
              <div class="col-md-6 col-lg-4">
                <div style="background:var(--dark4);border:1px solid var(--border);border-radius:12px;padding:16px;height:100%;@if($isToday) border-color:rgba(34,197,94,.4); @endif">
                  <div class="d-flex align-items-start gap-2 mb-2">
                    <span style="font-size:1.4rem;line-height:1">{{ $icon }}</span>
                    <div class="flex-grow-1">
                      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--g)">{{ $dayLabels[$workout->day_of_week] ?? 'Day '.$workout->day_of_week }}</div>
                      <div style="font-weight:700;color:var(--heading)">{{ $workout->workout_name }}</div>
                    </div>
                    @if($isToday)
                      <span style="font-size:.65rem;font-weight:700;color:var(--g)">Today</span>
                    @endif
                  </div>
                  <div style="font-size:.8rem;color:var(--muted);margin-bottom:10px">
                    {{ ucfirst($type) }} · {{ $workout->duration_minutes }} min
                    @if($workout->location)
                      · {{ ucfirst($workout->location->value) }}
                    @endif
                    @if($workout->intensity)
                      · {{ $workout->intensity }} intensity
                    @endif
                  </div>
                  @if($type === 'rest')
                    <p style="font-size:.82rem;color:var(--muted);margin:0">Recovery day — light walk or mobility optional.</p>
                  @elseif(count($exercises))
                    <div style="font-size:.78rem;color:var(--muted)">
                      <span style="color:var(--heading);font-weight:600">Exercises:</span>
                      <ul style="margin:6px 0 0;padding-left:18px;line-height:1.5">
                        @foreach($exercises as $ex)
                          <li>
                            @if(is_array($ex))
                              {{ $ex['name'] ?? $ex['exercise'] ?? json_encode($ex) }}
                              @if(!empty($ex['sets']) || !empty($ex['reps']))
                                <span style="opacity:.85">({{ trim(($ex['sets'] ?? '').'×'.($ex['reps'] ?? ''), '×') }})</span>
                              @endif
                            @else
                              {{ $ex }}
                            @endif
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    @endif

@endsection
