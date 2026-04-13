<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kicau - {{ $title ?? 'Beranda' }}</title>
    <meta name="description" content="Kicau — Platform berbagi momen, pikiran, dan cerita bersama jutaan orang.">

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --kicau-primary: #6C63FF;
            --kicau-primary-dark: #5a52d5;
            --kicau-secondary: #FF6584;
            --kicau-accent: #43E97B;
            --kicau-bg: #0F0F1A;
            --kicau-surface: #1A1A2E;
            --kicau-surface2: #222236;
            --kicau-border: #2E2E4A;
            --kicau-text: #E8E8F0;
            --kicau-text-muted: #8888AA;
            --kicau-like: #FF4D6D;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--kicau-bg);
            color: var(--kicau-text);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar-kicau {
            background: rgba(26, 26, 46, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--kicau-border);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand-kicau {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, var(--kicau-primary), var(--kicau-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        /* ── Cards ── */
        .card-kicau {
            background: var(--kicau-surface);
            border: 1px solid var(--kicau-border);
            border-radius: 16px;
            transition: transform 0.2s, border-color 0.2s;
        }
        .card-kicau:hover { border-color: var(--kicau-primary); }

        /* ── Post Card ── */
        .post-card {
            background: var(--kicau-surface);
            border: 1px solid var(--kicau-border);
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            transition: border-color 0.25s, transform 0.2s;
        }
        .post-card:hover { border-color: rgba(108,99,255,0.4); }

        .post-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--kicau-primary);
            flex-shrink: 0;
        }

        .post-username {
            font-weight: 600;
            color: var(--kicau-text);
            text-decoration: none;
        }
        .post-username:hover { color: var(--kicau-primary); }

        .post-handle {
            color: var(--kicau-text-muted);
            font-size: 0.875rem;
        }

        .post-body {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--kicau-text);
            margin: 0.75rem 0;
        }

        .post-media {
            border-radius: 12px;
            max-height: 520px;
            width: 100%;
            object-fit: cover;
            margin-top: 0.75rem;
        }

        /* ── Action Buttons ── */
        .btn-action {
            background: none;
            border: none;
            color: var(--kicau-text-muted);
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .btn-action:hover { background: rgba(108,99,255,0.1); color: var(--kicau-primary); }
        .btn-action.liked { color: var(--kicau-like); }
        .btn-action.liked:hover { background: rgba(255,77,109,0.1); }

        /* ── Compose Box ── */
        .compose-box {
            background: var(--kicau-surface);
            border: 1px solid var(--kicau-border);
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .compose-input {
            background: var(--kicau-surface2) !important;
            border: 1px solid var(--kicau-border) !important;
            color: var(--kicau-text) !important;
            border-radius: 12px !important;
            resize: none;
            transition: border-color 0.2s;
        }
        .compose-input:focus {
            border-color: var(--kicau-primary) !important;
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15) !important;
        }
        .compose-input::placeholder { color: var(--kicau-text-muted) !important; }

        /* ── Buttons ── */
        .btn-primary-kicau {
            background: linear-gradient(135deg, var(--kicau-primary), var(--kicau-primary-dark));
            border: none;
            color: #fff;
            border-radius: 999px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(108,99,255,0.3);
        }
        .btn-primary-kicau:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108,99,255,0.45);
            color: #fff;
        }

        .btn-outline-kicau {
            background: transparent;
            border: 1.5px solid var(--kicau-primary);
            color: var(--kicau-primary);
            border-radius: 999px;
            padding: 0.45rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-outline-kicau:hover {
            background: var(--kicau-primary);
            color: #fff;
        }

        /* ── Sidebar ── */
        .sidebar-card {
            background: var(--kicau-surface);
            border: 1px solid var(--kicau-border);
            border-radius: 20px;
            padding: 1.25rem;
        }
        .sidebar-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 1rem;
        }

        /* ── Media Preview ── */
        #media-preview { display: none; }
        #media-preview img, #media-preview video {
            max-height: 200px;
            border-radius: 12px;
            margin-top: 0.5rem;
            border: 1px solid var(--kicau-border);
        }

        /* ── Comments ── */
        .comment-item {
            border-left: 2px solid var(--kicau-border);
            padding-left: 1rem;
            margin-bottom: 0.75rem;
        }
        .comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* ── Profile ── */
        .profile-banner {
            height: 200px;
            background: linear-gradient(135deg, #6C63FF 0%, #FF6584 50%, #43E97B 100%);
            border-radius: 20px 20px 0 0;
        }
        .profile-avatar-wrap {
            margin-top: -50px;
            padding-left: 1.5rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--kicau-surface);
            object-fit: cover;
        }

        /* ── Auth forms ── */
        .auth-card {
            background: var(--kicau-surface);
            border: 1px solid var(--kicau-border);
            border-radius: 24px;
            padding: 2.5rem;
        }
        .form-control-kicau {
            background: var(--kicau-surface2) !important;
            border: 1px solid var(--kicau-border) !important;
            color: var(--kicau-text) !important;
            border-radius: 10px !important;
            padding: 0.6rem 0.9rem;
            transition: border-color 0.2s;
        }
        .form-control-kicau:focus {
            border-color: var(--kicau-primary) !important;
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15) !important;
        }
        .form-control-kicau::placeholder { color: var(--kicau-text-muted) !important; }
        .form-label-kicau { color: var(--kicau-text-muted); font-size: 0.875rem; font-weight: 500; }

        .nav-link-kicau {
            color: var(--kicau-text-muted);
            border-radius: 12px;
            padding: 0.6rem 0.9rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .nav-link-kicau:hover, .nav-link-kicau.active {
            background: rgba(108,99,255,0.12);
            color: var(--kicau-primary);
        }
        .nav-link-kicau i { font-size: 1.25rem; }

        /* ── Char counter ── */
        #char-count { color: var(--kicau-text-muted); font-size: 0.8rem; }
        #char-count.warning { color: #FF6584; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--kicau-bg); }
        ::-webkit-scrollbar-thumb { background: var(--kicau-border); border-radius: 3px; }

        /* ── Animations ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.4s ease forwards; }

        .dropdown-menu-kicau {
            background: var(--kicau-surface2);
            border: 1px solid var(--kicau-border);
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .dropdown-item-kicau {
            color: var(--kicau-text);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            margin: 2px 4px;
            transition: background 0.15s;
        }
        .dropdown-item-kicau:hover { background: rgba(108,99,255,0.12); color: var(--kicau-primary); }
        .dropdown-divider-kicau { border-color: var(--kicau-border); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar-col { display: none; }
            .post-card { border-radius: 12px; }
        }
    </style>
</head>
<body>

{{-- Navbar --}}
<nav class="navbar-kicau px-3">
    <div class="container-xl d-flex align-items-center justify-content-between">
        <a class="navbar-brand-kicau" href="{{ route('feed.index') }}">
            🐦 Kicau
        </a>

        @if(session('api_token'))
        @php $sessionUser = session('user', []); @endphp
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 p-1 pe-2" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);border-radius:999px;" data-bs-toggle="dropdown">
                    <img src="{{ $sessionUser['avatar_url'] ?? 'https://ui-avatars.com/api/?name=User&background=6C63FF&color=fff&size=128' }}" class="rounded-circle" width="34" height="34" style="object-fit:cover;" alt="{{ $sessionUser['name'] ?? 'User' }}">
                    <span class="d-none d-md-inline" style="font-size:0.875rem;font-weight:600;color:var(--kicau-text);">{{ $sessionUser['name'] ?? 'User' }}</span>
                    <i class="bi bi-chevron-down" style="font-size:0.75rem;color:var(--kicau-text-muted);"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-kicau mt-2" style="min-width:200px;">
                    <li>
                        <a class="dropdown-item dropdown-item-kicau" href="{{ route('profile.show', $sessionUser['username'] ?? '') }}">
                            <i class="bi bi-person me-2"></i> Profil Saya
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-kicau" href="{{ route('profile.edit') }}">
                            <i class="bi bi-gear me-2"></i> Pengaturan
                        </a>
                    </li>
                    <li><hr class="dropdown-divider dropdown-divider-kicau"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <button type="button" class="dropdown-item dropdown-item-kicau text-danger" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</nav>

{{-- Flash Messages via SweetAlert2 --}}
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: @json(session('success')),
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#1A1A2E',
            color: '#E8E8F0',
            iconColor: '#43E97B',
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: @json(session('error')),
            background: '#1A1A2E',
            color: '#E8E8F0',
            iconColor: '#FF6584',
            confirmButtonColor: '#6C63FF',
        });
    });
</script>
@endif

{{-- Main Content --}}
<main class="container-xl py-4">
    @yield('content')
</main>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: 'Kamu harus login lagi untuk mengakses Kicau.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#FF4D6D',
        cancelButtonColor: '#6C63FF',
        background: '#1A1A2E',
        color: '#E8E8F0',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logout-form').submit();
        }
    });
}

function confirmDelete(formId, message) {
    Swal.fire({
        title: 'Hapus?',
        text: message || 'Tindakan ini tidak bisa dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#FF4D6D',
        cancelButtonColor: '#6C63FF',
        background: '#1A1A2E',
        color: '#E8E8F0',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function toggleEditPost(postId) {
    const bodyBox = document.getElementById(`post-body-${postId}`);
    const formBox = document.getElementById(`edit-post-form-${postId}`);
    if (bodyBox && formBox) {
        if (formBox.style.display === 'none') {
            bodyBox.style.display = 'none';
            formBox.style.display = 'block';
        } else {
            bodyBox.style.display = 'block';
            formBox.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const likeBtn = e.target.closest('.btn-like');
        if (likeBtn) {
            e.preventDefault();
            const form = likeBtn.closest('.form-like');
            if (!form) return;
            
            const url = form.action;
            const csrf = form.querySelector('input[name="_token"]').value;

            // Optimistic UI updates
            const isLiked = likeBtn.classList.contains('liked');
            const icon = likeBtn.querySelector('i.bi');
            const countEl = likeBtn.querySelector('.likes-count');
            let currentCount = parseInt(countEl.textContent) || 0;

            if (isLiked) {
                likeBtn.classList.remove('liked');
                likeBtn.title = 'Suka';
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                countEl.textContent = Math.max(0, currentCount - 1);
            } else {
                likeBtn.classList.add('liked');
                likeBtn.title = 'Batal suka';
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                countEl.textContent = currentCount + 1;
            }

            likeBtn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_liked !== undefined) {
                    // Sync with actual server response
                    if (data.is_liked) {
                        likeBtn.classList.add('liked');
                        likeBtn.title = 'Batal suka';
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    } else {
                        likeBtn.classList.remove('liked');
                        likeBtn.title = 'Suka';
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                    }
                    countEl.textContent = data.likes_count;
                }
            })
            .catch(err => {
                console.error('Like failed:', err);
                // Revert UI on failure
                if (isLiked) {
                    likeBtn.classList.add('liked');
                    likeBtn.title = 'Batal suka';
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    countEl.textContent = currentCount;
                } else {
                    likeBtn.classList.remove('liked');
                    likeBtn.title = 'Suka';
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    countEl.textContent = currentCount;
                }
            })
            .finally(() => {
                likeBtn.disabled = false;
            });
        }
    });
});
</script>

@stack('scripts')
</body>
</html>
