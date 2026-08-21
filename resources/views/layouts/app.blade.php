<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- SEO Meta Tags -->
  <title>@yield('title', 'NAAP Lost & Found — AI-Powered Item Recovery')</title>
  <meta name="description" content="@yield('meta_description', 'NAAP Lost & Found System — Report lost or found items and get AI-powered matches to recover your belongings faster. National Aviation Academy of the Philippines.')" />
  <meta name="keywords" content="lost and found, NAAP, National Aviation Academy of the Philippines, lost items, found items, AI matching" />
  <meta name="author" content="NAAP" />
  <meta name="robots" content="index, follow" />

  <!-- Open Graph / Social -->
  <meta property="og:type" content="website" />
  <meta property="og:title" content="@yield('og_title', 'NAAP Lost & Found')" />
  <meta property="og:description" content="@yield('og_description', 'AI-powered lost and found system for NAAP. Report and recover your items faster.')" />
  <meta property="og:image" content="{{ asset('image.png') }}" />
  <meta property="og:site_name" content="NAAP Lost & Found" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="@yield('og_title', 'NAAP Lost & Found')" />
  <meta name="twitter:description" content="@yield('og_description', 'AI-powered lost and found system for NAAP.')" />

  <!-- Canonical URL -->
  <link rel="canonical" href="{{ url()->current() }}" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('css/white-black-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/layout.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/animations.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/wow-features.css') }}" rel="stylesheet" />

  <!-- Critical inline styles for images -->
  <style>
    img { max-width: 100%; height: auto; }
    .navbar-logo { width: 36px; height: 36px; overflow: hidden; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: white; }
    .navbar-logo img { width: 36px !important; height: 36px !important; max-width: 36px !important; object-fit: cover; display: block; border-radius: 50%; mix-blend-mode: multiply; }
    .user-avatar { width: 36px; height: 36px; overflow: hidden; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .user-avatar img { width: 36px !important; height: 36px !important; max-width: 36px !important; object-fit: cover; display: block; }
    .user-avatar-fallback { width: 100%; height: 100%; display: flex !important; align-items: center; justify-content: center; background: linear-gradient(135deg, #0041C7 0%, #0D85D8 100%); color: white; font-weight: 700; font-size: var(--text-sm); }
    .dropdown-avatar { width: 36px; height: 36px; overflow: hidden; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .dropdown-avatar img { width: 36px !important; height: 36px !important; object-fit: cover; display: block; }
    .dropdown-avatar-fallback { width: 100%; height: 100%; display: flex !important; align-items: center; justify-content: center; background: linear-gradient(135deg, #0041C7 0%, #0D85D8 100%); color: white; font-weight: 700; font-size: var(--text-sm); }
    .dash-avatar { width: 56px; height: 56px; overflow: hidden; border-radius: 16px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .dash-avatar img { width: 56px !important; height: 56px !important; max-width: 56px !important; object-fit: cover; display: block; }
    .dash-avatar-initial { width: 100%; height: 100%; display: flex !important; align-items: center; justify-content: center; }
    body.has-navbar { padding-top: 70px; }
  </style>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('image.png') }}" sizes="192x192" />
  <link rel="apple-touch-icon" href="{{ asset('image.png') }}" sizes="180x180" />
  <meta name="theme-color" content="#0041C7" />

  <!-- PWA -->
  <link rel="manifest" href="{{ asset('manifest.json') }}" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />
  <meta name="apple-mobile-web-app-title" content="NAAP LF" />

  @stack('styles')
</head>
<body class="has-navbar">

<!-- Skip to main content (accessibility) -->
<a href="#main-content" class="skip-link">Skip to main content</a>

@auth
  @include('components.layout.navbar')
  @include('components.layout.sidebar')
@endauth

<!-- Confirmation Modal -->
@include('components.confirmation-modal')

<main id="main-content" class="content-wrap">
  <div class="container py-4">
    @auth
    <div id="breadcrumbs" class="breadcrumbs"></div>
    @endauth
    @yield('content')
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/toast.js') }}"></script>
<script src="{{ asset('js/wow-features.js') }}"></script>
<script src="{{ asset('js/advanced-ui.js') }}"></script>
<script src="{{ asset('js/export-share.js') }}"></script>
<script src="{{ asset('js/realtime.js') }}"></script>
<script src="{{ asset('js/analytics.js') }}"></script>
<script src="{{ asset('js/bookmarks-compare.js') }}"></script>
<script src="{{ asset('js/account-security.js') }}"></script>
<script src="{{ asset('js/pwa.js') }}"></script>
<script src="{{ asset('js/comments.js') }}"></script>
<script src="{{ asset('js/advanced-search.js') }}"></script>
<script src="{{ asset('js/templates.js') }}"></script>
<script src="{{ asset('js/batch-leaderboard.js') }}"></script>
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

  // Dark mode toggle
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
  }

  const darkToggle = document.getElementById('darkModeToggle');
  if (darkToggle) {
    darkToggle.addEventListener('click', function() {
      document.body.classList.toggle('dark-mode');
      localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
      // Update icon
      const icon = this.querySelector('i');
      icon.className = document.body.classList.contains('dark-mode') ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    });
    // Set initial icon
    const icon = darkToggle.querySelector('i');
    if (savedTheme === 'dark') icon.className = 'bi bi-sun-fill';
  }

  // Toast for flash messages
  @if(session('success'))
    showToast('{{ session("success") }}', 'success');
  @endif
  @if($errors->any())
    showToast('{{ $errors->first() }}', 'error');
  @endif
})();
</script>
@stack('scripts')
</body>
</html>
