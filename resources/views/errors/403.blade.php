<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - NAAP Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --naap-blue: #0041C7;
            --naap-blue-dark: #0033a0;
        }
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
        }
        .error-container {
            text-align: center;
            max-width: 480px;
            padding: 2rem;
        }
        .error-icon {
            font-size: 5rem;
            color: var(--naap-blue);
            margin-bottom: 1.5rem;
            opacity: 0.85;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--naap-blue);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1rem;
            color: #555;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home {
            background-color: var(--naap-blue);
            color: #fff;
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        .btn-home:hover {
            background-color: var(--naap-blue-dark);
            color: #fff;
        }
        .brand {
            margin-top: 2.5rem;
            font-size: 0.85rem;
            color: #999;
        }
        .brand strong {
            color: var(--naap-blue);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-shield-lock error-icon"></i>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            You do not have permission to access this page. Please contact an administrator if you believe this is an error.
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door"></i> Back to Home
        </a>
        <div class="brand">
            <strong>NAAP</strong> Lost & Found System
        </div>
    </div>
</body>
</html>
