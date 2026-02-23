<!doctype html>
<html lang="en">
<head>
  <title>@yield('title', 'Lost & Found')</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('css/white-black-theme.css') }}" rel="stylesheet" />
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
@stack('scripts')
</body>
</html>
