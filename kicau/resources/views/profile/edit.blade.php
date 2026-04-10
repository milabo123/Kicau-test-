@extends('layouts.app')
@section('content')
@php
    $sessionUser = session('user', []);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <h5 class="fw-bold mb-4" style="color:var(--kicau-text);">
            <i class="bi bi-gear-fill me-2" style="color:var(--kicau-primary);"></i>Edit Profil
        </h5>

        <div class="card-kicau p-4">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                {{-- Avatar Preview --}}
                <div class="text-center mb-4">
                    <img src="{{ $sessionUser['avatar_url'] ?? '' }}" id="avatar-preview" class="rounded-circle mb-3"
                        width="100" height="100" style="object-fit:cover;border:3px solid var(--kicau-primary);" alt="Avatar">
                    <div>
                        <label for="avatar-upload" class="btn btn-outline-kicau btn-sm" style="cursor:pointer;">
                            <i class="bi bi-camera-fill me-1"></i> Ganti Foto
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display:none;"
                            onchange="previewAvatar(this)">
                    </div>
                    @error('avatar')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-kicau">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control form-control-kicau @error('name') is-invalid @enderror"
                        value="{{ old('name', $sessionUser['name'] ?? '') }}" placeholder="Masukkan nama lengkap" required>
                    @error('name')
                        <div class="invalid-feedback" style="color:#FF6584;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-kicau">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);border-right:none;color:var(--kicau-text-muted);">@</span>
                        <input type="text" name="username" class="form-control form-control-kicau @error('username') is-invalid @enderror"
                            value="{{ old('username', $sessionUser['username'] ?? '') }}" placeholder="username_kamu" required style="border-left:none !important;">
                    </div>
                    @error('username')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label-kicau">Bio <span style="color:var(--kicau-text-muted);font-size:0.8rem;">(maks. 160 karakter)</span></label>
                    <textarea name="bio" class="form-control form-control-kicau @error('bio') is-invalid @enderror"
                        rows="3" placeholder="Ceritakan tentang dirimu..." maxlength="160">{{ old('bio', $sessionUser['bio'] ?? '') }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback" style="color:#FF6584;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('profile.show', $sessionUser['username'] ?? '') }}" class="btn btn-outline-kicau">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary-kicau">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
