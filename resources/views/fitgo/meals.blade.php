@extends('layouts.fitgo-app')

@section('title', 'FitGo — Meal Plans')
@section('pageTitle', 'Meal Plans')

@section('headerActions')
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    <div class="card-dark mb-4" style="padding:14px 18px;font-size:.85rem;color:var(--muted)">
      <i class="bi bi-database" style="color:var(--g)"></i>
      Loaded from <strong style="color:var(--heading)">meal_plans</strong> for your user — {{ $mealCount }} meals saved after your quiz.
    </div>

    @if($dietPlan)
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card-dark h-100 text-center" style="padding:22px">
            <div class="card-label">Daily calories</div>
            <div style="font-size:1.75rem;font-weight:800;color:var(--g)">{{ number_format($dietPlan->daily_calories) }}</div>
            <div style="font-size:.78rem;color:var(--muted)">kcal / day</div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card-dark h-100">
            <div class="card-title-sm">Macro split</div>
            <div class="d-flex flex-wrap gap-3" style="font-size:.88rem">
              <span><strong style="color:var(--heading)">{{ $dietPlan->fat_grams }}g</strong> fat ({{ $dietPlan->fat_pct }}%)</span>
              <span><strong style="color:var(--heading)">{{ $dietPlan->protein_grams }}g</strong> protein ({{ $dietPlan->protein_pct }}%)</span>
              <span><strong style="color:var(--heading)">{{ $dietPlan->carb_grams }}g</strong> carbs ({{ $dietPlan->carb_pct }}%)</span>
            </div>
          </div>
        </div>
      </div>
    @endif

    @if($mealCount === 0)
      <div class="card-dark text-center" style="padding:48px 24px">
        <div style="font-size:2.5rem;margin-bottom:12px">🍽️</div>
        <h3 style="color:var(--heading);font-size:1.1rem;margin-bottom:8px">No meals yet</h3>
        <p style="color:var(--muted);font-size:.88rem;max-width:420px;margin:0 auto 20px">Complete the onboarding quiz to generate your personalised weekly meal plan.</p>
        <a href="{{ route('quiz') }}" class="btn-sm-g" style="text-decoration:none">Take the quiz</a>
      </div>
    @else
      @for($dow = 1; $dow <= 7; $dow++)
        @php $dayMeals = $mealsByDay->get($dow); @endphp
        @if($dayMeals && $dayMeals->isNotEmpty())
          <div class="card-dark mb-4 @if($dow === $todayDow) @endif" style="@if($dow === $todayDow) border-color:rgba(34,197,94,.35); @endif">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div class="card-title-sm mb-0">{{ $dayLabels[$dow] ?? 'Day '.$dow }}</div>
              @if($dow === $todayDow)
                <span style="font-size:.72rem;font-weight:700;color:var(--g);padding:4px 10px;border-radius:999px;border:1px solid rgba(34,197,94,.35);background:rgba(34,197,94,.1)">Today</span>
              @endif
            </div>
            <div class="row g-3">
              @foreach($dayMeals as $meal)
                <div class="col-md-6 col-lg-4">
                  <div style="background:var(--dark4);border:1px solid var(--border);border-radius:12px;padding:16px;height:100%">
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--g);margin-bottom:6px">{{ str_replace('_', ' ', $meal->meal_type->value) }}</div>
                    <div style="font-weight:700;color:var(--heading);margin-bottom:6px">{{ $meal->meal_name }}</div>
                    @if($meal->description)
                      <p style="font-size:.82rem;color:var(--muted);margin:0 0 10px;line-height:1.45">{{ $meal->description }}</p>
                    @endif
                    <div style="font-size:.8rem;color:var(--muted)">
                      <strong style="color:var(--heading)">{{ $meal->calories }}</strong> kcal
                      @if($meal->protein_g)
                        · P {{ $meal->protein_g }}g · F {{ $meal->fat_g }}g · C {{ $meal->carb_g }}g
                      @endif
                    </div>
                    @if(is_array($meal->ingredients) && count($meal->ingredients))
                      <div style="font-size:.75rem;color:var(--muted);margin-top:10px;line-height:1.4">
                        <span style="color:var(--heading)">Ingredients:</span> {{ implode(', ', $meal->ingredients) }}
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      @endfor
    @endif

@endsection
