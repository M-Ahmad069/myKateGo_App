@extends('layouts.fitgo-app')

@section('title', 'FitGo — My Profile')
@section('pageTitle', 'My Profile')

@section('headerActions')
      <div style="font-size:.82rem;color:var(--muted)">Goal: <strong style="color:var(--heading)">{{ $user->goal?->value ?? '—' }}</strong></div>
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    @if(session('status') === 'profile-updated')
      <div class="alert mb-3" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:var(--heading);border-radius:10px;padding:12px 16px;font-size:.88rem">Profile updated.</div>
    @endif

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card-dark">
          <div class="card-title-sm">Your account</div>
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
            <div class="col-12">
              <button type="submit" class="btn-sm-g">Save name</button>
              <a href="{{ route('profile.edit') }}" style="margin-left:12px;font-size:.85rem;color:var(--muted)">Breeze password settings →</a>
            </div>
          </form>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card-dark mb-4">
          <div class="card-title-sm">Quiz &amp; plan segment</div>
          <div style="display:grid;gap:10px;font-size:.88rem">
            <div class="d-flex justify-content-between border-bottom" style="border-color:rgba(255,255,255,.06)!important;padding-bottom:8px">
              <span style="color:var(--muted)">Plan segment</span>
              <span style="font-weight:600;color:var(--g)">{{ $user->plan_segment ?? '—' }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom" style="border-color:rgba(255,255,255,.06)!important;padding-bottom:8px">
              <span style="color:var(--muted)">Biological sex</span>
              <span style="font-weight:600;color:var(--heading)">{{ $user->gender->value }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom" style="border-color:rgba(255,255,255,.06)!important;padding-bottom:8px">
              <span style="color:var(--muted)">Age / height / weight</span>
              <span style="font-weight:600;color:var(--heading)">{{ $user->age }} yr · {{ number_format((float) $user->height_cm, 0) }} cm · {{ number_format((float) $user->weight_kg, 1) }} kg → {{ number_format((float) $user->target_weight_kg, 1) }} kg</span>
            </div>
            <div class="d-flex justify-content-between border-bottom" style="border-color:rgba(255,255,255,.06)!important;padding-bottom:8px">
              <span style="color:var(--muted)">Sex-specific signal</span>
              <span style="font-weight:600;color:var(--heading)">{{ $user->gender_specific_data ?? '—' }}</span>
            </div>
            <div>
              <div class="card-label">Coaching tags</div>
              <div style="color:var(--text);line-height:1.5">
                @forelse($user->coaching_tags ?? [] as $tag)
                  <span style="display:inline-block;margin:3px 6px 0 0;padding:5px 11px;border-radius:var(--r3);border:1px solid var(--border);font-size:.78rem">{{ $tag }}</span>
                @empty
                  <span style="color:var(--muted)">—</span>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <div class="card-dark">
          <div class="card-title-sm">Raw quiz profile (debug)</div>
          <pre style="margin:0;font-size:.72rem;color:var(--muted);white-space:pre-wrap;word-break:break-word;max-height:320px;overflow:auto;background:var(--dark4);padding:14px;border-radius:10px;border:1px solid var(--border)">@json($user->quiz_profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)</pre>
        </div>
      </div>
    </div>

@endsection
