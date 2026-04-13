@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 fade-in-up">
            <h4 class="mb-0" style="font-family:'Poppins',sans-serif; font-weight:700;">
                <i class="bi bi-bell-fill me-2" style="color:var(--kicau-primary);"></i>Notifikasi
            </h4>
            @if($unreadCount > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-kicau">
                    <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                </button>
            </form>
            @endif
        </div>

        {{-- Notification List --}}
        @forelse($notifications as $index => $notif)
        <div class="post-card fade-in-up d-flex align-items-start gap-3 {{ empty($notif['read_at']) ? '' : '' }}"
             style="animation-delay: {{ $index * 0.05 }}s; {{ empty($notif['read_at']) ? 'border-left: 3px solid var(--kicau-primary);' : 'opacity: 0.7;' }}">

            {{-- Icon berdasarkan tipe --}}
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                 style="width:46px; height:46px;
                 @if($notif['type'] === 'like')
                     background: rgba(255,77,109,0.15);
                 @elseif($notif['type'] === 'comment')
                     background: rgba(108,99,255,0.15);
                 @elseif($notif['type'] === 'follow')
                     background: rgba(67,233,123,0.15);
                 @endif
                 ">
                @if($notif['type'] === 'like')
                    <i class="bi bi-heart-fill" style="color: var(--kicau-like); font-size: 1.2rem;"></i>
                @elseif($notif['type'] === 'comment')
                    <i class="bi bi-chat-dots-fill" style="color: var(--kicau-primary); font-size: 1.2rem;"></i>
                @elseif($notif['type'] === 'follow')
                    <i class="bi bi-person-plus-fill" style="color: var(--kicau-accent); font-size: 1.2rem;"></i>
                @else
                    <i class="bi bi-bell-fill" style="color: var(--kicau-text-muted); font-size: 1.2rem;"></i>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    @if(!empty($notif['actor']))
                    <a href="{{ route('profile.show', $notif['actor']['username'] ?? '') }}" class="post-username" style="font-size: 0.95rem;">
                        {{ $notif['actor']['name'] ?? 'User' }}
                    </a>
                    <span class="post-handle">{{ '@' . ($notif['actor']['username'] ?? '') }}</span>
                    @endif
                </div>

                <p class="mb-1" style="font-size: 0.9rem; color: var(--kicau-text);">
                    {{ $notif['message'] ?? '' }}
                </p>

                <div class="d-flex align-items-center gap-2">
                    <small style="color: var(--kicau-text-muted);">
                        <i class="bi bi-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                    </small>

                    @if(empty($notif['read_at']))
                    <span class="badge rounded-pill" style="background: var(--kicau-primary); font-size: 0.65rem;">Baru</span>
                    @endif

                    {{-- Link ke konten terkait --}}
                    @if($notif['type'] === 'like' || $notif['type'] === 'comment')
                        @if(!empty($notif['notifiable_id']))
                        <a href="{{ route('posts.show', $notif['notifiable_id']) }}" class="btn-action" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">
                            <i class="bi bi-arrow-right-short"></i> Lihat Kicauan
                        </a>
                        @endif
                    @elseif($notif['type'] === 'follow')
                        @if(!empty($notif['actor']['username']))
                        <a href="{{ route('profile.show', $notif['actor']['username']) }}" class="btn-action" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">
                            <i class="bi bi-arrow-right-short"></i> Lihat Profil
                        </a>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Unread dot --}}
            @if(empty($notif['read_at']))
            <div class="flex-shrink-0 mt-2">
                <span class="d-inline-block rounded-circle" style="width:8px; height:8px; background:var(--kicau-primary);"></span>
            </div>
            @endif
        </div>
        @empty
        <div class="post-card text-center fade-in-up" style="padding: 3rem 1.5rem;">
            <i class="bi bi-bell-slash" style="font-size: 3rem; color: var(--kicau-text-muted);"></i>
            <p class="mt-3 mb-0" style="color: var(--kicau-text-muted); font-size: 1rem;">
                Belum ada notifikasi. Interaksi dengan pengguna lain untuk menerima notifikasi!
            </p>
        </div>
        @endforelse

        {{-- Pagination --}}
        @if(!empty($pagination['next_page_url']) || !empty($pagination['prev_page_url']))
        <div class="d-flex justify-content-center gap-3 mt-3 mb-4 fade-in-up">
            @if(!empty($pagination['prev_page_url']))
            <a href="{{ route('notifications.index', ['page' => ($pagination['current_page'] ?? 1) - 1]) }}"
               class="btn btn-sm btn-outline-kicau">
                <i class="bi bi-chevron-left me-1"></i> Sebelumnya
            </a>
            @endif
            @if(!empty($pagination['next_page_url']))
            <a href="{{ route('notifications.index', ['page' => ($pagination['current_page'] ?? 1) + 1]) }}"
               class="btn btn-sm btn-outline-kicau">
                Selanjutnya <i class="bi bi-chevron-right ms-1"></i>
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
