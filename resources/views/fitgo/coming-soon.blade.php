@extends('layouts.fitgo-app')

@section('title', 'FitGo — '.$title)
@section('pageTitle', $title)

@section('headerActions')
      <a href="{{ route('dashboard') }}" class="btn-sm-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px"><i class="bi bi-grid-fill"></i> Dashboard</a>
@endsection

@section('content')

    <div class="card-dark" style="max-width:560px">
      <h2 style="font-size:1.2rem;margin-bottom:10px">{{ $title }}</h2>
      <p style="color:var(--muted);font-size:.92rem;line-height:1.6;margin:0">This area is coming soon. Your meal plan, workouts, and progress tools are available from the dashboard.</p>
    </div>

@endsection
