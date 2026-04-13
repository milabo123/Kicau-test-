@extends('layouts.app')
@section('content')
@php
    $sessionUser   = session('user', []);
    $currentUserId = $sessionUser['id'] ?? null;
    $postUser      = $post['user'] ?? [];
    $postId        = $post['id'] ?? 0;
    $isLiked       = $post['is_liked'] ?? false;
    $createdAt     = $post['created_at'] ?? '';
    $comments      = $post['comments'] ?? [];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <a href="{{ url()->previous() }}" class="btn-action d-inline-flex mb-3" style="text-decoration:none;">
            <i class="bi bi-arrow-left"></i> <span>Kembali</span>
        </a>

        {{-- Main Post --}}
        @include('partials.post-card', ['post' => $post])

        {{-- Comment Form --}}
        <div class="compose-box mt-3">
            <div class="d-flex gap-3">
                <img src="{{ $sessionUser['avatar_url'] ?? '' }}" class="comment-avatar mt-1" alt="avatar">
                <form action="{{ route('comments.store', $postId) }}" method="POST" class="flex-grow-1" id="comment-form">
                    @csrf
                    <textarea name="body" id="comment-input" class="form-control compose-input mb-2" rows="2"
                        placeholder="Tulis komentar..." maxlength="300" required></textarea>
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <span id="comment-loading" class="spinner-border spinner-border-sm text-primary" style="display:none;" role="status"></span>
                        <button type="submit" class="btn btn-primary-kicau btn-sm" id="btn-submit-comment">
                            <i class="bi bi-chat-fill me-1"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Comments --}}
        <div class="mt-3">
            <h6 class="fw-bold mb-3" style="color:var(--kicau-text-muted);" id="comments-count">
                <i class="bi bi-chat-bubble me-2"></i>{{ count($comments) }} Komentar
            </h6>
            
            <div id="comments-list">

            @forelse($comments as $comment)
            @php
                $commentUser = $comment['user'] ?? [];
                $commentId   = $comment['id'] ?? 0;
            @endphp
            <div class="comment-item" id="comment-container-{{ $commentId }}">
                <div class="d-flex gap-2 align-items-start">
                    <a href="{{ route('profile.show', $commentUser['username'] ?? '') }}">
                        <img src="{{ $commentUser['avatar_url'] ?? '' }}" class="comment-avatar flex-shrink-0" alt="{{ $commentUser['name'] ?? '' }}">
                    </a>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('profile.show', $commentUser['username'] ?? '') }}" class="post-username" style="font-size:0.9rem;">
                                    {{ $commentUser['name'] ?? '' }}
                                </a>
                                <span class="post-handle ms-1" style="font-size:0.8rem;">{{ '@'.($commentUser['username'] ?? '') }}</span>
                                <span class="post-handle ms-1" style="font-size:0.8rem;">· {{ $comment['created_at'] ? \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() : '' }}</span>
                            </div>

                            @if($currentUserId && ($commentUser['id'] ?? null) === $currentUserId)
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn-action" onclick="toggleEditComment({{ $commentId }})" title="Edit">
                                    <i class="bi bi-pencil-square" style="font-size:0.85rem;"></i>
                                </button>
                                <form id="delete-comment-{{ $commentId }}" action="{{ route('comments.destroy', $commentId) }}" method="POST" class="mb-0">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn-action" onclick="confirmDelete('delete-comment-{{ $commentId }}', 'Komentar ini akan dihapus.')" title="Hapus">
                                        <i class="bi bi-trash3" style="font-size:0.85rem;"></i>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        
                        <div id="comment-body-{{ $commentId }}">
                            @php
                                $safeBody = htmlspecialchars($comment['body'] ?? '');
                                $formattedBody = preg_replace('/#(\w+)/', '<a href="' . route('search.index') . '?q=%23$1" class="text-decoration-none" style="color:var(--kicau-primary); font-weight:500;">#$1</a>', $safeBody);
                            @endphp
                            <p class="mb-1 mt-1" style="font-size:0.9rem;color:var(--kicau-text);">{!! nl2br($formattedBody) !!}</p>
                        </div>

                        {{-- Edit Comment Form --}}
                        <div id="edit-comment-form-{{ $commentId }}" style="display:none;" class="mt-2 text-end">
                            <form action="{{ route('comments.update', $commentId) }}" method="POST" class="edit-comment-handler">
                                @csrf
                                @method('PUT')
                                <textarea name="body" class="form-control compose-input mb-2" rows="1" required maxlength="300" style="font-size:0.85rem;">{{ $comment['body'] ?? '' }}</textarea>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditComment({{ $commentId }})" style="font-size:0.75rem;">Batal</button>
                                <button type="submit" class="btn btn-primary-kicau btn-sm" style="font-size:0.75rem;">Simpan</button>
                            </form>
                        </div>
                        
                        {{-- Comment Actions (Like, Reply) --}}
                        <div class="d-flex gap-3 mt-1 align-items-center">
                            <button class="btn-action d-flex align-items-center gap-1" onclick="toggleCommentLike({{ $commentId }})" style="font-size:0.85rem;">
                                <i class="bi bi-heart{{ ($comment['is_liked'] ?? false) ? '-fill text-danger' : '' }}" id="like-icon-comment-{{ $commentId }}"></i>
                                <span id="like-count-comment-{{ $commentId }}">{{ $comment['likes_count'] ?? 0 }}</span>
                            </button>
                            <button class="btn-action d-flex align-items-center gap-1" onclick="toggleReplyBox({{ $commentId }})" style="font-size:0.85rem;">
                                <i class="bi bi-chat"></i> Balas
                            </button>
                        </div>

                        {{-- Reply Box (Hidden by default) --}}
                        <div id="reply-box-{{ $commentId }}" class="mt-2" style="display:none;">
                            <form action="{{ route('comments.store', $postId) }}" method="POST" class="reply-form">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $commentId }}">
                                <div class="d-flex gap-2">
                                    <img src="{{ $sessionUser['avatar_url'] ?? '' }}" class="rounded-circle mt-1" width="28" height="28" style="object-fit:cover;" alt="avatar">
                                    <div class="flex-grow-1">
                                        <textarea name="body" class="form-control compose-input mb-2" rows="1" style="font-size:0.85rem; min-height:40px;" placeholder="Balas komentar ini..." maxlength="300" required></textarea>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary-kicau btn-sm" style="font-size:0.75rem; padding: 0.2rem 0.6rem;">Kirim Balasan</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Nested Replies --}}
                        @if(!empty($comment['replies']))
                        <div class="mt-2 ms-2 ps-3 border-start">
                            @foreach($comment['replies'] as $reply)
                            @php
                                $replyUser = $reply['user'] ?? [];
                                $replyId   = $reply['id'] ?? 0;
                            @endphp
                            <div class="comment-item pt-2 pb-1" id="comment-container-{{ $replyId }}" style="border-bottom:none;">
                                <div class="d-flex gap-2 align-items-start">
                                    <a href="{{ route('profile.show', $replyUser['username'] ?? '') }}">
                                        <img src="{{ $replyUser['avatar_url'] ?? '' }}" class="rounded-circle flex-shrink-0 mt-1" width="32" height="32" style="object-fit:cover;" alt="{{ $replyUser['name'] ?? '' }}">
                                    </a>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <a href="{{ route('profile.show', $replyUser['username'] ?? '') }}" class="post-username" style="font-size:0.85rem;">
                                                    {{ $replyUser['name'] ?? '' }}
                                                </a>
                                                <span class="post-handle" style="font-size:0.75rem;">{{ '@'.($replyUser['username'] ?? '') }}</span>
                                                <span class="post-handle ms-1" style="font-size:0.75rem;">· {{ $reply['created_at'] ? \Carbon\Carbon::parse($reply['created_at'])->diffForHumans() : '' }}</span>
                                            </div>
                                            @if($currentUserId && ($replyUser['id'] ?? null) === $currentUserId)
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn-action" onclick="toggleEditComment({{ $replyId }})" title="Edit">
                                                    <i class="bi bi-pencil-square" style="font-size:0.8rem;"></i>
                                                </button>
                                                <form id="delete-comment-{{ $replyId }}" action="{{ route('comments.destroy', $replyId) }}" method="POST" class="mb-0">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn-action" onclick="confirmDelete('delete-comment-{{ $replyId }}', 'Balasan ini akan dihapus.')" title="Hapus">
                                                        <i class="bi bi-trash3" style="font-size:0.8rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <div id="comment-body-{{ $replyId }}">
                                            @php
                                                $safeReplyBody = htmlspecialchars($reply['body'] ?? '');
                                                $formattedReplyBody = preg_replace('/#(\w+)/', '<a href="' . route('search.index') . '?q=%23$1" class="text-decoration-none" style="color:var(--kicau-primary); font-weight:500;">#$1</a>', $safeReplyBody);
                                            @endphp
                                            <p class="mb-1 mt-1" style="font-size:0.85rem;color:var(--kicau-text);">{!! nl2br($formattedReplyBody) !!}</p>
                                        </div>

                                        {{-- Edit Reply Form --}}
                                        <div id="edit-comment-form-{{ $replyId }}" style="display:none;" class="mt-2 text-end">
                                            <form action="{{ route('comments.update', $replyId) }}" method="POST" class="edit-comment-handler">
                                                @csrf
                                                @method('PUT')
                                                <textarea name="body" class="form-control compose-input mb-2" rows="1" required maxlength="300" style="font-size:0.85rem;">{{ $reply['body'] ?? '' }}</textarea>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditComment({{ $replyId }})" style="font-size:0.75rem;">Batal</button>
                                                <button type="submit" class="btn btn-primary-kicau btn-sm" style="font-size:0.75rem;">Simpan</button>
                                            </form>
                                        </div>
                                        
                                        <div class="d-flex gap-3 mt-1 align-items-center">
                                            <button class="btn-action d-flex align-items-center gap-1" onclick="toggleCommentLike({{ $replyId }})" style="font-size:0.8rem;">
                                                <i class="bi bi-heart{{ ($reply['is_liked'] ?? false) ? '-fill text-danger' : '' }}" id="like-icon-comment-{{ $replyId }}"></i>
                                                <span id="like-count-comment-{{ $replyId }}">{{ $reply['likes_count'] ?? 0 }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--kicau-text-muted);">
                <i class="bi bi-chat-bubble" style="font-size:2rem;"></i>
                <p class="mt-2">Belum ada komentar. Jadilah yang pertama!</p>
            </div>
            @endforelse
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function toggleCommentLike(commentId) {
    fetch(`/comments/${commentId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) return alert(data.error);
        const icon = document.getElementById(`like-icon-comment-${commentId}`);
        const count = document.getElementById(`like-count-comment-${commentId}`);
        if(icon) {
            icon.className = data.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
        }
        if(count) {
            count.innerText = data.count;
        }
    })
    .catch(console.error);
}

function toggleReplyBox(commentId) {
    const box = document.getElementById(`reply-box-${commentId}`);
    if (box) box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function toggleEditComment(commentId) {
    const bodyBox = document.getElementById(`comment-body-${commentId}`);
    const formBox = document.getElementById(`edit-comment-form-${commentId}`);
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
    // 1. Handle Main Comment Form (Optimistic)
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // ...existing logic for top-level comment (retained for brevity by just submitting directly or simplifying it to a normal submit if preferred)...
            // Since we added replies, a simple page reload after successful post is reliable for complex nesting.
            // Let's just fallback to the previous fetch but reload to see fresh data reliably
            const btnSubmit = document.getElementById('btn-submit-comment');
            btnSubmit.disabled = true;
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.ok ? window.location.reload() : res.json().then(d => alert(d.error || 'Gagal.')))
            .catch(() => alert('Error.'));
        });
    }

    // 2. Handle Reply Forms (Optimistic or just Reload)
    document.querySelectorAll('.reply-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.ok ? window.location.reload() : res.json().then(d => alert(d.error || 'Gagal balas.')))
            .catch(() => alert('Error.'))
            .finally(() => btn.disabled = false);
        });
    });

    // 3. Handle Edit Forms
    document.querySelectorAll('.edit-post-handler, .edit-comment-handler').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            fetch(this.action, {
                method: 'POST', // The form naturally has _method=PUT from laravel inside FormData
                body: new FormData(this)
            })
            .then(res => res.ok ? window.location.reload() : res.json().then(d => alert(d.error || 'Gagal menyimpan.')))
            .catch(() => alert('Error.'))
            .finally(() => btn.disabled = false);
        });
    });
});
</script>
@endpush
@endsection
