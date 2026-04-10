@extends('layouts.app')
@section('content')
@php
    $sessionUser    = session('user', []);
    $currentUserId  = $sessionUser['id'] ?? null;
    $profileId      = $user['id'] ?? null;
    $posts          = $postsData['data'] ?? [];
    $currentPage    = $postsData['current_page'] ?? 1;
    $lastPage       = $postsData['last_page'] ?? 1;
    $total          = $postsData['total'] ?? 0;
    $profileCreated = $user['created_at'] ?? null;
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

                <div class="d-flex gap-3 mt-2" style="font-size:0.875rem;">
                    <div>
                        <span class="fw-bold" style="color:var(--kicau-text);">{{ $total }}</span>
                        <span style="color:var(--kicau-text-muted);"> Kicauan</span>
                    </div>
                    <div>
                        <span class="fw-bold" style="color:var(--kicau-text);">{{ $followingCount }}</span>
                        <span style="color:var(--kicau-text-muted);"> Mengikuti</span>
                    </div>
                    <div>
                        <span class="fw-bold" style="color:var(--kicau-text);">{{ $followersCount }}</span>
                        <span style="color:var(--kicau-text-muted);"> Pengikut</span>
                    </div>
                    @if($profileCreated)
                    <div>
                        <span style="color:var(--kicau-text-muted);">
                            <i class="bi bi-calendar3 me-1"></i>Bergabung {{ \Carbon\Carbon::parse($profileCreated)->format('M Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div style="background:var(--kicau-surface);border:1px solid var(--kicau-border);border-top:none;border-bottom:none;">
            <ul class="nav" style="border-bottom:1px solid var(--kicau-border);">
                <li class="nav-item">
                    <span class="nav-link active fw-bold" style="color:var(--kicau-primary);border-bottom:2px solid var(--kicau-primary);border-radius:0;cursor:default;">
                        Kicauan
                    </span>
                </li>
            </ul>
        </div>

        {{-- Posts --}}
        <div>
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

            {{-- Pagination --}}
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
