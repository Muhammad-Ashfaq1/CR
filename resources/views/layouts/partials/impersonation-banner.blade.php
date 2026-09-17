@if(session()->has('impersonator_id'))
    @php
        $impersonator = \App\Models\User::find(session('impersonator_id'));
        $currentUser = auth()->user();
    @endphp
    <div class="awt-impersonation-banner mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.95) 0%, rgba(79, 70, 229, 0.95) 50%, rgba(67, 56, 202, 0.95) 100%); color: #ffffff; border-radius: 0.875rem; border: 1px solid rgba(255, 255, 255, 0.25); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="badge bg-warning text-dark fw-bold text-uppercase px-2 py-1 d-inline-flex align-items-center" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                <i class="icon-base ti tabler-spy me-1"></i> Impersonating
            </span>
            <span class="fw-medium small text-white d-flex align-items-center gap-2 flex-wrap">
                <span>You are currently viewing the system as <strong>{{ $currentUser?->name }}</strong></span>
                <span class="badge bg-white bg-opacity-25 text-white">{{ $currentUser?->role?->label() ?? 'User' }}</span>
                @if($impersonator)
                    <span class="opacity-75 d-none d-md-inline">&bull; Original Admin: {{ $impersonator->name }}</span>
                @endif
            </span>
        </div>
        <div>
            <form action="{{ route('impersonate.leave') }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-dark fw-bold rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                    <i class="icon-base ti tabler-arrow-back-up me-1"></i> Exit Impersonation
                </button>
            </form>
        </div>
    </div>
@endif
