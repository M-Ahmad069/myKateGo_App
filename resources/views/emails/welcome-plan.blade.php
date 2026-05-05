<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Welcome to FitGo</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
  <h1>Welcome, {{ $user->name }}!</h1>
  <p>Your <strong>{{ $user->dietPlan?->plan_type ?? 'Standard Keto' }}</strong> plan is ready.</p>
  <p><strong>Daily calories:</strong> {{ $user->dietPlan?->daily_calories ?? '—' }} kcal</p>
  <p><strong>Macro split:</strong>
    {{ $user->dietPlan?->fat_pct ?? '—' }}% fat ·
    {{ $user->dietPlan?->protein_pct ?? '—' }}% protein ·
    {{ $user->dietPlan?->carb_pct ?? '—' }}% carbs
  </p>
  <p><a href="{{ route('dashboard') }}">Open your dashboard</a></p>
  <p>— {{ config('app.name') }}</p>
</body>
</html>
