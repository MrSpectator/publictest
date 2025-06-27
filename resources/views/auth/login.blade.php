@php
    // Placeholder logo and promo image
    $logo = asset('images/isalesbook-logo.png');
    $promo = asset('images/promo-login.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | iSalesBook</title>
    <style>
        body { background: #f5f7fa; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        .main-container { display: flex; min-height: 100vh; }
        .login-left { flex: 1; background: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px 0; }
        .login-form-box { width: 100%; max-width: 400px; }
        .logo { width: 120px; margin-bottom: 30px; }
        .welcome-title { font-size: 2rem; font-weight: 600; margin-bottom: 10px; }
        .welcome-emoji { font-size: 1.5rem; }
        .form-label { font-weight: 500; margin-bottom: 5px; display: block; }
        .form-input { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 18px; font-size: 1rem; }
        .form-links { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .form-links a { color: #DA612B; text-decoration: none; font-size: 0.98rem; }
        .form-links a:hover { text-decoration: underline; }
        .btn-primary { width: 100%; background: #DA612B; color: #fff; border: none; border-radius: 6px; padding: 14px 0; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-bottom: 16px; transition: background 0.2s; }
        .btn-primary:hover { background: #b94e1c; }
        .btn-google { width: 100%; background: #fff; color: #444; border: 1px solid #ddd; border-radius: 6px; padding: 12px 0; font-size: 1rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 18px; }
        .btn-google img { width: 20px; }
        .or-divider { text-align: center; color: #aaa; margin: 10px 0; font-size: 0.95rem; }
        .signup-link { text-align: center; margin-top: 10px; font-size: 0.98rem; }
        .signup-link a { color: #DA612B; text-decoration: none; font-weight: 500; }
        .signup-link a:hover { text-decoration: underline; }
        .support-link { text-align: center; margin-top: 8px; font-size: 0.95rem; color: #888; }
        .support-link a { color: #DA612B; text-decoration: none; }
        .support-link a:hover { text-decoration: underline; }
        .login-footer { text-align: center; color: #aaa; font-size: 0.93rem; margin-top: 30px; }
        .login-right { flex: 1; background: #f8f8fa; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .promo-box { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 18px; box-shadow: 0 4px 24px rgba(218,97,43,0.08); padding: 36px 32px; max-width: 420px; text-align: center; }
        .promo-title { font-size: 1.3rem; font-weight: 600; color: #DA612B; margin-bottom: 18px; }
        .promo-img { width: 220px; border-radius: 10px; margin-bottom: 18px; }
        @media (max-width: 900px) { .main-container { flex-direction: column; } .login-right { display: none; } }
        @media (max-width: 480px) { .login-form-box { max-width: 98vw; padding: 0 8px; } .promo-box { padding: 18px 8px; } }
    </style>
</head>
<body>
<div class="main-container">
    <div class="login-left">
        <img src="{{ $logo }}" alt="iSalesBook Logo" class="logo">
        <div class="login-form-box">
            <div class="welcome-title">Welcome to iSalesBook <span class="welcome-emoji">👋</span></div>
            <form id="loginForm" method="POST" action="{{ route('auth.login') }}">
                @csrf
                <label class="form-label" for="email">Email</label>
                <input class="form-input" type="email" id="email" name="email" placeholder="Enter your email" required value="{{ old('email') }}">
                <label class="form-label" for="org_code">Organizational Code</label>
                <input class="form-input" type="text" id="org_code" name="org_code" placeholder="Enter your organizational code" required maxlength="5" pattern="[0-9]{5}" value="{{ old('org_code') }}">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" placeholder="Enter your password" required>
                <div class="form-links">
                    <a href="{{ route('auth.forgot-password') }}">Forgot Password?</a>
                    <span>or</span>
                    <a href="/api/registration/verify-email">Verify Email</a>
                </div>
                <button type="submit" class="btn-primary">Get Started</button>
                <div class="or-divider">OR</div>
                <button type="button" class="btn-google"><img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">Sign in with Google</button>
            </form>
            <div class="signup-link">Don't have an account? <a href="#">Sign up</a></div>
            <div class="support-link">Having trouble signing in? <a href="#">Contact Support</a></div>
            <div class="login-footer">© 2024 iSalesBook. All Rights Reserved</div>
        </div>
    </div>
    <div class="login-right">
        <div class="promo-box">
            <div class="promo-title">Unleash the Power of Efficient Inventory Management</div>
            <img src="{{ $promo }}" alt="Promo" class="promo-img">
        </div>
    </div>
</div>
<script>
    document.getElementById('org_code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 5);
    });
</script>
</body>
</html> 