<!doctype html>
<html lang="en">
<head>
  <title>@yield('title', 'NAAP Lost & Found')</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('css/white-black-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/layout.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/animations.css') }}" rel="stylesheet" />
  
  <!-- Fallback relative paths in case asset() generates wrong URLs -->
  <link href="/css/white-black-theme.css" rel="stylesheet" />
  <link href="/css/layout.css" rel="stylesheet" />
  
  <!-- Critical inline styles for images -->
  <style>
    img { max-width: 100%; height: auto; }
    .navbar-logo { width: 36px; height: 36px; overflow: hidden; flex-shrink: 0; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .navbar-logo img { width: 36px !important; height: 36px !important; max-width: 36px !important; object-fit: contain; display: block; }
    .user-avatar { width: 36px; height: 36px; overflow: hidden; border-radius: 50%; }
    .user-avatar img { width: 36px !important; height: 36px !important; max-width: 36px !important; object-fit: cover; display: block; }
    .dropdown-avatar { width: 36px; height: 36px; overflow: hidden; border-radius: 50%; }
    .dropdown-avatar img { width: 36px !important; height: 36px !important; object-fit: cover; display: block; }
    .dash-avatar { width: 56px; height: 56px; overflow: hidden; border-radius: 16px; flex-shrink: 0; }
    .dash-avatar img { width: 56px !important; height: 56px !important; max-width: 56px !important; object-fit: cover; display: block; }
    body.has-navbar { padding-top: 70px; }
  </style>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('image.png') }}" sizes="192x192" />
  <link rel="apple-touch-icon" href="{{ asset('image.png') }}" sizes="180x180" />
  <meta name="theme-color" content="#0041C7" />
  
  @stack('styles')
</head>
<body class="has-navbar">

@auth
  @include('components.layout.navbar')
  @include('components.layout.sidebar')
@endauth

<!-- Confirmation Modal -->
@include('components.confirmation-modal')

<main class="content-wrap">
  <div class="container py-4">
    @if (session('success'))
      <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-check-circle"></i>
        <div>{{ session('success') }}</div>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Please fix the errors below</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Scroll-triggered animations
(function() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.anim-on-scroll').forEach(el => observer.observe(el));

  // Staggered item delay from data-delay attribute
  document.querySelectorAll('[data-delay]').forEach(el => {
    el.style.setProperty('--n', el.dataset.delay);
  });
})();
</script>
@stack('scripts')
</body>
</html>
