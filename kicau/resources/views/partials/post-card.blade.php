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
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-action" onclick="toggleEditPost({{ $postId }})" title="Edit kicauan">
                        <i class="bi bi-pencil-square" style="font-size:0.95rem;"></i>
                    </button>
                    <form id="delete-post-{{ $postId }}" action="{{ route('posts.destroy', $postId) }}" method="POST" class="mb-0">
                        @csrf @method('DELETE')
                        <button type="button" class="btn-action" onclick="confirmDelete('delete-post-{{ $postId }}', 'Kicauan ini akan dihapus permanen.')" title="Hapus kicauan">
                            <i class="bi bi-trash3" style="font-size:0.95rem;"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Body --}}
            @if(!empty($post['body']))
                @php
                    $safeBody = htmlspecialchars($post['body'] ?? '');
                    $formattedBody = preg_replace('/#(\w+)/', '<a href="' . route('search.index') . '?q=%23$1" class="text-decoration-none" style="color:var(--kicau-primary); font-weight:500;">#$1</a>', $safeBody);
                @endphp
                <div id="post-body-{{ $postId }}">
                    <p class="post-body">{!! nl2br($formattedBody) !!}</p>
                </div>
                {{-- Edit Post Form --}}
                <div id="edit-post-form-{{ $postId }}" style="display:none;" class="mt-2 text-end">
                    <form action="{{ route('posts.update', $postId) }}" method="POST" class="edit-post-handler">
                        @csrf
                        @method('PUT')
                        <textarea name="body" class="form-control compose-input mb-2" rows="2" required maxlength="250">{{ $post['body'] }}</textarea>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditPost({{ $postId }})" style="font-size:0.75rem;">Batal</button>
                        <button type="submit" class="btn btn-primary-kicau btn-sm" style="font-size:0.75rem;">Simpan</button>
                    </form>
                </div>
            @endif

            {{-- Media --}}
            @if($mediaUrl)
                @if($mediaType === 'image')
                    <img src="{{ $mediaUrl }}" class="post-media" alt="post media" loading="lazy"
                         style="cursor:pointer;" onclick="openLightbox(this.src)">
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
