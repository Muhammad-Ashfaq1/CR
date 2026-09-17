{{--
    Shared theme picker.

    Required vars:
      $endpoint — PUT URL that accepts { theme_variant, theme_mode } JSON.

    Optional vars:
      $current — array{variant: string, mode: string} (defaults to AppTheme::forUser).
      $scope   — 'personal' (default) or 'organization'. On the organization
                 picker the saved theme is NOT necessarily what the person
                 saving it sees, so that picker explains itself; see below.
--}}
@php
    $current = $current ?? \App\Support\AppTheme::forUser(auth()->user());
    $selectedVariant = $current['variant'];
    $selectedMode = $current['mode'];

    $scope = $scope ?? 'personal';

    // On the ORGANIZATION picker, does the admin looking at it have a personal
    // theme that outranks whatever they save here? If so the save is real and
    // stored, they just will not see it — which is precisely what "it says
    // Saved then resets on reload" turned out to be.
    $personalOverride = $scope === 'organization'
        && \App\Support\AppTheme::forUser(auth()->user())['source'] === 'user';

    $lightThemes = [
        ['id' => 'sky',      'label' => 'Sky blue',        'titleBg' => '#d3f1f5', 'accent' => '#25b9d6'],
        ['id' => 'lake',     'label' => 'Lake blue',       'titleBg' => '#e7e8ff', 'accent' => '#696cff'],
        ['id' => 'eggplant', 'label' => 'Eggplant purple', 'titleBg' => '#5a5d6c', 'accent' => '#2d2f3d'],
    ];
    $darkThemes = [
        ['id' => 'dark',          'label' => 'Dark theme',          'titleBg' => '#0b1220', 'bodyBg' => '#0f172a', 'accent' => '#60a5fa'],
        ['id' => 'high-contrast', 'label' => 'High contrast theme', 'titleBg' => '#000000', 'bodyBg' => '#050505', 'accent' => '#3b82f6'],
    ];
@endphp

