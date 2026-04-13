@extends('layouts.app')
@section('content')
@php
    $sessionUser = session('user', []);
    $posts       = $postsData['data'] ?? [];
    $currentPage = $postsData['current_page'] ?? 1;
    $lastPage    = $postsData['last_page'] ?? 1;
    $total       = $postsData['total'] ?? 0;
    $filter      = $filter ?? 'all';
@endphp

<div class="row g-4">

    {{-- Left Sidebar --}}
    <div class="col-lg-2 d-none d-lg-block">
        <div style="position:sticky;top:80px;">
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('feed.index') }}" class="nav-link-kicau {{ request()->routeIs('feed.index') ? 'active' : '' }}">
                    <i class="bi bi-house-fill"></i> <span>Beranda</span>
                </a>
                <a href="{{ route('profile.show', $sessionUser['username'] ?? '') }}" class="nav-link-kicau">
                    <i class="bi bi-person-fill"></i> <span>Profil</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-link-kicau">
                    <i class="bi bi-gear-fill"></i> <span>Pengaturan</span>
                </a>
                <div class="mt-3">
                    <button class="btn-primary-kicau w-100 btn" data-bs-toggle="modal" data-bs-target="#composeModal">
                        <i class="bi bi-feather me-1"></i> Kicau Baru
                    </button>
                </div>
            </nav>
        </div>
    </div>

    {{-- Feed --}}
    <div class="col-lg-7 col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--kicau-text);">
                <i class="bi bi-house-fill me-2" style="color:var(--kicau-primary);"></i>Beranda
            </h5>
        </div>

        {{-- Tabs Header --}}
        <div class="mb-3" style="background:var(--kicau-surface); border:1px solid var(--kicau-border); border-radius:12px; overflow:hidden;">
            <ul class="nav nav-fill">
                <li class="nav-item">
                    <a href="{{ route('feed.index', ['filter' => 'all']) }}" 
                       class="nav-link fw-bold py-3 {{ $filter === 'all' ? 'active' : '' }}" 
                       style="{{ $filter === 'all' ? 'color:var(--kicau-primary); background:rgba(108,99,255,0.05); border-bottom:3px solid var(--kicau-primary); border-radius:0;' : 'color:var(--kicau-text-muted);' }}">
                        Semua
                    </a>
                </li>
                <li class="nav-item border-start" style="border-color:var(--kicau-border)!important;">
                    <a href="{{ route('feed.index', ['filter' => 'following']) }}" 
                       class="nav-link fw-bold py-3 {{ $filter === 'following' ? 'active' : '' }}" 
                       style="{{ $filter === 'following' ? 'color:var(--kicau-primary); background:rgba(108,99,255,0.05); border-bottom:3px solid var(--kicau-primary); border-radius:0;' : 'color:var(--kicau-text-muted);' }}">
                        Mengikuti
                    </a>
                </li>
            </ul>
        </div>

        {{-- Compose Box --}}
        <div class="compose-box">
            <div class="d-flex gap-3">
                <img src="{{ $sessionUser['avatar_url'] ?? '' }}" class="post-avatar" alt="avatar">
                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="flex-grow-1" id="compose-form">
                    @csrf
                    <textarea name="body" id="compose-body" class="form-control compose-input mb-2 @error('body') is-invalid @enderror" rows="3"
                        placeholder="Apa yang sedang kamu pikirkan?" maxlength="500">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback d-block mb-2 mt-n1" style="color:#FF6584; font-size: 0.85rem;">{{ $message }}</div>
                    @enderror
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label for="media-upload" class="btn-action" style="cursor:pointer;" title="Tambah Foto/Video">
                                <i class="bi bi-image" style="font-size:1.1rem;"></i>
                                <i class="bi bi-camera-video ms-1" style="font-size:1.1rem;"></i>
                            </label>
                            <input type="file" name="media" id="media-upload" accept="image/*,video/*" style="display:none;" onchange="previewMedia(this)">
                            <div id="media-preview">
                                <img id="preview-img" src="" alt="preview" style="display:none;">
                                <video id="preview-vid" controls style="display:none;"></video>
                                <button type="button" class="btn-action" onclick="clearMedia()" title="Hapus media">
                                    <i class="bi bi-x-circle text-danger"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="char-count">0/500</span>
                            <button type="submit" class="btn-primary-kicau btn btn-sm">
                                <i class="bi bi-send-fill me-1"></i>Kicau!
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Posts --}}
        @forelse($posts as $post)
            @include('partials.post-card', ['post' => $post])
        @empty
            <div class="text-center py-5">
                <div style="font-size:4rem;">🐦</div>
                <h5 class="mt-3" style="color:var(--kicau-text-muted);">Belum ada kicauan</h5>
                <p style="color:var(--kicau-text-muted);">Ikuti pengguna lain atau buat kicauan pertamamu!</p>
            </div>
        @endforelse

        {{-- Pagination manual dari API --}}
        @if($lastPage > 1)
        <div class="d-flex justify-content-center gap-2 mt-3">
            @if($currentPage > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1, 'filter' => $filter]) }}" class="btn btn-outline-kicau btn-sm">
                    <i class="bi bi-chevron-left"></i> Sebelumnya
                </a>
            @endif
            <span class="btn btn-sm" style="color:var(--kicau-text-muted);">Halaman {{ $currentPage }} / {{ $lastPage }}</span>
            @if($currentPage < $lastPage)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1, 'filter' => $filter]) }}" class="btn btn-outline-kicau btn-sm">
                    Berikutnya <i class="bi bi-chevron-right"></i>
                </a>
            @endif
        </div>
        @endif
    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-3 d-none d-lg-block sidebar-col">
        <div style="position:sticky;top:80px;" class="d-flex flex-column gap-3">
            {{-- My Profile Card --}}
            <div class="sidebar-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $sessionUser['avatar_url'] ?? '' }}" class="rounded-circle" width="52" height="52" style="object-fit:cover;border:2px solid var(--kicau-primary);" alt="avatar">
                    <div>
                        <div class="fw-bold" style="color:var(--kicau-text);">{{ $sessionUser['name'] ?? '' }}</div>
                        <div style="color:var(--kicau-text-muted);font-size:0.85rem;">{{ '@'.($sessionUser['username'] ?? '') }}</div>
                    </div>
                </div>
                @if(!empty($sessionUser['bio']))
                    <p class="mt-2 mb-0" style="font-size:0.875rem;color:var(--kicau-text-muted);">{{ $sessionUser['bio'] }}</p>
                @endif
            </div>

            {{-- Trending Topics --}}
            <div class="sidebar-card">
                <h6 class="fw-bold mb-3" style="color:var(--kicau-text);">
                    <i class="bi bi-graph-up-arrow me-2" style="color:var(--kicau-primary);"></i>Sedang Tren
                </h6>
                <div class="d-flex flex-column gap-3">
                    @forelse($trendingTags ?? [] as $topic)
                        <div>
                            <a href="{{ route('search.index', ['q' => '#'.$topic['tag']]) }}" class="text-decoration-none fw-bold" style="color:var(--kicau-text); display:block;">
                                {{ '#'.ucfirst($topic['tag']) }}
                            </a>
                            <span style="font-size:0.8rem; color:var(--kicau-text-muted);">{{ $topic['count'] }} kicauan</span>
                        </div>
                    @empty
                        <div style="font-size:0.85rem; color:var(--kicau-text-muted);">Belum ada tren saat ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Mobile compose FAB --}}
