@extends('layouts.fitgo-app')

@section('title', 'FitGo — Grocery List')
@section('pageTitle', 'Grocery List')

@section('headerActions')
      <a href="{{ route('fitgo.meals') }}" class="btn-sm-g" style="background:transparent;border:1px solid var(--border);box-shadow:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px"><i class="bi bi-egg-fried"></i> Meal Plans</a>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    <div class="card-dark mb-4" style="padding:14px 18px;font-size:.85rem;color:var(--muted)">
      <i class="bi bi-database" style="color:var(--g)"></i>
      @if($source === 'meal_plans')
        Built from <strong style="color:var(--heading)">ingredients</strong> in your {{ $mealCount }} saved meals.
      @else
        Sample keto staples (add ingredient data to meals for a personalised list).
      @endif
      <span style="display:block;margin-top:6px">Tick items while shopping — each checkbox saves to <strong style="color:var(--heading)">grocery_checks</strong> in MySQL.</span>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card-dark">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="card-title-sm mb-0">Shopping list ({{ count($itemRows) }} items)</div>
            <span id="grocerySaveStatus" style="font-size:.75rem;color:var(--muted)"></span>
          </div>
          <ul class="list-unstyled mb-0" id="groceryList" style="display:grid;gap:10px">
            @foreach($itemRows as $row)
              <li class="grocery-item @if($row['checked']) grocery-item-done @endif" data-item="{{ $row['name'] }}" style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--dark4);border:1px solid var(--border);border-radius:10px;font-size:.9rem;transition:.2s">
                <input type="checkbox" class="grocery-check" style="width:18px;height:18px;accent-color:var(--g);flex-shrink:0;cursor:pointer" @checked($row['checked']) aria-label="Mark {{ $row['name'] }}">
                <span class="grocery-label" style="flex:1">{{ $row['name'] }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card-dark" style="font-size:.84rem;color:var(--muted);line-height:1.55">
          <div class="card-title-sm">How to use</div>
          <p class="mb-2">Click a checkbox to mark bought or still needed. Your choice is stored per user in the database and stays after refresh.</p>
          <p class="mb-0">The ingredient names come from your meal plan; only checked/not checked is saved in <code style="font-size:.75rem">grocery_checks</code>.</p>
        </div>
      </div>
    </div>

@endsection

@push('scripts')
<script>
  (function () {
    const toggleUrl = @json(route('fitgo.grocery.toggle'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const statusEl = document.getElementById('grocerySaveStatus');

    function setStatus(text, ok) {
      if (!statusEl) return;
      statusEl.textContent = text;
      statusEl.style.color = ok === true ? 'var(--g)' : ok === false ? '#fca5a5' : 'var(--muted)';
      if (text) setTimeout(function () { statusEl.textContent = ''; }, 2000);
    }

    document.querySelectorAll('.grocery-check').forEach(function (box) {
      box.addEventListener('change', async function () {
        const li = box.closest('.grocery-item');
        const item = li?.dataset.item || '';
        const checked = box.checked;
        li?.classList.toggle('grocery-item-done', checked);
        setStatus('Saving…', null);

        try {
          const res = await fetch(toggleUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ item: item, checked: checked }),
          });
          if (!res.ok) throw new Error('save failed');
          setStatus('Saved to database', true);
        } catch (e) {
          box.checked = !checked;
          li?.classList.toggle('grocery-item-done', box.checked);
          setStatus('Could not save — try again', false);
        }
      });
    });
  })();
</script>
<style>
  .grocery-item-done {
    opacity: .55;
    border-color: rgba(34, 197, 94, .25) !important;
  }
  .grocery-item-done .grocery-label {
    text-decoration: line-through;
    color: var(--muted);
  }
</style>
@endpush
