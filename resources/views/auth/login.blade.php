<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kasir POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <style>
        :root { --teal:#2BBFA4; --teal-dark:#1E9A87; --teal-light:#E6F9F5; --coral:#FF6B6B; --text:#1A2B3C; --muted:#7A90A8; --border:#E2E8F0; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:linear-gradient(135deg, #E6F9F5 0%, #F0F4F8 50%, #EEF5FD 100%);
               font-family:'Nunito',sans-serif; }
        .login-card { background:white; border-radius:20px; padding:48px 40px; width:100%; max-width:420px;
                      box-shadow:0 20px 60px rgba(0,0,0,.08); border:1px solid var(--border); }
        .login-logo { text-align:center; margin-bottom:32px; }
        .login-logo .icon { font-size:48px; margin-bottom:8px; }
        .login-logo h1 { font-family:'Caveat',cursive; font-size:32px; color:var(--text); }
        .login-logo p { font-size:13px; color:var(--muted); margin-top:4px; }
        .form-group { margin-bottom:20px; }
        .form-label { display:block; font-size:12px; font-weight:800; color:var(--muted);
                      text-transform:uppercase; letter-spacing:.06em; margin-bottom:7px; }
        .form-control { width:100%; padding:12px 16px; border:2px solid var(--border); border-radius:10px;
                        font-size:14px; font-family:'Nunito',sans-serif; color:var(--text);
                        transition:border-color .2s; }
        .form-control:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(43,191,164,.1); }
        .form-error { color:var(--coral); font-size:12px; margin-top:4px; }
        .remember-row { display:flex; align-items:center; gap:8px; margin-bottom:24px; }
        .remember-row input { accent-color:var(--teal); }
        .remember-row label { font-size:13px; color:var(--muted); cursor:pointer; }
        .btn-login { width:100%; padding:14px; background:var(--teal); color:white; border:none;
                     border-radius:10px; font-size:15px; font-weight:800; cursor:pointer;
                     font-family:'Nunito',sans-serif; transition:background .2s;
                     box-shadow:0 4px 16px rgba(43,191,164,.3); }
        .btn-login:hover { background:var(--teal-dark); }
        .alert-danger { background:#FFF0F0; color:var(--coral); padding:12px 16px; border-radius:10px;
                        font-size:13px; font-weight:600; margin-bottom:20px; border:1px solid var(--coral); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <div class="icon">💊</div>
            <h1>Kasir POS</h1>
            <p>Point of Sale System</p>
        </div>

        @if($errors->any())
            <div class="alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                       placeholder="admin@kasirpos.com" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>
