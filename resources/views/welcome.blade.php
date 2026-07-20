@extends('layout.main')

@section('content')
@php $title = 'Home'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 2rem; padding-bottom: 4rem;">

  {{-- Hero Section --}}
  <div class="text-center mb-4 animate-fade-in-up">
    <div style="font-size: 3rem; line-height: 1; margin-bottom: 0.5rem;">✦</div>
    <h1 class="font-display text-gold mb-1" style="font-size: 1.5rem; letter-spacing: 0.1em;">
      Genshin Tracker
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.88rem;">
      Kelola material & task upgrade karaktermu
    </p>
  </div>

  {{-- Navigation Cards --}}
  <div class="row g-3 justify-content-center">
    <div class="col-6 col-sm-4 animate-fade-in-up" style="animation-delay: 0.05s;">
      <a href="{{ route('family.index') }}" class="home-nav-card">
        <i class="bi bi-collection-fill nav-icon"></i>
        <span class="nav-label">Family Material</span>
      </a>
    </div>
    <div class="col-6 col-sm-4 animate-fade-in-up" style="animation-delay: 0.1s;">
      <a href="{{ route('material.index') }}" class="home-nav-card">
        <i class="bi bi-gem nav-icon"></i>
        <span class="nav-label">Material</span>
      </a>
    </div>
    <div class="col-6 col-sm-4 animate-fade-in-up" style="animation-delay: 0.15s;">
      <a href="{{ route('task.index') }}" class="home-nav-card">
        <i class="bi bi-list-task nav-icon"></i>
        <span class="nav-label">Task</span>
      </a>
    </div>
  </div>

</div>
@endsection