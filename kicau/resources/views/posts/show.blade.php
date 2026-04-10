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
            <div class="comment-item">
                <div class="d-flex gap-2 align-items-start">
                    <a href="{{ route('profile.show', $commentUser['username'] ?? '') }}">
                        <img src="{{ $commentUser['avatar_url'] ?? '' }}" class="comment-avatar flex-shrink-0" alt="{{ $commentUser['name'] ?? '' }}">
                    </a>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('profile.show', $commentUser['username'] ?? '') }}" class="post-username" style="font-size:0.9rem;">
                                    {{ $commentUser['name'] ?? '' }}
                                </a>
                                <span class="post-handle ms-1" style="font-size:0.8rem;">{{ '@'.($commentUser['username'] ?? '') }}</span>
                                <span class="post-handle ms-1" style="font-size:0.8rem;">· {{ $comment['created_at'] ? \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() : '' }}</span>
                            </div>

                            @if($currentUserId && ($commentUser['id'] ?? null) === $currentUserId)
                            <form id="delete-comment-{{ $commentId }}" action="{{ route('comments.destroy', $commentId) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-action" onclick="confirmDelete('delete-comment-{{ $commentId }}', 'Komentar ini akan dihapus.')" title="Hapus">
                                    <i class="bi bi-trash3" style="font-size:0.85rem;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                        <p class="mb-0 mt-1" style="font-size:0.9rem;color:var(--kicau-text);">{{ $comment['body'] ?? '' }}</p>
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
document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btn-submit-comment');
            const loading = document.getElementById('comment-loading');
            const input = document.getElementById('comment-input');
            const body = input.value.trim();
            const url = this.action;
            const csrf = this.querySelector('input[name="_token"]').value;

            if (!body) return;

            btnSubmit.disabled = true;
            loading.style.display = 'inline-block';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ body: body })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                if (res.status === 201 || res.status === 200) {
                    input.value = ''; // clear
                    const c = res.body.comment;
                    
                    // Render new comment HTML
                    const html = `
                    <div class="comment-item fade-in-up">
                        <div class="d-flex gap-2 align-items-start">
                            <a href="/@${c.user.username}">
                                <img src="${c.user.avatar_url || ''}" class="comment-avatar flex-shrink-0" alt="${c.user.name}">
                            </a>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="/@${c.user.username}" class="post-username" style="font-size:0.9rem;">
                                            ${c.user.name}
                                        </a>
                                        <span class="post-handle ms-1" style="font-size:0.8rem;">@${c.user.username}</span>
                                        <span class="post-handle ms-1" style="font-size:0.8rem;">· Baru saja</span>
                                    </div>
                                </div>
                                <p class="mb-0 mt-1" style="font-size:0.9rem;color:var(--kicau-text);">${c.body}</p>
                            </div>
                        </div>
                    </div>`;
                    
                    const list = document.getElementById('comments-list');
                    const emptyState = list.querySelector('.text-center.py-4');
                    // Remove empty state if present
                    if (emptyState) emptyState.remove();
                    
                    // Prepend new comment
                    list.insertAdjacentHTML('afterbegin', html);
                    
                    // Update comment count
                    const countHeader = document.getElementById('comments-count');
                    const textContent = countHeader.innerText || countHeader.textContent;
                    let num = parseInt(textContent.replace(/[^0-9]/g, '')) || 0;
                    countHeader.innerHTML = `<i class="bi bi-chat-bubble me-2"></i>${num + 1} Komentar`;

                    // Remove sweet alert trigger if exists
                    const existingScript = document.getElementById('sweetalert-comments');
                    if (existingScript) existingScript.remove();
                } else {
                    // Show validation error (inline or alert)
                    const errText = res.body.error || 'Gagal mengirim komentar.';
                    alert(errText); // Fallback to simple alert if error since UI doesn't have inline error for comment
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan.');
            })
            .finally(() => {
                btnSubmit.disabled = false;
                loading.style.display = 'none';
            });
        });
    }
});
</script>
@endpush
@endsection
