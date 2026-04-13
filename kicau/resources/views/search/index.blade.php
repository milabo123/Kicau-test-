@extends('layouts.app')

@section('content')
@php
    $sessionUser   = session('user', []);
    $currentUserId = $sessionUser['id'] ?? null;
    $posts         = $postsData['data'] ?? [];
    $currentPage   = $postsData['current_page'] ?? 1;
    $lastPage      = $postsData['last_page'] ?? 1;
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8 ps-lg-4">
        
        {{-- Mobile Search Bar (Since navbar hides it on md down) --}}
        <div class="d-md-none mb-3">
            <form action="{{ route('search.index') }}" method="GET">
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--kicau-surface2); border:1px solid var(--kicau-border); border-right:none; color:var(--kicau-text-muted);">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control form-control-kicau" style="border-left:none; padding-left:0.5rem; background:var(--kicau-surface2);" placeholder="Cari kicauan, @akun, atau #hashtag" value="{{ request('q') }}">
                </div>
            </form>
        </div>

        <h4 class="fw-bold mb-3" style="font-family:'Poppins', sans-serif;">
            Hasil Pencarian: <span style="color:var(--kicau-primary);">"{{ $q }}"</span>
        </h4>

        @if(empty($q))
            <div class="card-kicau text-center py-5">
                <div style="font-size:3rem;">🔎</div>
                <p class="mt-2 mb-0" style="color:var(--kicau-text-muted);">Ketik sesuatu di atas untuk mulai mencari.</p>
            </div>
        @else

            {{-- Users Result --}}
            @if(count($users) > 0)
                <h6 class="fw-bold mb-3 text-uppercase" style="color:var(--kicau-text-muted); font-size:0.85rem; letter-spacing:1px;">Pengguna</h6>
                <div class="card-kicau p-3 mb-4">
                    @foreach($users as $user)
                        <div class="d-flex justify-content-between align-items-center mb-{{ $loop->last ? '0' : '3' }}">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('profile.show', $user['username']) }}">
                                    <img src="{{ $user['avatar_url'] ?? '' }}" class="rounded-circle" width="46" height="46" style="object-fit:cover; border:2px solid var(--kicau-border);" alt="{{ $user['name'] }}">
                                </a>
                                <div>
                                    <a href="{{ route('profile.show', $user['username']) }}" class="post-username d-block lh-1" style="font-size:0.95rem;">{{ $user['name'] }}</a>
                                    <span class="post-handle" style="font-size:0.85rem;">{{ '@'.$user['username'] }}</span>
                                </div>
                            </div>
                            @if($currentUserId && $user['id'] !== $currentUserId)
                            <form action="{{ route('users.follow', $user['id']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-kicau btn-sm" style="border-radius:999px; font-size:0.8rem; padding: 0.3rem 0.8rem;">
                                    Ikuti
                                </button>
                            </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Posts Result --}}
            <h6 class="fw-bold mb-3 text-uppercase" style="color:var(--kicau-text-muted); font-size:0.85rem; letter-spacing:1px;">Kicauan Terbaru</h6>
            <div>
                @forelse($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    @if(count($users) === 0)
                        <div class="card-kicau text-center py-5">
                            <div style="font-size:3rem;">🏜️</div>
                            <p class="mt-2 mb-0" style="color:var(--kicau-text-muted);">Tidak ada hasil yang ditemukan untuk pencarian ini.</p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="mb-0" style="color:var(--kicau-text-muted); font-size:0.9rem;">Tidak ada kicauan terkait.</p>
                        </div>
                    @endif
                @endforelse

                {{-- Pagination --}}
                @if($lastPage > 1)
                <div class="d-flex justify-content-center gap-2 mt-4">
                    @if($currentPage > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="btn btn-outline-kicau btn-sm">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    @endif
                    
                    <span class="btn btn-sm" style="color:var(--kicau-text-muted); cursor:default;">Hal {{ $currentPage }} / {{ $lastPage }}</span>
                    
                    @if($currentPage < $lastPage)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="btn btn-outline-kicau btn-sm">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
                @endif
            </div>

        @endif
    </div>
</div>
@endsection
