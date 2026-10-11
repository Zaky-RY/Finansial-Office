<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Finance Office</title>
    <style>
        :root {
            --brand: #176b50;
            --brand-dark: #0f4e3a;
            --brand-soft: #eef7f1;
            --ink: #17221f;
            --muted: #6d7b76;
            --line: #d1ded7;
            --danger: #b42318;
            --danger-bg: #fff0ee;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f7faf8;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrap {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
        }
        .hero {
            background: linear-gradient(145deg, #125741 0%, #176b50 60%, #1f8263 100%);
            color: #fff;
            padding: 8vw 6vw;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .hero-pattern {
            position: absolute;
            width: 500px;
            height: 500px;
            border: 90px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            right: -160px;
            bottom: -150px;
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            width: fit-content;
        }
        .hero h1 {
            font-size: clamp(38px, 5vw, 64px);
            line-height: 1.05;
            letter-spacing: -0.05em;
            margin: 24px 0 16px;
            max-width: 560px;
        }
        .hero p {
            max-width: 440px;
            line-height: 1.65;
            color: #d6eee1;
            font-size: 16px;
            margin: 0;
        }
        .hero-footer {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 40px;
        }
        .login {
            display: grid;
            place-items: center;
            padding: 40px 24px;
            background: #ffffff;
        }
        .box {
            width: min(400px, 100%);
        }
        .brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 40px;
        }
        .brand span {
            color: var(--brand);
        }
        .box h2 {
            font-size: 28px;
            letter-spacing: -0.04em;
            margin: 0 0 6px;
        }
        .muted {
            color: var(--muted);
            font-size: 14px;
        }
        .field {
            margin: 20px 0;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #293832;
        }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px 14px;
            font: inherit;
            font-size: 14px;
            transition: all 0.15s ease;
            background: #fff;
        }
        input:focus {
            outline: 3px solid #d4efe0;
            border-color: var(--brand);
        }
        .btn {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            padding: 13px 18px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.15s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .btn:hover:not(:disabled) {
            background: var(--brand-dark);
        }
        .btn:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }
        .error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
            font-weight: 500;
        }
        .alert {
            background: var(--danger-bg);
            border: 1px solid #fecdca;
            color: var(--danger);
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @media(max-width: 840px) {
            .wrap { grid-template-columns: 1fr; }
            .hero { display: none; }
            .login { padding: 48px 20px; }
            .brand { margin-bottom: 28px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <section class="hero">
        <div class="hero-pattern"></div>
        <div>
            <div class="hero-badge">Enterprise Edition &bull; MVP 1.0</div>
            <h1>Keuangan kantor yang tertata & transparan.</h1>
            <p>Kelola reimbursement, alokasi anggaran, dan persetujuan pengeluaran kantor secara terpusat dengan kepastian audit mutlak.</p>
        </div>
        <div class="hero-footer">
            &copy; {{ date('Y') }} Finance Office. Sistem Manajemen Keuangan Internal.
        </div>
    </section>

    <main class="login">
        <div class="box">
            <div class="brand">Finance <span>Office</span></div>
            <h2>Selamat datang kembali</h2>
            <p class="muted">Masuk dengan kredensial akun Anda untuk mengakses workspace.</p>

            @if($errors->any())
                <div class="alert" role="alert">
                    <span>⚠️</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email kantor</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@office.test">
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field" style="display:flex; justify-content:space-between; align-items:center; margin:16px 0 24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:normal; font-size:13px; cursor:pointer; margin:0;">
                        <input type="checkbox" name="remember" style="width:auto; margin:0;"> Ingat saya di perangkat ini
                    </label>
                </div>

                <button id="submitBtn" class="btn" type="submit">
                    <span>Masuk ke workspace</span>
                </button>
            </form>
        </div>
    </main>
</div>

<script>
    document.getElementById('loginForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> <span>Memverifikasi...</span>';
        }
    });
</script>
</body>
</html>
