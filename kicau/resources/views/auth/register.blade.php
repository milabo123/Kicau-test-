<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kicau - Daftar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --kicau-primary: #6C63FF; --kicau-secondary: #FF6584;
            --kicau-bg: #0F0F1A; --kicau-surface: #1A1A2E;
            --kicau-surface2: #222236; --kicau-border: #2E2E4A;
            --kicau-text: #E8E8F0; --kicau-text-muted: #8888AA;
        }
        body { font-family:'Inter',sans-serif; background:var(--kicau-bg); color:var(--kicau-text); min-height:100vh; display:flex; align-items:center; }
        .auth-card { background:var(--kicau-surface); border:1px solid var(--kicau-border); border-radius:24px; padding:2.5rem; }
        .brand { font-family:'Poppins',sans-serif; font-weight:800; font-size:2rem; background:linear-gradient(135deg,var(--kicau-primary),var(--kicau-secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .form-control { background:var(--kicau-surface2) !important; border:1px solid var(--kicau-border) !important; color:var(--kicau-text) !important; border-radius:10px !important; padding:0.65rem 0.9rem; }
        .form-control:focus { border-color:var(--kicau-primary) !important; box-shadow:0 0 0 3px rgba(108,99,255,0.15) !important; }
        .form-control::placeholder { color:var(--kicau-text-muted) !important; }
        .form-label { color:var(--kicau-text-muted); font-size:0.875rem; font-weight:500; }
        .btn-kicau { background:linear-gradient(135deg,var(--kicau-primary),#5a52d5); border:none; color:#fff; border-radius:999px; padding:0.6rem 1.5rem; font-weight:600; width:100%; transition:all 0.2s; box-shadow:0 4px 15px rgba(108,99,255,0.3); }
        .btn-kicau:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(108,99,255,0.45); color:#fff; }
        .bg-blob { position:fixed; border-radius:50%; filter:blur(80px); opacity:0.12; pointer-events:none; }
        .input-group-text { background:var(--kicau-surface2) !important; border:1px solid var(--kicau-border) !important; color:var(--kicau-text-muted) !important; }
    </style>
</head>
<body>
    <div class="bg-blob" style="width:500px;height:500px;background:var(--kicau-primary);top:-200px;right:-100px;"></div>
    <div class="bg-blob" style="width:350px;height:350px;background:var(--kicau-secondary);bottom:-100px;left:-100px;"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <div class="brand">🐦 Kicau</div>
                    <p style="color:var(--kicau-text-muted);">Bergabung dan mulai berbagi!</p>
                </div>

                <div class="auth-card">
                    @if($errors->any())
                    <div class="alert mb-3" style="background:rgba(255,77,109,0.1);border:1px solid rgba(255,77,109,0.3);color:#FF4D6D;border-radius:12px;font-size:0.875rem;">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Nama kamu" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                                    placeholder="username_unik" required style="border-left:none !important;">
                            </div>
                            <small style="color:var(--kicau-text-muted);">Hanya huruf, angka, _ dan -</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                placeholder="kamu@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                        </div>

                        <button type="submit" class="btn btn-kicau">
                            <i class="bi bi-person-plus-fill me-2"></i>Buat Akun
                        </button>
                    </form>

                    <div class="text-center mt-4" style="font-size:0.875rem;color:var(--kicau-text-muted);">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" style="color:var(--kicau-primary);font-weight:600;">Masuk di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
