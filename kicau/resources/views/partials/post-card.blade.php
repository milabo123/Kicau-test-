@php
    // post adalah array dari API, bukan Eloquent model
    $sessionUser    = session('user', []);
    $currentUserId  = $sessionUser['id'] ?? null;
    $postUser       = $post['user'] ?? [];
    $postId         = $post['id'] ?? 0;
    $isLiked        = $post['is_liked'] ?? false;
    $likesCount     = $post['likes_count'] ?? 0;
    $commentsCount  = $post['comments_count'] ?? 0;
    $createdAt      = $post['created_at'] ?? '';
    $mediaUrl       = $post['media_url'] ?? null;
    $mediaType      = $post['media_type'] ?? null;
    $postUserId     = $postUser['id'] ?? null;
@endphp

<div class="post-card fade-in-up">
    <div class="d-flex gap-3">
        <a href="{{ route('profile.show', $postUser['username'] ?? '') }}">
            <img src="{{ $postUser['avatar_url'] ?? '' }}" class="post-avatar" alt="{{ $postUser['name'] ?? '' }}">
        </a>
        <div class="flex-grow-1 min-width-0">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <a href="{{ route('profile.show', $postUser['username'] ?? '') }}" class="post-username">
                        {{ $postUser['name'] ?? '' }}
                    </a>
                    <span class="post-handle ms-1">{{ '@'.($postUser['username'] ?? '') }}</span>
                    <span class="post-handle ms-2">·</span>
                    <span class="post-handle ms-1" title="{{ $createdAt }}">
                        {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->diffForHumans() : '' }}
                    </span>
                </div>

                @if($postUserId && $currentUserId && $postUserId === $currentUserId)
                <form id="delete-post-{{ $postId }}" action="{{ route('posts.destroy', $postId) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="button" class="btn-action" onclick="confirmDelete('delete-post-{{ $postId }}', 'Kicauan ini akan dihapus permanen.')" title="Hapus kicauan">
                        <i class="bi bi-trash3" style="font-size:0.95rem;"></i>
                    </button>
                </form>
                @endif
            </div>

            {{-- Body --}}
            @if(!empty($post['body']))
                <p class="post-body">{{ $post['body'] }}</p>
            @endif

            {{-- Media --}}
            @if($mediaUrl)
                @if($mediaType === 'image')
                    <img src="{{ $mediaUrl }}" class="post-media" alt="post media" loading="lazy">
                @elseif($mediaType === 'video')
                    <video class="post-media" controls preload="metadata">
                        <source src="{{ $mediaUrl }}" type="video/mp4">
                        Browser kamu tidak mendukung video.
                    </video>
                @endif
            @endif

            {{-- Actions --}}
            <div class="d-flex gap-1 mt-3">
                {{-- Like --}}
                <form action="{{ route('posts.like', $postId) }}" method="POST" class="form-like">
                    @csrf
                    <button type="button" class="btn-action btn-like {{ $isLiked ? 'liked' : '' }}" title="{{ $isLiked ? 'Batal suka' : 'Suka' }}">
                        <i class="bi bi-heart{{ $isLiked ? '-fill' : '' }}"></i>
                        <span class="likes-count">{{ $likesCount }}</span>
                    </button>
                </form>

                {{-- Comment --}}
                <a href="{{ route('posts.show', $postId) }}" class="btn-action" title="Komentar">
                    <i class="bi bi-chat-bubble"></i>
                    <span>{{ $commentsCount }}</span>
                </a>

                {{-- Share (cosmetic) --}}
                <button class="btn-action" title="Bagikan" onclick="Swal.fire({icon:'info',title:'Salin tautan',text:'{{ route('posts.show', $postId) }}',background:'#1A1A2E',color:'#E8E8F0',confirmButtonColor:'#6C63FF'})">
                    <i class="bi bi-share"></i>
                </button>
            </div>
        </div>
    </div>
</div>
