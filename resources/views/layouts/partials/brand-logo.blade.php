{{-- Construction Brand Mark --}}
<svg width="{{ $size ?? 32 }}" height="{{ $size ?? 32 }}" viewBox="0 0 72 72" role="img" aria-label="{{ config('app.name') }} logo" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="crGoldGrad" x1="12" y1="12" x2="60" y2="60" gradientUnits="userSpaceOnUse">
      <stop stop-color="#F59E0B"/>
      <stop offset="0.5" stop-color="#D97706"/>
      <stop offset="1" stop-color="#B45309"/>
    </linearGradient>
    <linearGradient id="crBrightGold" x1="20" y1="10" x2="52" y2="50" gradientUnits="userSpaceOnUse">
      <stop stop-color="#FDE68A"/>
      <stop offset="0.4" stop-color="#F59E0B"/>
      <stop offset="1" stop-color="#D97706"/>
    </linearGradient>
    <linearGradient id="crSlateGrad" x1="8" y1="8" x2="64" y2="64" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E293B"/>
      <stop offset="1" stop-color="#0F172A"/>
    </linearGradient>
    <linearGradient id="crGlow" x1="36" y1="0" x2="36" y2="72" gradientUnits="userSpaceOnUse">
      <stop stop-color="#F59E0B" stop-opacity="0.15"/>
      <stop offset="1" stop-color="#F59E0B" stop-opacity="0"/>
    </linearGradient>
  </defs>

  {{-- Squircle Base --}}
  <rect x="6" y="6" width="60" height="60" rx="16" fill="url(#crSlateGrad)"/>
  <rect x="6" y="6" width="60" height="60" rx="16" stroke="rgba(255,255,255,0.08)" stroke-width="1.5"/>
  <rect x="6" y="6" width="60" height="60" rx="16" fill="url(#crGlow)"/>

  {{-- Tower Crane Arm & Cables --}}
  <path d="M18 20H54M30 14L30 20M30 14L46 20M30 14L20 20M48 20L48 27M47 27H49M48 27L48 29" stroke="#FBBF24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

  {{-- Left Building (Tower 1) --}}
  <path d="M19 54V32L28 26V54H19Z" fill="url(#crBrightGold)"/>
  {{-- Center Skyscraper (Tower 2) --}}
  <path d="M28 54V21L42 16V54H28Z" fill="url(#crGoldGrad)"/>
  {{-- Right Building (Tower 3) --}}
  <path d="M42 54V30L51 36V54H42Z" fill="#B45309"/>

  {{-- Architectural Windows on Center Tower --}}
  <rect x="32" y="24" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="36.5" y="24" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="32" y="30" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="36.5" y="30" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="32" y="36" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="36.5" y="36" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="32" y="42" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>
  <rect x="36.5" y="42" width="2.5" height="2.5" rx="0.5" fill="#0F172A"/>

  {{-- Windows on Tower 1 --}}
  <rect x="22" y="36" width="2" height="2" rx="0.5" fill="#0F172A" opacity="0.8"/>
  <rect x="22" y="42" width="2" height="2" rx="0.5" fill="#0F172A" opacity="0.8"/>

  {{-- Heavy Industrial Foundation Line --}}
  <line x1="14" y1="54" x2="58" y2="54" stroke="#F59E0B" stroke-width="3" stroke-linecap="round"/>
</svg>