<div class="d-lg-none" style="position:fixed;bottom:24px;right:24px;z-index:999;">
    <button class="btn btn-primary-kicau rounded-circle shadow-lg" style="width:56px;height:56px;font-size:1.5rem;" data-bs-toggle="modal" data-bs-target="#composeModal">
        <i class="bi bi-feather"></i>
    </button>
</div>

{{-- Mobile Compose Modal --}}
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--kicau-surface);border:1px solid var(--kicau-border);border-radius:20px;">
            <div class="modal-header" style="border-color:var(--kicau-border);">
                <h6 class="modal-title fw-bold" style="color:var(--kicau-text);">
                    <i class="bi bi-feather me-2" style="color:var(--kicau-primary);"></i>Kicau Baru
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <textarea name="body" class="form-control compose-input mb-3 @error('body') is-invalid @enderror" rows="4"
                        placeholder="Apa yang ingin kamu bagikan?" maxlength="500">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback d-block mb-3 mt-n2" style="color:#FF6584; font-size: 0.85rem;">{{ $message }}</div>
                    @enderror
                    <div class="mb-3">
                        <label class="form-label-kicau">Tambah Foto/Video</label>
                        <input type="file" name="media" class="form-control form-control-kicau" accept="image/*,video/*">
                    </div>
                    <button type="submit" class="btn btn-primary-kicau w-100">
                        <i class="bi bi-send-fill me-1"></i> Kirim Kicauan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Char counter
const composeBody = document.getElementById('compose-body');
const charCount = document.getElementById('char-count');
if (composeBody) {
    composeBody.addEventListener('input', function() {
        const len = this.value.length;
        charCount.textContent = len + '/500';
        charCount.classList.toggle('warning', len > 450);
    });
}

// Media preview
function previewMedia(input) {
    const preview = document.getElementById('media-preview');
    const previewImg = document.getElementById('preview-img');
    const previewVid = document.getElementById('preview-vid');
    preview.style.display = 'block';
    previewImg.style.display = 'none';
    previewVid.style.display = 'none';

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const url = URL.createObjectURL(file);

        if (file.type.startsWith('video/')) {
            previewVid.src = url;
            previewVid.style.display = 'block';
        } else {
            previewImg.src = url;
            previewImg.style.display = 'block';
        }
    }
}

function clearMedia() {
    document.getElementById('media-upload').value = '';
    document.getElementById('media-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
    document.getElementById('preview-vid').src = '';
}
</script>
@endpush
@endsection
