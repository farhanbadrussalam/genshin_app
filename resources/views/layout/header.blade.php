<nav class="navbar navbar-expand-md genshin-navbar">
  <div class="container-fluid">

    {{-- Brand / Logo --}}
    <a class="navbar-brand" href="{{ url('/') }}">
      <span class="brand-icon">✦</span>
      Genshin
    </a>

    {{-- Page Title (center, visible on mobile) --}}
    @isset($title)
    <span class="page-title d-md-none">{{ $title }}</span>
    @endisset

    {{-- Hamburger --}}
    <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list text-gold fs-5"></i>
    </button>

    {{-- Nav Links --}}
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto gap-1 py-2 py-md-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}"
            href="{{ url('/') }}">
            <i class="bi bi-house-fill me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('family.*') ? 'active' : '' }}"
            href="{{ route('family.index') }}">
            <i class="bi bi-collection-fill me-1"></i>Family
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('material.*') ? 'active' : '' }}"
            href="{{ route('material.index') }}">
            <i class="bi bi-gem me-1"></i>Material
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('task.*') ? 'active' : '' }}"
            href="{{ route('task.index') }}">
            <i class="bi bi-list-task me-1"></i>Task
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>