@once
    @push('styles')
    <style>
        .awt-theme-card {
            cursor: pointer;
            transition: border-color 120ms ease, box-shadow 120ms ease;
        }
        .awt-theme-card .awt-theme-preview {
            border: 2px solid var(--bs-border-color, #d6d9e0);
            border-radius: 0.5rem;
            overflow: hidden;
            transition: border-color 120ms ease, box-shadow 120ms ease;
        }
        .awt-theme-card.is-selected .awt-theme-preview {
            border-color: var(--bs-primary, #696cff);
            box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb, 105, 108, 255), 0.18);
        }
        .awt-theme-card .form-check-input { cursor: pointer; }

        .awt-theme-card.is-disabled { cursor: not-allowed; opacity: 0.55; }
        .awt-theme-card.is-disabled .awt-theme-preview { filter: grayscale(0.3); }
        .awt-theme-card.is-disabled .form-check-input { pointer-events: none; }
        .awt-theme-card .coming-soon {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: var(--bs-secondary-bg, #eef0f6);
            color: var(--bs-secondary-color, #6c757d);
            border-radius: 999px;
            padding: 2px 8px;
            margin-left: 0.5rem;
        }
    </style>
    @endpush
@endonce

<div data-theme-picker
     data-theme-endpoint="{{ $endpoint }}"
     data-theme-scope="{{ $scope }}"
     @if($scope === 'organization')
         data-theme-personal-endpoint="{{ route('agent.settings.themes.update') }}"
     @endif>

    {{-- Shown when this admin's own theme masks the organization theme. The
         alert is server-rendered when that is already true on load, and the
         script reveals it if a save reports the same thing. --}}
    <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 {{ $personalOverride ? '' : 'd-none' }}"
         data-theme-override-notice>
        <i class="icon-base ti tabler-user-cog"></i>
        <span class="flex-grow-1">
            You have your own theme set, so it is what <strong>you</strong> see —
            the organization theme below still applies to everyone who has not
            chosen one.
        </span>
        <button type="button" class="btn btn-sm btn-outline-warning" data-theme-use-org>
            Use my organization's theme
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap py-1 mb-3">
        <div>
            <div class="fw-semibold">Set color theme to</div>
            <small class="text-muted">Select a light or dark theme or have your computer system setting determine what you see.</small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            {{-- Saving used to be silent: a failed request logged to the console
                 and nothing else, so the theme applied on screen, vanished on
                 the next reload, and looked like it had never been saved. --}}
            <span class="small" data-theme-status role="status" aria-live="polite"></span>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-theme-retry>Retry</button>
            <select class="form-select w-auto" name="theme_mode" data-theme-mode>
                <option value="light"  @selected($selectedMode === 'light')>Light</option>
                <option value="dark"   @selected($selectedMode === 'dark')>Dark</option>
                <option value="system" @selected($selectedMode === 'system')>Use system setting</option>
            </select>
        </div>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold mb-3">Light themes</h6>
        <div class="row g-3" data-theme-group>
            @foreach($lightThemes as $t)
                @php $isSel = $selectedVariant === $t['id']; @endphp
                <div class="col-12 col-md-4">
                    <label class="awt-theme-card d-block {{ $isSel ? 'is-selected' : '' }}"
                           data-theme-card="{{ $t['id'] }}">
                        <div class="awt-theme-preview">
                            <div class="d-flex align-items-center gap-1 px-2 py-1"
                                 style="background: {{ $t['titleBg'] }};">
                                <span class="d-inline-block bg-warning rounded-circle" style="width:6px;height:6px;"></span>
                                <span class="d-inline-block bg-white rounded-circle opacity-50" style="width:6px;height:6px;"></span>
                                <span class="d-inline-block bg-white rounded-circle opacity-50" style="width:6px;height:6px;"></span>
                            </div>
                            <div class="d-flex bg-white" style="height:140px;">
                                <div class="bg-light p-1 d-flex flex-column gap-1 border-end">
                                    <span class="d-inline-block bg-secondary rounded" style="width:10px;height:10px;"></span>
                                    <span class="d-inline-block bg-secondary rounded" style="width:10px;height:10px;"></span>
                                </div>
                                <div class="d-flex flex-column gap-1 p-2 border-end" style="width:80px;">
                                    <span class="d-block rounded" style="height:8px;width:60%;background: {{ $t['accent'] }};"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:8px;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:8px;width:70%;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:8px;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:8px;width:70%;"></span>
                                </div>
                                <div class="flex-grow-1 d-flex flex-column gap-1 p-2">
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:5px;width:55%;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:5px;width:90%;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:5px;"></span>
                                    <span class="d-block bg-secondary rounded opacity-25" style="height:5px;width:55%;"></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <input type="radio"
                                   name="theme_variant"
                                   value="{{ $t['id'] }}"
                                   class="form-check-input m-0"
                                   data-theme-radio
                                   {{ $isSel ? 'checked' : '' }}>
                            <span class="fw-medium">{{ $t['label'] }}</span>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <div class="row g-3" data-theme-group>
            @foreach($darkThemes as $t)
                @php
                    $isSel = $selectedVariant === $t['id'];
                    $isDisabled = ! empty($t['disabled']);
                @endphp
                <div class="col-12 col-md-4">
                    <div class="fw-bold text-muted mb-2 d-flex align-items-center">
                        <span>{{ $t['label'] }}</span>
                        @if($isDisabled)
                            <span class="coming-soon">Coming soon</span>
                        @endif
                    </div>
                    <label class="awt-theme-card d-block {{ $isSel ? 'is-selected' : '' }} {{ $isDisabled ? 'is-disabled' : '' }}"
                           data-theme-card="{{ $t['id'] }}"
                           @if($isDisabled) data-theme-disabled="1" aria-disabled="true" @endif>
                        <div class="awt-theme-preview">
                            <div class="d-flex align-items-center gap-1 px-2 py-1"
                                 style="background: {{ $t['titleBg'] }};">
                                <span class="d-inline-block bg-warning rounded-circle" style="width:6px;height:6px;"></span>
                                <span class="d-inline-block bg-white rounded-circle opacity-50" style="width:6px;height:6px;"></span>
                                <span class="d-inline-block bg-white rounded-circle opacity-50" style="width:6px;height:6px;"></span>
                            </div>
                            <div class="d-flex" style="height:140px;background: {{ $t['bodyBg'] }};">
                                <div class="p-1 d-flex flex-column gap-1 border-end">
                                    <span class="d-inline-block bg-light rounded" style="width:10px;height:10px;"></span>
                                    <span class="d-inline-block bg-light rounded" style="width:10px;height:10px;"></span>
                                </div>
                                <div class="d-flex flex-column gap-1 p-2 border-end" style="width:80px;">
                                    <span class="d-block rounded" style="height:8px;width:60%;background: {{ $t['accent'] }};"></span>
                                    <span class="d-block bg-light rounded opacity-50" style="height:8px;"></span>
                                    <span class="d-block bg-light rounded opacity-50" style="height:8px;width:70%;"></span>
                                </div>
                                <div class="flex-grow-1 d-flex flex-column gap-1 p-2">
                                    <span class="d-block bg-light rounded opacity-50" style="height:5px;width:55%;"></span>
                                    <span class="d-block bg-light rounded opacity-50" style="height:5px;width:90%;"></span>
                                    <span class="d-block bg-light rounded opacity-50" style="height:5px;"></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <input type="radio"
                                   name="theme_variant"
                                   value="{{ $t['id'] }}"
                                   class="form-check-input m-0"
                                   data-theme-radio
                                   {{ $isSel ? 'checked' : '' }}
                                   @if($isDisabled) disabled @endif>
                            <span class="fw-medium">{{ $t['label'] }}</span>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
    (function () {
        const DARK_VARIANTS = ['dark', 'high-contrast'];
        const DEFAULT_LIGHT_VARIANT = 'lake';
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

        document.querySelectorAll('[data-theme-picker]').forEach(initPicker);

        function initPicker(root) {
            const endpoint = root.dataset.themeEndpoint;
            if (!endpoint) return;

            const cards = root.querySelectorAll('[data-theme-card]');
            const radios = root.querySelectorAll('[data-theme-radio]');
            const modeSelect = root.querySelector('[data-theme-mode]');
            const status = root.querySelector('[data-theme-status]');
            const retry = root.querySelector('[data-theme-retry]');
            const overrideNotice = root.querySelector('[data-theme-override-notice]');

            // Mirror the saved theme into localStorage so the pre-paint script
            // in the layout head can apply it before the stylesheets land.
            // A CACHE ONLY: every server render overwrites it, and the inline
            // reader defers to the server-rendered attributes whenever they
            // exist. It must never be able to outrank the database, or a stale
            // tab would resurrect the very bug this change fixes.
            function cacheTheme(data) {
                if (!data || !data.theme_variant) return;
                try {
                    localStorage.setItem('awt_theme', JSON.stringify({
                        variant: data.theme_variant,
                        mode: data.theme_mode || 'light',
                    }));
                } catch (_) {}
            }

            // What the SERVER currently holds. The picker is only ever allowed
            // to show a theme this agrees with: if a save fails, the screen goes
            // back to this rather than sitting on a choice nothing stored — the
            // state that made a lost save look like "it reverted on reload".
            let saved = {
                variant: root.querySelector('[data-theme-radio]:checked')?.value || DEFAULT_LIGHT_VARIANT,
                mode: modeSelect ? modeSelect.value : 'light',
            };
            let lastAttempt = null;

            function setStatus(text, tone) {
                if (!status) return;
                status.textContent = text || '';
                status.className = 'small ' + (tone || 'text-muted');
            }

            function showRetry(show) {
                retry?.classList.toggle('d-none', !show);
            }

            function revertToSaved() {
                markSelected(saved.variant);
                if (modeSelect) modeSelect.value = saved.mode;
                applyClient(saved.variant, saved.mode);
            }

            function applyClient(variant, mode) {
                if (window.AwtTheme && typeof window.AwtTheme.apply === 'function') {
                    window.AwtTheme.apply(variant, mode);
                }
            }

            function markSelected(variant) {
                cards.forEach(card => {
                    card.classList.toggle('is-selected', card.dataset.themeCard === variant);
                });
                radios.forEach(r => { r.checked = r.value === variant; });
            }

            async function persist(payload) {
                lastAttempt = payload;
                setStatus('Saving…');
                showRetry(false);

                let res;
                try {
                    res = await fetch(endpoint, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });
                } catch (err) {
                    // Offline, blocked, DNS — the server never heard about it.
                    console.error('[themes] save failed', err);
                    revertToSaved();
                    setStatus('Could not save — check your connection.', 'text-danger');
                    showRetry(true);
                    return;
                }

                if (res.status === 419 || res.status === 401) {
                    // The single most likely silent failure: a session that
                    // expired in a tab left open. Say so, because "nothing
                    // happened" is indistinguishable from a bug.
                    revertToSaved();
                    setStatus('Your session expired — sign in again to save.', 'text-danger');
                    showRetry(true);
                    return;
                }

                if (!res.ok) {
                    console.error('[themes] save failed', res.status);
                    revertToSaved();
                    setStatus('Could not save (error ' + res.status + ').', 'text-danger');
                    showRetry(true);
                    return;
                }

                // A 200 is not proof. fetch FOLLOWS redirects, so a session that
                // has expired lands on the login page — status 200, HTML body —
                // and taking that as success is precisely how a save could be
                // reported as done while nothing was stored, then "vanish" on
                // the next reload. Only JSON from our own endpoint counts.
                const contentType = res.headers.get('content-type') || '';
                let data = null;
                if (contentType.includes('application/json')) {
                    try {
                        data = await res.json();
                    } catch (_) {
                        data = null;
                    }
                }

                if (!data || !data.theme_variant) {
                    console.error('[themes] save did not return a saved theme', res.status, res.url);
                    revertToSaved();
                    setStatus(res.redirected
                        ? 'Your session expired — sign in again to save.'
                        : 'Could not save — the server did not confirm it.', 'text-danger');
                    showRetry(true);
                    return;
                }

                // The SERVER's answer is what is stored — it may have adjusted
                // the pair (dark mode forces a dark variant), so the screen is
                // re-applied from the response rather than from what was sent.
                saved = {
                    variant: data.theme_variant,
                    mode: data.theme_mode || payload.theme_mode || saved.mode,
                };
                markSelected(saved.variant);
                if (modeSelect) modeSelect.value = saved.mode;
                applyClient(saved.variant, saved.mode);

                // The organization endpoint reports when the person saving has
                // a personal theme that outranks it. Say so on the spot: the
                // save DID work, and without this the next reload coming back
                // on their own theme reads as "it did not save".
                if (data.personal_override) {
                    overrideNotice?.classList.remove('d-none');
                    setStatus(data.message || 'Saved for your organization', 'text-warning');
                } else {
                    overrideNotice?.classList.add('d-none');
                    setStatus('Saved', 'text-success');
                }

                cacheTheme(data);
                window.dispatchEvent(new CustomEvent('awt:theme:changed', { detail: data }));
                try { localStorage.setItem('awt:theme:changed', JSON.stringify({ ...data, ts: Date.now() })); } catch (_) {}
            }

            retry?.addEventListener('click', () => {
                if (lastAttempt) persist(lastAttempt);
            });

            cards.forEach(card => {
                card.addEventListener('click', (e) => {
                    if (card.dataset.themeDisabled === '1') {
                        e.preventDefault();
                        return;
                    }
                    const variant = card.dataset.themeCard;
                    const mode = DARK_VARIANTS.includes(variant) ? 'dark' : 'light';
                    if (modeSelect) modeSelect.value = mode;

                    markSelected(variant);
                    applyClient(variant, mode);
                    persist({ theme_variant: variant, theme_mode: mode });
                });
            });

            modeSelect?.addEventListener('change', () => {
                const mode = modeSelect.value;
                const checked = root.querySelector('[data-theme-radio]:checked');
                let variant = checked?.value || DEFAULT_LIGHT_VARIANT;

                // Mirrors AppTheme::pairWithMode() so the screen and the server
                // never disagree about what was chosen. Note `mode === 'light'`,
                // not `mode !== 'dark'`: "Use system setting" must NOT drop a dark
                // variant, because the browser resolves light/dark at runtime and
                // the variant is the palette it resolves into. Treating system as
                // light is what silently reset a dark theme to the default here.
                if (mode === 'dark' && !DARK_VARIANTS.includes(variant)) {
                    variant = 'dark';
                } else if (mode === 'light' && DARK_VARIANTS.includes(variant)) {
                    variant = DEFAULT_LIGHT_VARIANT;
                }

                // Paint the resolved pair locally so the screen reacts at once…
                markSelected(variant);
                applyClient(variant, mode);

                // …but send ONLY the mode. The radio above is pre-checked from
                // the RESOLVED theme, which may be inherited from the
                // organization rather than chosen. Posting it would store an
                // inheritance as a personal choice and pin this person to it
                // forever — the defect that left 18 of 26 accounts stuck on the
                // platform default. The server decides what to store; see
                // AppTheme::personalVariantToStore().
                persist({ theme_mode: mode });
            });

            // "Use my organization's theme" — clears the personal override so
            // the organization theme applies to this admin again. Posts to the
            // PERSONAL endpoint even though this picker saves the organization
            // one, because the override being cleared is a personal record.
            root.querySelector('[data-theme-use-org]')?.addEventListener('click', async () => {
                const personalEndpoint = root.dataset.themePersonalEndpoint;
                if (!personalEndpoint) return;

                setStatus('Clearing your personal theme…');

                try {
                    const res = await fetch(personalEndpoint, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ theme_variant: 'inherit' }),
                    });

                    const data = res.headers.get('content-type')?.includes('application/json')
                        ? await res.json()
                        : null;

                    if (!data || !data.theme_variant) {
                        setStatus('Could not clear your personal theme.', 'text-danger');
                        return;
                    }

                    saved = { variant: data.theme_variant, mode: data.theme_mode || saved.mode };
                    markSelected(saved.variant);
                    if (modeSelect) modeSelect.value = saved.mode;
                    applyClient(saved.variant, saved.mode);
                    overrideNotice?.classList.add('d-none');
                    setStatus('Now using your organization theme.', 'text-success');
                    cacheTheme(data);
                } catch (err) {
                    console.error('[themes] could not clear personal override', err);
                    setStatus('Could not clear your personal theme.', 'text-danger');
                }
            });
        }
    })();
    </script>
    @endpush
@endonce
