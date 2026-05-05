@extends('layouts.fitgo-app')

@section('title', 'FitGo — Coach')
@section('pageTitle', 'FitGo Coach')

@section('headerActions')
      <span style="font-size:.82rem;color:var(--muted)">AI coach (OpenAI) with rule-based fallback if unavailable.</span>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @php
      $cal = $user->dietPlan?->daily_calories;
      $protein = $user->dietPlan?->protein_grams;
    @endphp

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card-dark mb-4" style="min-height:220px;display:flex;flex-direction:column">
          <div class="card-title-sm">Conversation</div>
          <div id="coachBubble" class="coach-msg mb-3" style="flex:1">Ask anything about keto pacing, adherence, cravings, workouts, or sleep. I prioritise steady wins over hype.</div>
          <div id="coachTyping" class="coach-msg mb-3" style="display:none;color:var(--muted)"><span class="coach-typing-dot"></span><span class="coach-typing-dot"></span><span class="coach-typing-dot"></span></div>
          <form id="coachForm" class="d-flex gap-2 flex-wrap">
            @csrf
            <input type="text" id="coachInput" maxlength="600" placeholder="Your question…" class="form-control flex-grow-1" style="min-width:220px;background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:12px 14px">
            <button type="submit" class="btn-sm-g" id="coachSend">Send</button>
          </form>
          <div class="card-label mt-3">Quick replies</div>
          <div id="coachChips"></div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card-dark mb-4">
          <div class="card-title-sm">Today’s anchors</div>
          <div style="font-size:.88rem;display:grid;gap:10px">
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Daily calories</span><strong>{{ $cal ? number_format($cal).' kcal' : '—' }}</strong></div>
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Protein</span><strong>{{ $protein ? $protein.' g' : '—' }}</strong></div>
            <div class="d-flex justify-content-between"><span style="color:var(--muted)">Goal</span><strong>{{ $user->goal?->value ?? '—' }}</strong></div>
          </div>
        </div>
        <div class="card-dark" style="font-size:.82rem;color:var(--muted);line-height:1.55">
          Tip: Mention “plateau”, “hydration”, “hunger”, “steps”, “fast”, or “sleep” to trigger tighter guidance. Your quiz tags silently steer tone.
        </div>
      </div>
    </div>

@endsection

@push('scripts')
<script>
  const coachUrl = @json(route('fitgo.coach.message'));
  const chipStart = ['Hit a plateau', 'Keto-friendly snacks', 'What about walking?'];

  function renderChips(list) {
    const el = document.getElementById('coachChips');
    el.innerHTML = '';
    (list || []).forEach(function (c) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'coach-chip';
      b.textContent = c;
      b.addEventListener('click', function () {
        document.getElementById('coachInput').value = c;
        document.getElementById('coachForm').dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));
      });
      el.appendChild(b);
    });
  }

  renderChips(chipStart);

  async function coachSend(msg) {
    const bubble = document.getElementById('coachBubble');
    const typing = document.getElementById('coachTyping');
    bubble.style.opacity = '.35';
    typing.style.display = 'block';

    try {
      const res = await fetch(coachUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ message: msg || '' }),
      });
      const data = await res.json().catch(function () { return {}; });
      typing.style.display = 'none';
      bubble.style.opacity = '1';
      bubble.textContent = data.reply || 'Something went sideways — try again in a moment.';
      renderChips(data.chips && data.chips.length ? data.chips : chipStart);
    } catch (e) {
      typing.style.display = 'none';
      bubble.style.opacity = '1';
      bubble.textContent = 'Network issue. Check connection and retry.';
    }
  }

  document.getElementById('coachForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const input = document.getElementById('coachInput');
    const msg = (input.value || '').trim();
    input.value = '';
    await coachSend(msg);
  });
</script>
@endpush
