@php
    $logo = asset('images/isalesbook-logo.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | iSalesBook</title>
    <style>
        body { background: #f5f7fa; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        .main-container { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .reset-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(218,97,43,0.08); padding: 38px 32px; max-width: 370px; width: 100%; text-align: center; }
        .logo { width: 110px; margin-bottom: 18px; }
        .reset-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 10px; letter-spacing: 1px; }
        .reset-instruction { color: #DA612B; background: #fff3e0; border-radius: 6px; padding: 8px 0; margin-bottom: 18px; font-size: 1rem; }
        .form-label { font-weight: 500; margin-bottom: 5px; display: block; text-align: left; }
        .form-input { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 18px; font-size: 1rem; }
        .form-input.error { border-color: #dc3545; }
        .error-message { color: #dc3545; font-size: 0.9rem; margin-bottom: 15px; text-align: left; }
        .btn-primary { width: 100%; background: #DA612B; color: #fff; border: none; border-radius: 6px; padding: 14px 0; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-bottom: 10px; transition: background 0.2s; }
        .btn-primary:hover { background: #b94e1c; }
        .back-link { display: block; margin-bottom: 18px; color: #444; text-decoration: none; font-size: 0.98rem; font-weight: 500; }
        .back-link:hover { text-decoration: underline; color: #DA612B; }
        @media (max-width: 480px) { .reset-card { padding: 18px 8px; } }
    </style>
</head>
<body>
<div class="main-container">
    <form class="reset-card" method="POST" action="/forgot-password">
        @csrf
        <img src="{{ $logo }}" alt="iSalesBook Logo" class="logo">
        <div class="reset-title">RESET PASSWORD</div>
        <div class="reset-instruction">Please enter the email address associated with your account</div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
        
        <label class="form-label" for="email">Email</label>
        <input class="form-input @error('email') error @enderror" type="email" id="email" name="email" placeholder="Enter email address" value="{{ old('email') }}" required>
        <a href="{{ route('auth.login') }}" class="back-link">Back to login</a>
        <button type="submit" class="btn-primary">Proceed</button>
    </form>
</div>
</body>
</html> 