@extends('layouts.app')
@section('content')
@php
    $sessionUser    = session('user', []);
    $currentUserId  = $sessionUser['id'] ?? null;
    $profileId      = $user['id'] ?? null;
    $profileCreated = $user['created_at'] ?? null;
    $tab            = $tab ?? 'posts';
    
    // Map items depending on the tab actively opened
    if ($tab === 'posts') {
        $posts       = $postsData ? ($postsData['data'] ?? []) : [];
        $currentPage = $postsData['current_page'] ?? 1;
        $lastPage    = $postsData['last_page'] ?? 1;
    } else {
        $network     = $networkData ? ($networkData['data'] ?? []) : [];
        $currentPage = $networkData['current_page'] ?? 1;
        $lastPage    = $networkData['last_page'] ?? 1;
    }
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        {{-- Profile Banner --}}
        <div class="card-kicau overflow-hidden mb-0" style="border-radius:20px 20px 0 0;">
            <div class="profile-banner"></div>
            <div class="pb-3 px-3" style="background:var(--kicau-surface);">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="profile-avatar-wrap ps-0">
                        <img src="{{ $user['avatar_url'] ?? '' }}" class="profile-avatar" alt="{{ $user['name'] ?? '' }}">
                    </div>
                    <div class="pt-3 pb-1">
                        @if($profileId && $currentUserId && $profileId !== $currentUserId)
                            <form action="{{ route('users.follow', $profileId) }}" method="POST">
                                @csrf
                                @if($isFollowing)
                                    <button type="submit" class="btn btn-outline-kicau">
                                        <i class="bi bi-person-check-fill me-1"></i> Mengikuti
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary-kicau">
                                        <i class="bi bi-person-plus-fill me-1"></i> Ikuti
                                    </button>
                                @endif
                            </form>
                        @elseif($profileId && $currentUserId && $profileId === $currentUserId)
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-kicau">
                                <i class="bi bi-pencil me-1"></i> Edit Profil
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-2">
                    <h5 class="fw-bold mb-0" style="color:var(--kicau-text);">{{ $user['name'] ?? '' }}</h5>
                    <span style="color:var(--kicau-text-muted);">{{ '@'.($user['username'] ?? '') }}</span>
                </div>

                @if(!empty($user['bio']))
                    <p class="mt-2 mb-2" style="color:var(--kicau-text);">{{ $user['bio'] }}</p>
                @endif

                <div class="d-flex gap-4 mt-2" style="font-size:0.875rem;">
                    <div>
                        <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'posts']) }}" class="text-decoration-none" style="color:inherit;">
                            <span class="fw-bold" style="color:var(--kicau-text);">{{ $postsCount ?? 0 }}</span>
                            <span style="color:var(--kicau-text-muted);"> Kicauan</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'following']) }}" class="text-decoration-none {{ $tab === 'following' ? 'border-bottom border-primary' : '' }}" style="color:inherit;">
                            <span class="fw-bold" style="color:var(--kicau-text);">{{ $followingCount }}</span>
                            <span style="color:var(--kicau-text-muted);"> Mengikuti</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'followers']) }}" class="text-decoration-none {{ $tab === 'followers' ? 'border-bottom border-primary' : '' }}" style="color:inherit;">
                            <span class="fw-bold" style="color:var(--kicau-text);">{{ $followersCount }}</span>
                            <span style="color:var(--kicau-text-muted);"> Pengikut</span>
                        </a>
                    </div>
                    @if($profileCreated)
                    <div class="d-none d-sm-block ms-auto">
                        <span style="color:var(--kicau-text-muted);">
                            <i class="bi bi-calendar3 me-1"></i>Bergabung {{ \Carbon\Carbon::parse($profileCreated)->format('M Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabs Header --}}
        <div style="background:var(--kicau-surface);border:1px solid var(--kicau-border);border-top:none;border-bottom:none;">
            <ul class="nav nav-fill" style="border-bottom:1px solid var(--kicau-border);">
                <li class="nav-item">
                    <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'posts']) }}" 
                       class="nav-link fw-bold {{ $tab === 'posts' ? 'active' : '' }}" 
                       style="{{ $tab === 'posts' ? 'color:var(--kicau-primary);border-bottom:2px solid var(--kicau-primary);border-radius:0;' : 'color:var(--kicau-text-muted);' }}">
                        Kicauan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'followers']) }}" 
                       class="nav-link fw-bold {{ $tab === 'followers' ? 'active' : '' }}" 
                       style="{{ $tab === 'followers' ? 'color:var(--kicau-primary);border-bottom:2px solid var(--kicau-primary);border-radius:0;' : 'color:var(--kicau-text-muted);' }}">
                        Pengikut
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profile.show', ['username' => $user['username'], 'tab' => 'following']) }}" 
                       class="nav-link fw-bold {{ $tab === 'following' ? 'active' : '' }}" 
                       style="{{ $tab === 'following' ? 'color:var(--kicau-primary);border-bottom:2px solid var(--kicau-primary);border-radius:0;' : 'color:var(--kicau-text-muted);' }}">
                        Mengikuti
                    </a>
                </li>
            </ul>
        </div>

        {{-- Main Render Loop --}}
        <div>
            @if($tab === 'posts')
                @forelse($posts as $post)
                    @php
                        // Profile API posts don't embed user — inject it from the profile $user
                        $postWithUser = array_merge($post, ['user' => $user, 'is_liked' => false]);
                    @endphp
                    @include('partials.post-card', ['post' => $postWithUser])
                @empty
                    <div class="post-card text-center py-5">
                        <div style="font-size:3rem;">🐦</div>
                        <p class="mt-2" style="color:var(--kicau-text-muted);">Belum ada kicauan.</p>
                    </div>
                @endforelse
            @endif

            @if($tab === 'followers' || $tab === 'following')
                <div class="card-kicau p-3 rounded-top-0 mb-4" style="border-top:none;">
                    @forelse($network as $netUser)
                        <div class="d-flex justify-content-between align-items-center mb-{{ $loop->last ? '0' : '3' }}">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('profile.show', $netUser['username']) }}">
                                    <img src="{{ $netUser['avatar_url'] ?? '' }}" class="rounded-circle" width="46" height="46" style="object-fit:cover; border:2px solid var(--kicau-border);" alt="{{ $netUser['name'] }}">
                                </a>
                                <div>
                                    <a href="{{ route('profile.show', $netUser['username']) }}" class="post-username d-block lh-1" style="font-size:0.95rem;">{{ $netUser['name'] }}</a>
                                    <span class="post-handle" style="font-size:0.85rem;">{{ '@'.$netUser['username'] }}</span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <a href="{{ route('profile.show', $netUser['username']) }}" class="btn btn-outline-kicau btn-sm" style="border-radius:999px; font-size:0.8rem; padding: 0.3rem 0.8rem;">
                                    Lihat Profil
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div style="font-size:3rem;">😶</div>
                            <p class="mt-2" style="color:var(--kicau-text-muted);">Tidak ada akun.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Universal Pagination --}}
            @if($lastPage > 1)
            <div class="d-flex justify-content-center gap-2 mt-3">
                @if($currentPage > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="btn btn-outline-kicau btn-sm">
                        <i class="bi bi-chevron-left"></i> Sebelumnya
                    </a>
                @endif
                <span class="btn btn-sm" style="color:var(--kicau-text-muted);">Halaman {{ $currentPage }} / {{ $lastPage }}</span>
                @if($currentPage < $lastPage)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="btn btn-outline-kicau btn-sm">
                        Berikutnya <i class="bi bi-chevron-right"></i>
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
