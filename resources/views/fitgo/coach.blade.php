@extends('layouts.fitgo-app')

@section('title', 'FitGo — Coach')
@section('pageTitle', 'FitGo Coach')

@section('headerActions')
      <span class="fitgo-ai-badge" id="coachEngineBadge">FitGo AI — ready</span>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @php
      $cal = $user->dietPlan?->daily_calories;
      $protein = $user->dietPlan?->protein_grams;
      $aiChatUrl = route('ai.chat');
      $aiHistoryUrl = route('ai.chat.history');
    @endphp

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card-dark mb-4" style="min-height:480px;display:flex;flex-direction:column">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="card-title-sm mb-0">Conversation</div>
            <span style="font-size:.75rem;color:var(--muted)">Same chat as the dashboard widget</span>
          </div>
          <div id="coachMessages" class="fitgo-ai-thread" style="flex:1;overflow-y:auto;min-height:300px;max-height:440px;padding:4px 0"></div>
          <div id="coachTyping" class="fitgo-ai-typing" style="display:none">
            <span class="coach-typing-dot"></span><span class="coach-typing-dot"></span><span class="coach-typing-dot"></span>
          </div>
          <form id="coachForm" class="d-flex gap-2 flex-wrap mt-2">
            @csrf
            <input type="text" id="coachInput" maxlength="2000" placeholder="Ask your coach anything…" class="form-control flex-grow-1" style="min-width:220px;background:var(--dark4);border:1px solid var(--border);color:var(--heading);border-radius:10px;padding:12px 14px">
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
          <strong style="color:var(--heading)">Try asking:</strong> “hello”, “what should I eat today?”, “workout for today”, “my macros”, or “I hit a plateau”. The coach reads your saved plan from the database when it can.
        </div>
      </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('js/fitgo-ai-chat.js') }}"></script>
<script>
  FitGoAiChat.init({
    messagesEl: document.getElementById('coachMessages'),
    typingEl: document.getElementById('coachTyping'),
    formEl: document.getElementById('coachForm'),
    inputEl: document.getElementById('coachInput'),
    chipsEl: document.getElementById('coachChips'),
    chatUrl: @json($aiChatUrl),
    historyUrl: @json($aiHistoryUrl),
    showAvatar: true,
    emptyHint: 'Say hello to start chatting with FitGo AI Coach.',
    initialChips: ['Hello', 'What should I eat today?', 'I hit a plateau'],
    onReply: function (data) {
      const badge = document.getElementById('coachEngineBadge');
      if (!badge) return;
      if (data.engine === 'openai') {
        badge.textContent = 'FitGo AI — OpenAI';
      } else {
        badge.textContent = 'FitGo AI — built-in coach';
      }
    },
  });
</script>
@endpush
