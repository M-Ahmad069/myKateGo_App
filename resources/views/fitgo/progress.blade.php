@extends('layouts.fitgo-app')

@section('title', 'FitGo — Progress')
@section('pageTitle', 'Progress')

@section('headerActions')
      <div style="font-size:.82rem;color:var(--muted)">Change: <strong style="color:var(--g)">{{ $lostKg >= 0 ? '-' : '+' }}{{ number_format(abs($lostKg), 1) }} kg</strong></div>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @if(session('status') === 'progress-saved')
      <div class="alert mb-3" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:var(--heading);border-radius:10px;padding:12px 16px;font-size:.88rem">Progress log saved.</div>
    @endif

    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="card-dark">
          <div class="card-title-sm">Weight trend</div>
          <div style="height:260px;position:relative">
            <canvas id="progressWeightChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card-dark h-100">
          <div class="card-title-sm">Snapshot</div>
          <div style="display:grid;gap:12px;font-size:.88rem">
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Starting</span><strong>{{ number_format($startWeight, 1) }} kg</strong></div>
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Latest log</span><strong style="color:var(--g)">{{ number_format($currentWeight, 1) }} kg</strong></div>
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Calories (plan)</span><strong>{{ $user->dietPlan ? number_format($user->dietPlan->daily_calories) : '—' }}</strong></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-dark mb-4">
      <div class="card-title-sm">Log a day</div>
      <form method="POST" action="{{ route('progress.store') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-3 col-6">
          <label class="card-label">Date</label>
          <input type="date" name="logged_date" value="{{ old('logged_date', now()->toDateString()) }}" required class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-3 col-6">
          <label class="card-label">Weight (kg)</label>
          <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg', $latestLog?->weight_kg) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-6">
          <label class="card-label">Water (L)</label>
          <input type="number" step="0.1" name="water_liters" value="{{ old('water_liters', $latestLog?->water_liters) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-6">
          <label class="card-label">Steps</label>
          <input type="number" name="steps" value="{{ old('steps', $latestLog?->steps) }}" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
        <div class="col-md-2 col-12">
          <button type="submit" class="btn-sm-g" style="width:100%">Save</button>
        </div>
        <div class="col-12">
          <label class="card-label">Notes</label>
          <input type="text" name="notes" value="{{ old('notes') }}" maxlength="2000" placeholder="Optional" class="form-control" style="background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:10px 12px">
        </div>
      </form>
    </div>

    <div class="card-dark">
      <div class="card-title-sm">History (latest 60)</div>
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
              <tr><td colspan="5" style="color:var(--muted)">No logs yet — add one above.</td></tr>
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
