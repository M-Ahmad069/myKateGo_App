@extends('layouts.fitgo-app')

@section('title', 'FitGo — Progress')
@section('pageTitle', 'Progress')

@section('headerActions')
      <div style="font-size:.82rem;color:var(--muted)">Logged in as <strong style="color:var(--heading)">{{ $user->name }}</strong></div>
      <div style="font-size:.82rem;color:var(--muted)">Change: <strong style="color:var(--g)">{{ $lostKg >= 0 ? '-' : '+' }}{{ number_format(abs($lostKg), 1) }} kg</strong></div>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @if(session('status') === 'progress-saved')
      <div class="mb-4" style="padding:12px 16px;border-radius:10px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:var(--g);font-size:.88rem">
        Saved to your account in the database (<code style="font-size:.75rem">progress_logs</code>).
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4" style="padding:12px 16px;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.35);color:#fca5a5;font-size:.88rem">
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <div class="card-dark mb-4" style="padding:16px 20px;font-size:.85rem;color:var(--muted)">
      <i class="bi bi-database" style="color:var(--g)"></i>
      All entries below are loaded from <strong style="color:var(--heading)">your</strong> rows only (user #{{ $user->id }} · {{ $logCount }} log{{ $logCount === 1 ? '' : 's' }}).
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="card-dark">
          <div class="card-title-sm">Weight trend <span style="font-size:.72rem;color:var(--muted);font-weight:400">(from your logs)</span></div>
          <div style="height:260px;position:relative">
            <canvas id="progressWeightChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card-dark h-100">
          <div class="card-title-sm">Snapshot</div>
          <div style="display:grid;gap:12px;font-size:.88rem">
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Quiz start weight</span><strong>{{ number_format($startWeight, 1) }} kg</strong></div>
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Latest logged weight</span><strong style="color:var(--g)">{{ number_format($currentWeight, 1) }} kg</strong></div>
            @if($todayLog && $todayLog->water_liters !== null)
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Water today</span><strong>{{ number_format((float) $todayLog->water_liters, 1) }} L</strong></div>
            @endif
            @if($todayLog && $todayLog->steps !== null)
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Steps today</span><strong>{{ number_format($todayLog->steps) }}</strong></div>
            @endif
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Calories (your plan)</span><strong>{{ $user->dietPlan ? number_format($user->dietPlan->daily_calories) : '—' }}</strong></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-dark mb-4">
      <div class="card-title-sm">Log a day</div>
      <p style="font-size:.82rem;color:var(--muted);margin:0 0 14px">Updates one row per date for <strong style="color:var(--heading)">{{ $user->email }}</strong>. Same date = overwrite that day’s row.</p>
      <form method="POST" action="{{ route('progress.store') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-3 col-6">
          <label class="card-label">Date</label>
          <input type="date" name="logged_date" value="{{ old('logged_date', now()->toDateString()) }}" required class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-3 col-6">
          <label class="card-label">Weight (kg)</label>
          <input type="number" step="0.1" min="20" max="300" name="weight_kg" value="{{ old('weight_kg', $todayLog?->weight_kg ?? $user->weight_kg) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-6">
          <label class="card-label">Water (L)</label>
          <input type="number" step="0.1" min="0" max="20" name="water_liters" value="{{ old('water_liters', $todayLog?->water_liters) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-6">
          <label class="card-label">Steps</label>
          <input type="number" min="0" name="steps" value="{{ old('steps', $todayLog?->steps) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-12">
          <button type="submit" class="btn-sm-g" style="width:100%">Save</button>
        </div>
        <div class="col-12">
          <label class="card-label">Notes</label>
          <input type="text" name="notes" value="{{ old('notes', $todayLog?->notes) }}" maxlength="2000" placeholder="Optional" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
      </form>
    </div>

    <div class="card-dark">
      <div class="card-title-sm">Your history <span style="font-size:.72rem;color:var(--muted);font-weight:400">(latest 60)</span></div>
      <div class="table-responsive">
        <table class="table table-dark table-hover table-sm mb-0" style="--bs-table-bg:transparent;--bs-table-hover-bg:rgba(34,197,94,.06);font-size:.85rem">
          <thead>
            <tr style="color:var(--muted)">
              <th>Date</th>
              <th>Weight</th>
              <th>Water</th>
              <th>Steps</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
              <tr>
                <td>{{ $log->logged_date?->format('M j, Y') }}</td>
                <td>{{ $log->weight_kg !== null ? number_format((float) $log->weight_kg, 1).' kg' : '—' }}</td>
                <td>{{ $log->water_liters !== null ? number_format((float) $log->water_liters, 1).' L' : '—' }}</td>
                <td>{{ $log->steps !== null ? number_format($log->steps) : '—' }}</td>
                <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $log->notes ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" style="color:var(--muted)">No logs yet — add one above or use Dashboard → Log Today.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  const chartLabels = @json($chartLabels);
  const chartWeights = @json($chartWeights);
  const ctx = document.getElementById('progressWeightChart');
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
</script>
@endpush
