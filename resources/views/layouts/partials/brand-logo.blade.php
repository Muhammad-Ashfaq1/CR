{{-- Construction Brand Mark --}}
<svg width="{{ $size ?? 32 }}" height="{{ $size ?? 32 }}" viewBox="0 0 72 72" role="img" aria-label="{{ config('app.name') }} logo" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="crBrandGrad" x1="10" y1="10" x2="62" y2="62" gradientUnits="userSpaceOnUse">
      <stop stop-color="#f59e0b"/>
      <stop offset="1" stop-color="#d97706"/>
    </linearGradient>
    <linearGradient id="crDarkGrad" x1="10" y1="62" x2="62" y2="10" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1e293b"/>
      <stop offset="1" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
  <rect x="8" y="8" width="56" height="56" rx="14" fill="url(#crDarkGrad)"/>
  {{-- Skyscraper silhouette --}}
  <path d="M22 52V28L32 20V52H22Z" fill="url(#crBrandGrad)" opacity="0.9"/>
  <path d="M32 52V16L46 24V52H32Z" fill="url(#crBrandGrad)"/>
  <path d="M46 52V34L54 40V52H46Z" fill="url(#crBrandGrad)" opacity="0.75"/>
  {{-- Windows --}}
  <rect x="36" y="24" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="41" y="24" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="36" y="30" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="41" y="30" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="36" y="36" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="41" y="36" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="36" y="42" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <rect x="41" y="42" width="3" height="3" rx="0.5" fill="#1e293b"/>
  <line x1="16" y1="52" x2="58" y2="52" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round"/>
</svg>
