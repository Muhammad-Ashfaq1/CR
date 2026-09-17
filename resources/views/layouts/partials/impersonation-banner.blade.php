@if(session()->has('impersonator_id'))
    @php
        $impersonator = \App\Models\User::find(session('impersonator_id'));
        $currentUser = auth()->user();
    @endphp
    <div class="awt-impersonation-banner py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%); color: #ffffff; z-index: 1085; position: sticky; top: 0;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold text-uppercase px-2 py-1 d-flex align-items-center">
                <i class="icon-base ti tabler-spy me-1"></i> Impersonating
            </span>
            <span class="fw-medium small text-white">
                You are currently viewing the system as <strong>{{ $currentUser?->name }}</strong> 
                <span class="badge bg-white bg-opacity-25 text-white ms-1">{{ $currentUser?->role?->label() ?? 'User' }}</span>
                @if($impersonator)
                    <span class="opacity-75 d-none d-md-inline ms-2">&bull; Original Admin: {{ $impersonator->name }}</span>
                @endif
            </span>
        </div>
        <div>
            <form action="{{ route('impersonate.leave') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-dark fw-bold rounded-pill px-3 shadow-sm d-flex align-items-center">
                    <i class="icon-base ti tabler-arrow-back-up me-1"></i> Exit Impersonation
                </button>
            </form>
        </div>
    </div>
@endif
