# 51 — UI Kit & Design System

The UI layer of this application, written down as a **kit you can lift into
another Laravel + Vuexy project**. Different domain, different flows, different
data — same shell, same surfaces, same rules.

Read this if you are either (a) adding a screen to AWT Phone, or (b) starting a
new Laravel app on the Vuexy template and want it to look and behave like this
one without rediscovering the decisions.

Related: [47 — Table Pagination](47-table-pagination.md) is the deep reference
for the shared table; this doc covers how it fits with everything else.

---

## 0. The one-paragraph summary

Vuexy gives you a Bootstrap 5 shell (sidebar, navbar, cards, utilities). On top
of it sits a small, deliberately app-agnostic kit — prefixed `awt-` — that
supplies the things Vuexy does not: a **theme system** with user-chosen
palettes, a **glass surface** every card is built from, an **AJAX table**, one
**confirm dialog**, a **self-saving settings form**, and a **mobile
master-detail** toggle, plus a handful of shared Blade components that each own
one decision. The app runs **two shells** on that kit — Vuexy's vertical menu
for the two admin audiences, and a `tal-*` CSS-grid workspace for agents — and
the shell, not the page, mounts anything that has to survive a navigation or
escape a stacking context. Everything else is per-screen CSS, namespaced and
additive, that reads the kit's variables and never fights it.

---

## 1. The three layers

Every stylesheet in this app belongs to exactly one of these. Knowing which one
you are writing in answers most "where does this rule go?" questions.

| Layer | Lives in | Rule |
|---|---|---|
| **1. Vendor** | `public/assets/vendor/css/core.css`, `demo.css` | Vuexy itself. **Never edited.** Upgrades must stay drop-in. |
| **2. Kit** | `public/assets/css/awt-*.css`, `public/assets/js/awt-*.js` | Shared, app-agnostic, unscoped on purpose so any page can use it. Changes here affect every screen — treat as public API. |
| **3. Screen** | `public/assets/css/<area>-<screen>.css` | One file per screen, loaded by that screen only, namespaced to a prefix of its own. Free to change; nothing else reads it. |

The direction of dependency is strictly downward: a screen file may read kit
variables, the kit may read vendor variables, and nothing ever reaches back up.

### What is portable

If you are porting to a new app, these are the files worth taking. They contain
no domain knowledge:

| File | What it gives you |
|---|---|
| `public/assets/css/awt-glass.css`<br>`public/assets/js/awt-glass.js` | Surfaces, the tone palette, stat/intro/pill primitives, and idle-only live blur |
| `public/assets/css/awt-table.css`<br>`public/assets/js/awt-table.js`<br>`resources/views/components/awt-table.blade.php`<br>`app/Support/TableFragment.php` | The whole AJAX table |
| `public/assets/js/awt-confirm.js` | The confirm/prompt dialog |
| `public/assets/js/awt-master-detail.js` | Mobile list↔detail toggle |
| `public/assets/js/awt-mobile-view-gestures.js`<br>`public/assets/css/awt-mobile-view-gestures.css` | Swipe-to-act and press-and-hold on touch lists |
| `public/assets/js/awt-tab-overflow.js` | Priority-plus tab strips |
| `public/assets/js/awt-theme.js`<br>`resources/views/partials/_theme-prepaint.blade.php`<br>`resources/views/partials/_theme-picker.blade.php`<br>`app/Support/AppTheme.php` | The theme engine, pre-paint and the picker |
| `public/assets/css/awt-menu.css` | Sidebar sub-item icons |
| `public/assets/css/awt-responsive.css` | The shared breakpoint ladder and single-pane collapse |
| `resources/views/components/settings/*.blade.php` | Self-saving settings tabs |
| `public/assets/js/toast-helpers.js`<br>`public/assets/js/notifications.js`, `alerts.js` | Toast + alert helpers |
| `public/assets/js/awt-copy-link.js` | Declarative copy-to-clipboard |
| `public/assets/js/awt-doc-preview.js`<br>`resources/views/partials/document-preview-modal.blade.php` | Render a private document in place instead of downloading it |
| `public/assets/js/awt-phone-input.js`<br>`public/assets/css/awt-phone-input.css`<br>`resources/views/components/awt-phone-input.blade.php`<br>`resources/views/components/awt-phone-assets.blade.php` | The shared phone field (needs `intl-tel-input`) |

Take with edits: `awt-themes.css` (the palettes are ours — keep the structure,
change the hex); `awt-responsive.css` (it names this app's `rc-*` pane layouts
in places); `premium-paywall.css` + `components/awt-premium-modal.blade.php`
(the mechanism — one catalogue-driven paywall for every add-on — is portable,
the catalogue is not).

Leave behind: anything named after a screen or a shell — `operator-*`,
`tenant-*`, `admin-*`, `rc-*`, `tal-*`, `video-*`.

**Copy the composition, not the screen file.** The screen stylesheets are not
portable, but the shape two of them implement is: a banner, a KPI row and a
listing on glass, with the table transparent and padded off the card's corners.
`tenant-team.css` (a hub with tabs, a bulk bar and a roster) and
`tenant-phone-numbers.css` (a listing with a toolbar and result cards) are the
two worth reading before writing the third — §8 states the rules they follow and
why each one exists.

### Renaming the prefix

`awt-` is this app's namespace. In a new project, pick a two-or-three letter
prefix and rename consistently — CSS classes, JS globals (`AwtConfirm`,
`AwtTheme`), data attributes (`data-awt-table`), and the storage keys. The kit
never resolves its own prefix at runtime, so a find-and-replace is genuinely
sufficient.

---

## 2. The shell

### Where people land

Three audiences, three landing pages, and the third one is the surprise:

| Signing in as | Lands on | Their "dashboard" |
|---|---|---|
| Platform super-admin | `admin.dashboard` | The Admin Command Center — glass KPI row, dual traffic chart, attention matrix |
| Organization admin | `tenant.dashboard` | Eleven server-rendered sections, decision-first: act-on-it before read-it |
| Agent (operator) | `tenant.calls.index` | **None.** An agent lands in the Phone workspace |

That last row is a design position, not an omission. An agent's job is a queue
of things happening now, so the shell carries their status (the navbar presence
dot), their backlog (the rail's unread badges) and their work (the `rc-*`
panes). A summary screen would be a page they had to leave the work to read.
`AuthController` encodes all three destinations.

### Two shells, four layouts

| Layout | For | Shell |
|---|---|---|
| `layouts/admin.blade.php` | Platform super-admin | Vuexy vertical menu |
| `layouts/tenant.blade.php` | Organization admin **and** an agent on an admin-owned page | Vuexy vertical menu, **or** the `tal-*` agent shell |
| `layouts/operator.blade.php` | Agent workspace screens | `tal-*` agent shell |
| `layouts/auth.blade.php` | Login / register / reset | Bare, no chrome, **no theme** |

`layouts/tenant.blade.php` picks its own shell. `$isAgentShell` is true when the
viewer is operator-flagged (`tenant_operator` or `operator`) **and not** admin-flagged, by either the `users.role`
column or a Spatie role. The `operator` role string is supported as a seamless synonym for `tenant_operator` across `EnsureUserHasRole` middleware, `User::isTenantOperatorOnly()`, `homeRoute()`, and user management UI forms. The legacy `tenant` marker is deliberately not a
blocker, because older operator rows still carry it. Everything downstream —
which navbar, which sidebar, whether `tenant-agent-layout.css` loads — hangs off
that one flag, so a page reached by both audiences is written once.

### The agent shell (`tal-*`)

The second shell is a CSS grid, not a Vuexy variant:

```
grid-template-areas: "navbar navbar"
                     "sidebar content";     /* 56px row, 88px column */
height: 100vh;  body { overflow: hidden }
```

The page never scrolls; the content region does. That is what lets a workspace
pin a dialer, a tab strip and a docked call bar in place while a list scrolls
under them — and it is why every `rc-*` screen is written as panes with their
own overflow rather than as a document.

Everything else about it follows from that frame:

- **Route-derived body classes.** Both layouts append `tal-page-messaging`,
  `-calls`, `-contacts`, `-settings`, `-video`, `-fax`, … from
  `request()->routeIs(…)`. A screen that needs the frame tuned (a pane that must
  not scroll, a header that must collapse) hangs its rule off that class rather
  than adding a wrapper.
- **A config-driven rail.** `config/operator_navigation.php` is the single
  source of truth for the tabs; `OperatorNavigation::sidebarGroupsFor()` resolves
  them per user, honouring a saved order in
  `user_messaging_preferences.nav_tab_order` and a labels-on/off preference in
  `nav_show_labels`. Default order is Text → Phone → Auto Dialer → Video →
  Contacts → …; Auto Dialer is omitted from the catalogue when
  `config('services.telnyx.auto_dialer_enabled')` is false. The Customize-tabs
  settings page writes the same rows. A tab whose route no longer exists is
  dropped rather than migrated — which is how Bookings left without touching
  anybody's saved order.
- **Badges that can be cleared.** Phone, Text and Message carry
  `[data-nav-badge]`, painted by `sidebar-badges.js` from read-only count
  endpoints. One rule governs them: a badge may only count rows the UI can
  actually clear, or it never reaches zero and becomes wallpaper.
- **An off-canvas drawer below `lg`.** The 88px rail becomes a slide-in drawer
  (`agent-shell-drawer.js`, `[data-tal-drawer-toggle]`, `.tal-drawer-backdrop`).
  It toggles one class on `.tal-shell`, delegates on `document` behind a global
  guard, traps Tab inside the drawer and restores focus to the burger on close.
- **Presence lives on the avatar.** The navbar dot carries the real
  `agent_statuses` value and a ring for Do Not Disturb, and its menu writes both
  through the endpoints the dashboard already uses. Status and DND are separate
  controls on purpose: status is what the team sees, DND is where an inbound call
  physically goes.

### The `<html>` element carries the state

```blade
@php $awtTheme = \App\Support\AppTheme::forUser(auth()->user()); @endphp
<html class="layout-navbar-fixed layout-menu-fixed layout-compact {{ $awtTheme['classes'] }}"
      data-bs-theme="{{ $awtTheme['bs_theme'] }}"
      data-awt-theme="{{ $awtTheme['variant'] }}"
      data-awt-theme-mode="{{ $awtTheme['mode'] }}"
      data-assets-path="{{ asset('assets') }}/"
      data-template="vertical-menu-template">
```

`data-assets-path` and `data-template` are Vuexy's own — its `helpers.js` reads
them. The rest is ours. Every theme rule in the app is scoped to one of those
attributes or classes, which is why a theme change is a single attribute swap
with no re-render.

### Head order is load-bearing

This sequence is not arbitrary; each position is a decision:

1. `<meta name="csrf-token">` — every `fetch()` in the app reads it.
2. `<meta name="awt-table-scope">` — per-viewer key for remembered table state.
3. Vuexy `core.css`, then `demo.css`.
4. `awt-themes.css` — palettes, after core so it can override.
5. `@stack('styles')` — **the page's own stylesheets**.
6. `awt-responsive.css` — **immediately after the page's own**, so small-screen
   fixes outrank every screen stylesheet without adding `!important`. Every rule
   in it lives inside a media query, so desktop is untouched.
7. **Shell-owned sheets** — `awt-table.css`, `awt-menu.css`, and in the tenant
   and operator shells `awt-mobile-view-gestures.css`, `awt-reminders.css`,
   `awt-notification-center.css`, `awt-e911.css`, `awt-ask-ai.css`,
   `awt-promo-banner.css`, `premium-paywall.css`, `awt-navbar-org.css`. These sit
   after `awt-responsive.css` because they dress markup the *layout* renders,
   which the page never sees.
8. `@include('partials._theme-prepaint')` — inline, ahead of first paint.

Scripts at the foot: vendor (jQuery → Popper → Bootstrap → menu) →
`awt-confirm.js` → `notifications.js` / `toast-helpers.js` / `alerts.js` (they
delegate to the dialog, so it must exist first) → `awt-table.js` → the shell's
own behaviour → mounted modals → `@stack('scripts')`.

> **Rule:** everything the kit owns loads **before** `@stack('scripts')`.
> `awt-table.js` so its DOM-ready hook beats any page's own table init;
> `awt-master-detail.js` and then `awt-mobile-view-gestures.js` in that order, so
> a committed swipe can swallow the click before the detail sheet reacts to it.

The three shells do not carry the same set, and the differences are deliberate.
The admin shell has no Turbo, no master-detail, no touch gestures and no
`toast-helpers.js` — it is a desktop tool for one person, and every one of those
exists to serve a workspace or a phone. Check what a shell actually loads before
relying on it rather than assuming: `awt-copy-link.js` is linked only by the
operator layout, so a `data-awt-copy` button dropped on an admin page today does
nothing at all. (The script itself degrades properly — it feature-tests
`showSuccess` / `showError` and falls back to `AwtConfirm` — but a file that is
never loaded cannot degrade.)

### Shell-mounted, not page-pushed

Two things a page cannot do for itself, and both have bitten this codebase more
than once.

**A partial that renders inside the navbar or at the foot of `<body>` cannot
`@push('styles')`.** By the time it runs, the stack has already been flushed into
`<head>` — the push is accepted and silently dropped. So the stylesheet for the
notification bell, the E911 alert, the Ask AI widget, the add-ons strip and the
paywall are all linked by the *layout*, next to a comment saying why. The same
applies to the sidebar: `awt-menu.css` is a layout link because the layout draws
the sidebar.

**A modal declared beside its trigger inherits the trigger's stacking
context.** A paywall opened from inside `#awtDialerModal`, or from a settings tab
panel, renders *behind* the thing that opened it. Every shared modal is therefore
mounted as a direct child of the shell — the global dialer, the video-call
surface, the Auto Dialer, and each `<x-awt-premium-modal>` — regardless of which
page triggers it. `layouts/operator.blade.php` also exposes `@stack('modals')`
for a page that needs one the shell does not already carry; because it renders
*after* the yielded content, a shell copy always wins over a page's.

### Standalone documents

The guest video room and its pre-join page build their own `<!doctype html>`
rather than extending a layout: a guest gets the meeting and no way into the
application. The cost is that anything the shell normally provides must be
pulled in by hand — `guest-room.blade.php` links `awt-confirm.js` itself,
because `meeting-room.js` calls `window.AwtConfirm` and a missing dialog leaves
the button it guards doing nothing at all. If you add a standalone page, audit
it against the foot-of-body list above rather than assuming.

### Cache-busting

Every first-party asset is stamped with the file's own mtime:

```blade
<link rel="stylesheet"
      href="{{ asset('assets/css/awt-glass.css') }}?v={{ filemtime(public_path('assets/css/awt-glass.css')) }}" />
```

No build step, no manifest, and editing the file is all it takes to invalidate.
The cost is a `stat()` per asset per render — acceptable at this scale, and the
thing it prevents (a user on yesterday's CSS after a hotfix) is much worse.

---

## 3. Theming

Five variants across two axes, resolved server-side by
[`app/Support/AppTheme.php`](../app/Support/AppTheme.php).

| Variant | Surface | Notes |
|---|---|---|
| `sky` | light | cyan accent |
| `lake` | light | indigo accent — **the default** |
| `eggplant` | light | violet accent |
| `dark` | dark | slate |
| `high-contrast` | dark | **an overlay on dark, not a sixth theme** |

Plus a **mode** — `light` / `dark` / `system` — which is a separate axis from
the variant.

### The four rules that make this work

**1. High contrast wears both classes.**

```php
$variant === 'high-contrast' ? 'awt-theme-dark awt-theme-high-contrast' : 'awt-theme-'.$variant
```

Every hand-tuned dark rule in the app therefore applies to it, and its own class
only has to raise contrast on top. Written as a separate theme it would need
~800 rules duplicated — which is exactly why it sat unfinished before.

**2. Resolution is a three-step hierarchy, personal first.**

User's own choice → their organization's theme → platform default. A personal
row records a **choice**, never an inheritance: storing the resolved variant
when the user only touched the light/dark dropdown pins them forever and the
organization can never reach them again. On this install that had already
happened to 18 of 26 people. See `AppTheme::personalVariantToStore()`.

**3. `system` is not a synonym for light.** The browser decides at runtime from
`prefers-color-scheme`; the stored variant is the palette to use once it has.

**4. Server beats localStorage, always.** The pre-paint script
([`partials/_theme-prepaint.blade.php`](../resources/views/partials/_theme-prepaint.blade.php))
only acts in two cases: resolving `system` mode ahead of the stylesheets, and
filling in for a document that rendered no theme at all. A cache that could
outrank the database is how "I saved a theme and it came back wrong" happens.

### The signed-out surface has no theme

`layouts/auth.blade.php` hardcodes `data-bs-theme="light"`, links no
`awt-themes.css` and includes no pre-paint. A theme is a property of a person
and there is no person yet at the login screen, so the alternative is guessing —
and a login page that flickered from a guess to the real palette on the first
authenticated page is worse than one that never claimed to know.

### One picker, two scopes

`partials/_theme-picker.blade.php` is used by both the personal and the
organization settings pages; `$scope` is the only difference. The organization
copy explains itself when the admin viewing it has a personal theme that
outranks what they are about to save — otherwise saving works, the row is
stored, and nothing visible changes, which reads exactly like a bug.

### Writing themeable CSS

Read variables, never hex:

```css
/* Good — follows whatever theme the viewer picked */
color: rgb(var(--bs-primary-rgb));
border-color: rgba(var(--awt-tone-rgb), 0.16);

/* Bad — pins one palette */
color: #696cff;
```

A fixed colour is legitimate only when the colour **carries meaning that must
survive a theme change** — e.g. the two traffic series on the admin dashboard
are cyan and violet in both themes because they are read against each other.
When you do this, say why in a comment.

---

## 4. Surfaces — the glass system

[`awt-glass.css`](../public/assets/css/awt-glass.css) is the shared look every
card in the app is built from. Deliberately unscoped so any page can wear it.

### Tones

```css
.awt-tone-primary { --awt-tone-rgb: var(--bs-primary-rgb); }
/* …info, success, warning, danger, secondary, dark */
```

A tone sets **one variable**. Every glass rule reads `--awt-tone-rgb` rather
than a colour, so a new tone is one class and a theme change carries everywhere
at once.

### Primitives

| Class | What it is |
|---|---|
| `.awt-glass-card` | The surface: translucent fill, a short live backdrop blur (`--awt-glass-blur`), accent wash off one corner, accent-tinted shadow |
| `.awt-stat-icon` `.awt-stat-label` `.awt-stat-value` `.awt-stat-desc` `.awt-stat-note` | A statistic on that surface |
| `.awt-glass-intro` + `-copy` `-title` `-subtitle` `-actions` | Page banner |
| `.awt-glass-pill` | A single linked figure on the banner (reads its own tone) |
| `.awt-glass-control` + `-label` | A labelled control inside the banner's action cluster |

### Badges, pills and status marks

Three different things, and using the wrong one is the commonest way a screen
stops looking like the rest of the app.

| You want | Use | Comes from |
|---|---|---|
| A **state** on a row — Active, Pending, Verified | `<span class="badge bg-label-success">` | Vuexy |
| A **figure** in a page banner — "6 people", "3 active" | `.awt-glass-pill` (+ a tone class) | the kit |
| A **mark** that must survive images being blocked (email) | a text glyph in a coloured round cell | hand-rolled per template |

`bg-label-*` is Vuexy's soft badge — tinted background, coloured text, no fill.
The app uses it 67 times and effectively nowhere uses the solid `bg-*` variant;
a solid badge in a table reads as a button somebody forgot to wire up.

The tone-to-meaning mapping is fixed across the whole app, and a screen that
picks its own is the thing this table exists to prevent:

| Tone | Means |
|---|---|
| `success` | working, verified, clean, active |
| `warning` | needs a person, pending, screened, expiring |
| `danger` | failing, blocked, rejected, at risk |
| `info` | in flight, submitted, acknowledged |
| `secondary` | not applicable, not enrolled, not monitored, draft |

**`secondary` is not a soft `danger`.** "Not monitored" and "clean" must never
render alike — the first is *we have not looked*, and dressing it in green is
the one claim that turns a status board into a liar. The same rule governs the
deliverability report ([57](57-deliverability-report.md)).

**A tile's tone is fixed, never derived from its value.** A figure that changes
colour reads as a *different metric*, not as the same one having moved. Decide
the tone from what the tile counts, once, in the markup.

An initial-avatar for a named row is the same vocabulary:

```blade
<span class="avatar avatar-sm">
    <span class="avatar-initial rounded bg-label-primary">{{ strtoupper(substr($row->name, 0, 1)) }}</span>
</span>
```

### Info cards, empty states and callouts

A panel that *tells* rather than *counts* is still a glass card — it just uses
the intro primitives without a KPI row:

```blade
<div class="awt-glass-card awt-tone-warning">
    <div class="awt-glass-intro">
        <div class="awt-glass-intro-copy">
            <h4 class="awt-glass-intro-title">Purchasing is unavailable</h4>
            <p class="awt-glass-intro-subtitle">No messaging profile is configured for this organization yet.</p>
        </div>
        <div class="awt-glass-intro-actions">
            <a href="…" class="btn btn-primary">Configure</a>
        </div>
    </div>
</div>
```

Two rules that keep these from multiplying:

**A flash message is a Bootstrap `alert`, not a glass card.** It is transient
and it is about *what just happened*; a glass card is furniture and is about
*what is true*. Mixing them means a page where dismissing something leaves a
hole.

**An empty state lives in the table, not beside it.** Put it in the `@empty`
branch of the row loop, spanning every column, with the same button the banner
carries — so "no departments yet" and "add a department" are one thought:

```blade
@empty
    <tr>
        <td colspan="4" class="text-center py-5">
            <i class="icon-base ti tabler-building-store text-muted mb-3 display-4"></i>
            <h6 class="text-muted mb-2">No departments yet</h6>
            <p class="text-muted small mb-0">Click "Add Department" to create your first one.</p>
        </td>
    </tr>
@endforelse
```

### Conventions inside it worth keeping

**Hover elevates but promises nothing.** The glass brightens and the shadow
deepens; the cursor never changes and nothing moves. A card that rises under the
pointer reads as a link, and these are not.

**One `gap` owns every vertical space in a card body.** A card carrying a
footnote is spaced exactly like one without, which is what keeps a row of them
level whatever each has to say.

**Live blur is idle-only.** `backdrop-filter` is recast for every glass surface
on every scroll frame, which is why a long dashboard used to hitch. The kit
keeps `--awt-glass-blur` at `blur(8px)`, never stacks a second filter on a tile
inside a card, and `awt-glass.js` sets `html.awt-scrolling` so the live blur
drops while the page is moving. Nested tiles keep the translucent fill.

The decorative corner orb is a `::after` pseudo-element — invisible to assistive
technology by construction rather than by `aria-hidden`.

---

## 5. Page anatomy

The standard composition, top to bottom:

```
banner  (.awt-glass-card.awt-tone-primary > .awt-glass-intro)
KPI row (.row.g-4 of .col-sm-6.col-xl-3 > .awt-glass-card.h-100)
content (table in <x-awt-table>, or a chart, or a master-detail pair)
```

A page file follows this skeleton:

```blade
@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@push('styles')
    {{-- shared surfaces first… --}}
    <link rel="stylesheet" href="{{ asset('assets/css/awt-glass.css') }}?v={{ filemtime(public_path('assets/css/awt-glass.css')) }}" />
    {{-- …then this screen's own layer on top, namespaced .awt-cc-* --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin-command-center.css') }}?v={{ filemtime(public_path('assets/css/admin-command-center.css')) }}" />
@endpush

@section('content')
    {{-- markup --}}
@endsection

@push('scripts')
    {{-- page JS --}}
@endpush
```

**Presentation helpers go in one `@php` block at the top of `@section('content')`,
not inline.** The dashboard's `$rateTone()` is the example: "a rate is good,
watchable, or bad" is decided once, so every percentage on the page uses the
same thresholds.

### Icons

Tabler, via iconify, always in this form:

```blade
<i class="icon-base ti tabler-phone-calling" aria-hidden="true"></i>
```

`aria-hidden` on every decorative icon. An icon that is the *only* content of a
control needs an `aria-label` on the control.

---

## 6. Shared components

`resources/views/components/` holds the tags that carry a decision which would
otherwise be re-made, and re-made differently, on every screen that needs it.
Reach for one before writing markup that does the same job.

| Tag | What it owns |
|---|---|
| `<x-awt-table>` | The AJAX table wrapper — §8 |
| `<x-awt-phone-input>` + `<x-awt-phone-assets>` | A phone field: `intl-tel-input` with searchable dial codes, a visible display value and a hidden E.164 field that is what actually posts |
| `<x-awt-premium-modal feature="…">` | The app's one paywall, for every add-on |
| `<x-awt-ai-line :status="…">` | One line of AI state under a row |
| `<x-video-call-button :number="…">` | "Video call this teammate", wherever a teammate appears |
| `<x-ringtone-picker>` | Built-in + uploaded ringtones with in-place preview |
| `<x-ppb.badge status="…">` | Personal Phone Bridge status, on four screens |
| `<x-caller-id-selector>` | Outbound caller ID selection & branded calling pitch UI |
| `<x-deliverability-indicator>` | Carrier sweep deliverability status & reputation indicators |
| `<x-fcr-automation-badge>` | First Contact Resolution (FCR) automation status |
| `<x-settings.tab>` / `<x-settings.section-form>` | Self-saving settings tabs — §10 |

Four properties recur, and they are what make a component worth having rather
than a wrapper around some markup.

**It renders nothing when it does not apply.** `<x-awt-premium-modal>` emits
nothing for a viewer who already owns the add-on, is signed out, or asks for a
feature the platform does not sell — so an entitled agent carries no paywall
markup at all. `<x-video-call-button>` emits nothing when the number it is given
does not belong to a teammate. That is what lets the same tag sit in a contacts
list, a call row and a text thread without any of them knowing the rule.

**It answers the question itself, from one source.**
`<x-video-call-button>` decides "is this a colleague?" by matching the number
against the tenant's extension directory — the same directory internal voice
calling dials — rather than taking a flag from the caller. `<x-ppb.badge>` maps
six statuses to a label, a tone and an icon in one place, so the settings page,
the per-agent tab, the users table and the dashboard card cannot drift.
`<x-awt-premium-modal>` reads name, price, icon and benefit lines from
`config('billing.premium_features')`, so a new add-on gets a working paywall from
a config entry and no new Blade, CSS or JS.

**It knows about more than one audience.** The paywall shows an agent "Request
Access from Admin" with a note to send; it shows an admin a link to the switch,
because asking yourself for permission you already hold is nonsense. Neither
path writes.

**Assets come with it, once.** `<x-awt-phone-assets>` is an `@once` block that
pushes the vendor CSS/JS the phone field needs, so a page with six phone inputs
links them one time and a page with none links nothing.

> Every one of these opens with a docblock naming its props and its contract —
> including, on the two that carry long commented examples, the warning that
> **Blade comments do not nest** (§15). Keep that habit: a component is read far
> more often than it is written, and the caller cannot see its rules from the
> call site.

---

## 7. CSS conventions

These are the rules the codebase actually holds itself to. A new screen
stylesheet opens with a comment saying which of them it is honouring.

1. **One file per screen**, named `<area>-<screen>.css`, loaded by that screen.
2. **Namespace every class** with a short screen prefix. Three families are in
   use and each says which surface it belongs to:
   `awt-<screen>-*` for admin and organization-admin screens (`.awt-cc-*`
   command centre, `.awt-rc-*` rate cards, `.awt-did-*` DID inventory);
   `rc-<screen>-*` for the agent workspace panes (`.rc-phone-*`,
   `.rc-contacts-*`, `.rc-conv-*`); and `tal-*` for the agent shell itself,
   which is the frame and belongs to no screen. Two screens never share a class
   unless it lives in the kit.
3. **Additive, and no `!important`.** Build *from* the theme's classes rather
   than fighting them. If you need `!important`, you are usually overriding
   something you should have read as a variable instead.
4. **Scope state to the block, not the element**: `.awt-cc-value.is-good`, not a
   global `.is-good`.
5. **Logical properties** — `inline-size`, `block-size`, `inset-inline-end` —
   over physical ones. The shell is `dir="ltr"` today; this keeps it cheap not
   to be.
6. **Guard modern CSS.** `color-mix()` is used with an
   `@supports not (color: color-mix(...))` fallback block.
7. **Respect `prefers-reduced-motion`.** Slow the animation, do not simply
   delete it — the table spinner still needs to say it is working.

---

## 8. Tables

Full reference in [47 — Table Pagination](47-table-pagination.md). The contract
in short:

```blade
<x-awt-table id="admin-did-inventory" :state="['page' => $numbers->currentPage(), 'per_page' => $perPage]">
    <table class="table">…</table>
    {{ $numbers->links() }}
</x-awt-table>
```

Inside the wrapper:

- anything inside `.pagination` is intercepted;
- a rows-per-page or filter form carries `data-awt-table-form`;
- a filter link carries `data-awt-table-link`;
- everything else — row actions, View buttons, `mailto:` — is left alone.

`state` is both the current state **and the whitelist**: only these keys are
persisted and only these are read back from a URL. Never pass a record id, a
token, or a cursor.

Server side:

```php
$perPageOptions = [10, 25, 50, 100];
$perPage = (int) $request->integer('per_page');
$perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;   // 10 is the app-wide default

if (TableFragment::wants($request, 'admin-did-inventory')) {
    return view('admin.phone-numbers.partials.inventory-table', $data);  // just the table
}
```

Three things this buys, and each is a rule in its own right:

- **Rows-per-page is always whitelisted.** A crafted `per_page` must not be able
  to ask the database for the whole table.
- **The fragment response is an optimisation, not a contract.** The script picks
  its table out of whatever HTML comes back, so a controller that ignores
  `wants()` still paginates correctly — it just re-renders more than it needs to.
- **It degrades.** Without JavaScript the links are ordinary GET links and the
  table still works.

Remembered state is keyed by `TableFragment::scopeToken()` — an HMAC of user +
tenant id, so an operator's page 3 never leaks into an admin's view of the same
route, and no database id ends up in browser storage.

### Putting a listing on glass

The composition, which `tenant-team.css` and `tenant-phone-numbers.css` both
implement and either can be lifted from:

```blade
<div class="awt-glass-card awt-tone-secondary awt-team-panel">
    <div class="awt-team-bulkbar">…</div>       {{-- optional, only with selection --}}
    <div class="awt-team-table table-responsive">
        <table class="table table-hover align-middle">…</table>
    </div>
</div>
```

Four rules, and each exists because breaking it produced a visible bug:

**Make the table transparent.** Vuexy's `.table` paints its own background, and
over glass that reads as an opaque slab dropped onto the card. Set
`--bs-table-bg: transparent` and express the hover tint as a tone wash instead.

**Pad the container; do not let the table sit flush.** A glass card has
`overflow: hidden` and a `1rem` radius. A right-aligned Actions column at the
very edge has its buttons clipped by the corner on the first and last rows.
`padding-inline: 1rem` on the scroll container puts the whole column inside the
straight part of the edge, and the header and last row need the vertical
equivalent.

**Do not give the scroll container the card's radius.** `border-radius: inherit`
on a `.table-responsive` makes it clip its own content at the corners — the same
bug one level down.

**Hairlines, not rules.** Row borders read as
`rgba(var(--awt-tone-rgb), 0.1)` and the last row drops its border entirely, so
a twenty-row table stays a status board rather than becoming a ledger.

### Row actions

An icon-button cluster in a right-aligned final column:

```blade
<td class="text-end text-nowrap">
    <button class="btn btn-sm btn-icon btn-outline-primary edit-thing" data-id="{{ $row->id }}" title="Edit">
        <i class="icon-base ti tabler-edit"></i>
    </button>
    <button class="btn btn-sm btn-icon btn-outline-danger delete-thing" data-id="{{ $row->id }}" title="Delete">
        <i class="icon-base ti tabler-trash"></i>
    </button>
</td>
```

`btn-outline-primary` for the safe action, `btn-outline-danger` for the
destructive one, `btn-outline-secondary` for anything in between. Destructive
actions route through [`AwtConfirm`](#9-dialogs-and-notifications), never
`window.confirm`.

**Bind row-action handlers by delegation on a container, never on the buttons.**
Rows are replaced wholesale on every page change, so a handler bound to a button
dies with the row it was bound to — see
[47](47-table-pagination.md) and the Turbo note in §15.

**A row that is itself clickable must still let its buttons through.** The
delegated row handler bails on interactive descendants first:

```js
container.addEventListener('click', function (event) {
    if (event.target.closest('a, input, label, button')) { return; }
    const row = event.target.closest('.thing-row');
    if (row?.dataset.href) { window.location.href = row.dataset.href; }
});
```

**A row action that reuses a bulk flow must replace the selection, not add to
it.** Narrow to the one row — clearing the checkboxes *and* the tracking Set —
before triggering the bulk control. A selection helper that treats the DOM as
its source of truth will otherwise put every still-ticked row straight back on
the next sync, and "release this one" releases four.

---

## 9. Dialogs and notifications

### `AwtConfirm` — the app's only confirm/prompt

Native `confirm()` cannot be styled, cannot show a loading state, blocks the
tab, and renders as *"phone.allwetrade.com says"* — which reads as a browser
warning rather than as your application asking a question.

```js
// Ask, then act
if (await AwtConfirm.open({ title: 'Cancel meeting', message: '…' })) { … }

// Ask AND run the work inside the dialog, so buttons can show a spinner
// and a failure is reported without losing the dialog
AwtConfirm.open({
    title: 'Delete recording',
    confirmText: 'Delete',
    tone: 'danger',
    onConfirm: async () => { const r = await fetch(url); if (!r.ok) throw new Error('…'); },
});

// Ask for a value
const url = await AwtConfirm.prompt({ title: 'Insert link', label: 'URL' });
```

Declaratively, with no JS at the call site — this is what replaced every inline
`onsubmit="return confirm(…)"`:

```blade
<form … data-awt-confirm="Everyone invited will lose access."
        data-awt-confirm-title="Cancel meeting"
        data-awt-confirm-text="Cancel meeting"
        data-awt-confirm-tone="danger">
```

**It is self-contained on purpose** — it injects its own markup and styles on
first use and depends on no Bootstrap, SweetAlert2 or jQuery. Four layouts would
otherwise each need the same include, and any one that was missed would silently
fall back to a native dialog, which is the bug being fixed. It also puts the
overlay directly under `<body>`, above every header and drawer, with no stacking
surprises.

> **Porting note:** on any page that swaps `<body>` (Turbo), re-run its attach.
> A self-contained IIFE that binds once still needs its listeners back after the
> document body it bound to is replaced.

### Toasts

Session flash is bridged to JS in the layout — `window.sessionMessages.success`,
`.error`, `.info`, `.warning`, `.status`, `.errors[]` — and `notifications.js`
renders it.

For a toast raised from script, call the bare helpers in `toast-helpers.js`:
`showSuccess` / `showError` / `showWarning` / `showInfo`. They pick the best
renderer available (Notiflix → Notyf → console) and are deliberately **not**
`alert()`, for the same reason `AwtConfirm` exists. `alerts.js` layers
`Alerts.showSuccess/showError/showConfirmation/…` on top for callers that want
the namespaced form. Never call `notyf.*` directly.

### Two more declarative primitives

Both are one delegated handler for the whole app, so a row fetched later needs
no re-binding.

**Copy to clipboard** — `awt-copy-link.js`. Any element with `data-awt-copy`
copies that text on click, and because it is a real `<button>` it is keyboard
accessible for free. Success and failure go through the toast helpers above, so
load it after them.

```blade
<button type="button"
        data-awt-copy="{{ $meeting->guestUrl() }}"
        data-awt-copy-success="Guest meeting link copied"
        data-awt-copy-error="Could not copy — copy it manually">
```

> Nothing secret ever goes in that attribute. The value is a public URL or code
> already printed on the page — no token, no passcode, no internal id.

**Preview a private document** — `awt-doc-preview.js` plus
`@include('partials.document-preview-modal')`. A `[data-awt-preview]` button
renders the file in place instead of downloading it; `AwtDocPreview.render(el, …)`
draws into any pane, which is how the compliance reviewer keeps the document on
screen while deciding. Two things it is strict about: the `kind` (`pdf` /
`image` / `none`) is decided by the **server** from the stored filename rather
than sniffed in the browser, and the frame is emptied when the modal closes so a
private document is not left loaded — and decrypted in memory — behind whatever
the user does next.

---

## 10. Forms

### Settings tabs save themselves

```blade
<x-settings.section-form section="message">
    <input type="checkbox" name="bold_unreads" value="1" @checked($tabPrefs['bold_unreads'] ?? false)>
</x-settings.section-form>
```

Each input's `name` is the schema key for that section. A toggle, radio, select
or slider saves the moment it changes; a typed field 600 ms after the user stops
typing, or on blur. One status line per tab reports *Saving… / Saved / Could not
save*, in `role="status" aria-live="polite"` so it is announced once without
stealing focus, and it keeps the row the Save button used to occupy so nothing
collapses.

**Settings tabs only.** A modal whose fields must be reviewed together before
submission keeps its own explicit Save button.

### Shared field sets

When a create page and an inline editor edit the same record, they include the
**same** `partials/_fields.blade.php`. The rate-card editor is the worked
example — and it is also the worked example of the trap:

> If a field leaves the form, it must leave the **validator's dimension list**
> too. Otherwise the controller reads an input that is never posted, defaults it
> to `0`, and silently wipes the column on every save. See
> `BillingController::validateRateCard()`.

### When a create form earns its own page

Editing stays inline when you are adjusting a record you can already see, beside
the figures you are adjusting it against. Creating is the opposite situation —
nothing to look at yet, many fields to think about, and a scope decision that
changes what the whole record applies to. That earns a page.

---

## 11. Responsive and master-detail

Breakpoints, matching Bootstrap: `991.98px` (tablet), `767.98px` (mobile),
`575.98px` (small phone), plus a landscape-phone guard at
`(max-height: 520px) and (orientation: landscape)`.

All of it lives in `awt-responsive.css`, loaded **last**, entirely inside media
queries. Desktop is provably unaffected.

Two/three-pane list+detail screens collapse to a single pane below `md`, and
`awt-master-detail.js` flips which pane shows. Opt in with three markers on
containers you already have — no new nesting, no per-row edits:

```html
<div data-awt-md>
    <div data-awt-md-list>…</div>
    <div data-awt-md-detail><button data-awt-md-back>…</button></div>
</div>
```

The detail opens as a bottom sheet over the dimmed list, so you can see where
you were. Opening pushes one history entry on mobile, so hardware Back returns
to the list with its tab, search, filters, pagination and scroll position
intact — because the list is never re-rendered. Delegated on `document` and
bound once, so Turbo navigations never double-bind.

Opted in today: Phone (`rc-phone-body`), the Auto Dialer workspace, the Video
hub, and Text and Contacts in **both** their agent and their admin rendering.
A row tap reveals the detail directly. `data-awt-md-manual` on the wrapper
suppresses that — the script still honours it — but no screen sets it any more:
on Phone it left an agent updating a pane they could not see, and needing a
second tap on a HUD button to read it.

### Touch row gestures

`awt-mobile-view-gestures.js` + `awt-mobile-view-gestures.css` add the two
gestures a phone user reaches for without thinking. Both files are gated on the
**same** condition — `matchMedia('(max-width: 768px)')` **and** a device that
reports touch — and every CSS rule sits inside that media query, so on desktop
neither contributes anything at all.

| Gesture | Effect |
|---|---|
| Swipe left past `80px` | The row's destructive action (Delete / Clear), red zone |
| Swipe right past `80px` | The row's status action (Mark read / Flag / Dial), emerald zone |
| Press and hold `500ms` | The floating action sheet `#mobileActionSheet`, plus `navigator.vibrate(50)` |

Opt in with a marker on the container and one on each row. The container also
carries the endpoints, so the script never hardcodes a URL:

```blade
<div id="rcVoicemailList"
     data-awt-gestures="voicemail"
     data-awt-gestures-delete-url="{{ route('tenant.calls.voicemail.destroy') }}"
     data-awt-gestures-flag-url="{{ route('tenant.calls.voicemail.flag') }}">
    <div class="rc-call-item" data-mobile-gesture="true" data-vm-id="7">…</div>
</div>
```

Registered lists: `conversations` (`#conversationList`), `voicemail`
(`#rcVoicemailList`), `history` (`#rcCallsList`). A row built in JS needs the
flag in its builder too, or a refreshed list stops being swipeable.

Four rules make it safe to sit on top of screens that already work:

- **Scrolling wins ties.** The first ~10px decide the axis. If the vertical
  component is greater *or equal*, the gesture is abandoned for that whole touch
  and `preventDefault()` is never called. Rows carry `touch-action: pan-y`, so
  vertical panning stays on the browser's own fast path. Multi-touch aborts.
- **Existing controls keep their taps.** A touch starting on a `button`, link,
  input, `label` or `audio` inside the row is not a gesture. A list in
  `.rc-edit-mode` (multi-select) is skipped entirely.
- **The trailing click is swallowed.** A committed swipe or a long press eats
  the synthetic click that follows, in the capture phase — otherwise
  `awt-master-detail.js` would also slide the detail sheet up over the action
  just taken. Load this file **after** master-detail. Clicks the script
  dispatches itself are flagged and let through.
- **No new endpoints, no DOM restructuring.** Each action either replays a click
  on a control the row already renders (so that module's own handler, confirm
  dialog and toast run unchanged) or calls the same REST endpoint with the same
  payload. Rows are never wrapped — several modules select rows with
  `$list.children(…)` — so the two coloured zones are appended *inside* the row
  at `right: 100%` / `left: 100%` and ride its transform.

Voicemail's right edge toggles **Flag**, not read/unread: playing a message is
what marks it heard server-side, so there is no read endpoint to map a swipe
onto.

Pinned by `resources/js/ui/mobile-view-gestures.test.js` (30 tests).

---

## 12. Navigation

### The vertical menu

Both admin shells. Sections are hand-written in the sidebar partial — Platform
Admin groups them as *Operations · Billing · System*, the organization admin as
*Phone System · AI & Automation · Advanced Tools · Account*.

```blade
<li class="menu-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}" class="menu-link">
        <i class="menu-icon icon-base ti tabler-smart-home"></i>
        <div data-i18n="Dashboard">Dashboard</div>
    </a>
</li>
```

- Active state comes from `request()->routeIs('…*')` — the wildcard keeps a
  parent lit on child routes.
- Sections are separated by `<li class="menu-header small text-uppercase">`.
- Sub-items get icons from `awt-menu.css`, which is loaded **by the layout**,
  not pushed from a page — the sidebar is rendered by the layout, so a
  page-level `@push` would miss it.
- The collapse toggle carries `aria-label`, `aria-expanded` and `aria-controls`,
  kept current by a script at the foot of the partial.

The agent rail is the other half of this and works the opposite way round —
config-driven, per-user ordered, badged. It is described with the shell it
belongs to, in §2.

### Tab strips that outgrow their pane

A tab row inside a master-detail pane has no fixed width: the Phone strip has
360px in a desktop split and most of the window on a tablet. Deciding in the
markup which tabs are visible therefore gets it wrong at every width but one —
the pattern this app used to carry, a "promoted slot" that held whichever of
two tabs you last picked, made the row change shape depending on where you had
been and hid tabs that would have fitted with room to spare.

Two answers, both measured after layout, neither authored into the markup:

**Overflow into a menu** — `public/assets/js/awt-tab-overflow.js`. Every tab is
written inline in priority order and the `⌄` menu starts empty; the script
measures the row and moves tabs in from the END until the rest fits. If they
all fit, the chevron is not drawn at all. Used by the Phone strip.

```blade
<div class="rc-phone-tabs-row" data-awt-tab-overflow>
    <ul class="nav nav-tabs" data-awt-tab-overflow-strip> … </ul>
    <div class="dropdown is-empty" data-awt-tab-overflow-more>
        <a data-bs-toggle="dropdown">⌄</a>
        <ul class="dropdown-menu" data-awt-tab-overflow-menu></ul>
    </div>
</div>
```

The `<li>` elements are **moved**, never re-rendered, so handlers and ids
survive the trip; only `.nav-link` ↔ `.dropdown-item` is swapped. Two things
follow from that and are easy to get wrong:

- A Bootstrap `Tab` resolves its siblings **once**, in its constructor, with
  `closest('.list-group, .nav, [role="tablist"]')` — and the menu is not inside
  the strip. So the module disposes the cached instance whenever an anchor
  moves, intercepts menu clicks to put the tab back in the row *before* showing
  it, and exposes `AwtTabOverflow.promote(el)` for any page that activates a
  tab by key (deep links, Back).
- The active tab is never moved into the menu. An underline nobody can see is
  the same as no underline.

Two invariants were added after a bug that hid tabs **behind a chevron that was
itself hidden** — the one failure mode here a user cannot work around. Keep both:

- **Authored order is written down, not re-derived.** Each tab is stamped
  `data-awt-tab-index` the first time a strip is seen, and the order is rebuilt
  from **both halves — row and menu**. `turbo:load` fires on a first load too, so
  every visit scans twice; the second pass used to read "the authored order" out
  of a row this module had already trimmed, orphaning whatever was in the menu.
  The stamp travels in the markup, so it also survives a Turbo snapshot restore,
  where the cached body already has tabs in the menu.
- **The chevron is a fact, not an inference.** `layout()` ends by asking the menu
  whether it has children and draws the chevron if and only if it does — rather
  than trusting whichever branch that pass happened to take. Whatever put a row
  there (another script, a restored snapshot, a controller that ran first), the
  answer is the same.

**Scroll the row** — the Contacts strip. All seven filters live in one
horizontally scrolling row with `‹ ›` buttons that appear only when there is
something past that edge (`contacts.js`, `syncTabArrows()`). Cheaper, and it
keeps every tab one gesture away, but it costs a pair of controls the overflow
menu does not need when everything fits.

Reach for the overflow menu when the row usually fits and the extra tabs are
secondary; reach for the scroller when the row rarely fits and no tab is
clearly lowest-priority.

---

## 13. Live figures

Two patterns, one rule behind both: **the server is the only thing that renders
a figure.** What differs is the size of the unit being replaced.

### A. Field swap — the admin dashboard

The server builds **one** metrics array shared by the initial render and the
JSON feed, so the two cannot drift, and the markup tags each figure with
`data-metric`. The script writes text into those spans and nothing else.

```blade
<div id="admin-dashboard"
     data-metrics-url="{{ route('admin.dashboard.metrics') }}"
     data-poll-interval="15000">
    <span data-metric="voice.live">{{ number_format($voice['live_calls']) }}</span>
```

Where a figure carries a *judgement* as well as a value, the threshold has to
exist on both sides — a `$rateTone()` closure in the Blade `@php` block and a
`toneClass()` in the script — and each carries a comment naming the other as its
twin. Keep them in step: a percentage that changed colour the moment a poll
replaced it would be reporting the poll, not the number.

Two costs are managed explicitly, and both belong in the controller's docblock:

- anything **expensive** is cached for slightly less than the human-noticeable
  staleness window (the margin figures: 60 s against a 15 s poll);
- anything that must be **current** is a `COUNT` over an indexed range, run per
  poll — "live" has to mean live.

### B. Section swap — the tenant dashboard

Eleven sections, each its own Blade partial. Changing the date range asks the
server for the dashboard again; the response carries a `sections` map of
**rendered HTML from those same partials**, and the script assigns each one into
its host by `data-dash-section`.

```blade
<div id="tenant-dashboard" data-metrics-url="…" data-range="30d" data-refresh-interval="60000">
    <div class="col-12 awt-dash-collapsible" data-dash-section="alerts">
        @include('tenant.dashboard.partials.alerts')
```

**This file renders no dashboard markup**, which is the whole point: there is
exactly one copy of every section's HTML, and editing a partial cannot leave a JS
mirror of it behind. The script owns only which markup is on screen, plus the
chart.

Five things it has to get right, and each is a rule for anything copying it:

- **Loading dims, never blanks.** Every section takes `.is-loading` and
  `aria-busy="true"` for the duration, so assistive tech is not read a
  half-swapped list and the page keeps its height.
- **The swap is announced once.** `[data-dash-announce]` is a
  `role="status" aria-live="polite"` line — a range change is a navigation for a
  screen-reader user and must not be silent.
- **A section that has nothing to say collapses with its wrapper.**
  `.awt-dash-collapsible:not(:has(*))` hides the column. The wrapper stays in the
  DOM either way, because the swap addresses it by `data-dash-section` and needs
  a target to write into.
- **Bootstrap does not know about the new nodes.** Tooltips are disposed and
  re-registered after every swap, or a re-swapped node ends up with two.
- **Nothing runs behind a hidden tab or across a navigation.** The timer skips
  while `document.hidden`, the in-flight request is aborted on a new one, and
  `turbo:before-cache` tears down the timer and destroys the chart. `init()` is
  idempotent and guarded by a `data-dash-init` stamp.

### Which to use

Field swap when the page is a fixed set of figures whose *shape* never changes.
Section swap when a change can alter what a block contains — rows appearing, a
list emptying, a whole section becoming irrelevant — because that is markup, and
markup belongs to Blade.

> A polled page must have **no side effects**. It is hit every 15 or 60 seconds
> by every person who leaves a tab open.

---

## 14. Accessibility rules we hold to

- Decorative icons: `aria-hidden="true"`. Icon-only controls: `aria-label`.
- Decorative flourishes are pseudo-elements, so they are invisible to assistive
  tech by construction.
- Status regions use `role="status" aria-live="polite"` — announced once,
  focus untouched.
- Dialogs trap focus, close on Escape and on backdrop click, and restore focus
  to the trigger.
- Contrast is corrected globally: Vuexy's default body/muted greys
  (`#6d6b77` / `#acaab1`) fail AA on white, so `awt-themes.css` darkens
  `--bs-body-color` and friends across the app while leaving dark mode alone.
- Loading states dim, never blank — the block keeps its height so nothing below
  it jumps.

---

## 15. Gotchas

**`hidden` + `d-flex` stays visible.** In `core.css`,
`[hidden] { display: none !important; }` is at line 598 and
`.d-flex { display: flex !important; }` at line 15589. Same specificity, both
`!important`, so the later one wins. An element carrying both is **shown**. Use
`d-none` for the hidden state, or toggle the `d-flex` class itself.

**Turbo re-binds.** Tenant and operator layouts run Turbo Drive with
`data-turbo-eval="false"` on vendor scripts so they are not re-executed. Any
script of yours that binds to elements inside `<body>` must either delegate on
`document` (bound once, guarded by a `window.__…Bound` flag) or re-attach after a
body swap.

**`@php(...)` inline form is broken** in this Blade version. Use the block form.

**A glass card's rounded corner clips whatever sits flush against it.**
`.awt-glass-card` carries `overflow: hidden` — it has to, or the decorative
corner orb escapes the radius — so a right-aligned Actions column, an avatar at
the left edge, or the first and last rows of a table run straight into the
curve and lose pixels. Pad the container rather than removing the overflow, and
do not hand the inner scroll container the same radius. See §8.

**Screen CSS in a `<style>` block is a layer violation, not a shortcut.** It
cannot be cached, cannot be found by anyone grepping the stylesheets, and is
re-parsed on every render of the page. `tenant/phone-numbers/index.blade.php`
carried 75 such lines — including Vuexy's default primary hard-coded as
`rgba(105, 108, 255, …)` in three places, which painted the wrong brand colour
on every other palette. Screen rules belong in
`public/assets/css/<area>-<screen>.css` and read `var(--bs-primary-rgb)`.

**A class name in a stylesheet may be owned by JavaScript.** Anything a page
builds over AJAX — table rows, search-result cards, bulk bars — carries class
names written in a `.js` file or a `@push('scripts')` block. Renaming such a
class while restyling a screen silently unstyles every dynamically-rendered
element, and nothing fails loudly. Grep the page's scripts for a class before
renaming it; `.phone-number-card`, `.capability-badge` and `.search-state` all
keep old names for exactly this reason.

**Blade comments do not nest.** A `{{--` opened inside a commented-out block
ends the *outer* block at its first closing delimiter, dropping the rest back
into live Blade with its `@if` already swallowed. That has produced a fatal
"unexpected endif" on every operator page once. Write notes inside a commented
block as bare prose — never the delimiters, not even as an example.

**A stylesheet pushed from the navbar or the foot of `<body>` is dropped.**
`@stack('styles')` has already been flushed by then. Link it from the layout —
see §2.

**The offline page cannot load Vuexy.** [public/offline.html](../public/offline.html) is shown by the service worker when there is no network, so it inlines a flattened copy of the auth shell (login / 403) rather than linking `core.css` or `page-auth.css`. Change the auth empty-state look → update that file in the same change. See [60](60-offline-and-pwa.md).

**A stale unversioned sheet outlives your edit.** `tenant-messages.css` is
served from the edge with a ten-year `max-age` and no version query, so a rule
added to it reaches a returning browser whenever the cache feels like it. New
rules go in a `?v={{ filemtime(…) }}` file beside the module that uses them,
which is why `awt-reminders.css`, `messaging-schedule.css`,
`messaging-voice.css` and `auto-dialer-workspace.css` exist as their own sheets.

**Do not nest `backdrop-filter`.** A glass card already blurs what sits behind
it. A KPI tile, health chip or metric tile inside that card must not set its
own blur — two live filters on the same pixels are what made the dashboard
feel like it was loading as you scrolled. `--awt-glass-blur` lives in
`awt-glass.css`; `awt-glass.js` drops it while `html.awt-scrolling` is set.

**Never return an Eloquent model from a JSON endpoint.** `encrypted` casts
decrypt on `toJson()`; a nested relation once leaked API credentials. Return
arrays.

**Editing CSS during screenshot work:** re-dump the page after a CSS change or
you are shooting the cached stylesheet.

---

## 16. Checklists

### Adding a screen to this app

- [ ] `@extends` the right layout for the audience — and if it is a screen both
      an admin and an agent reach, check it in **both** shells
- [ ] Namespace matches the surface: `awt-<screen>-*` on an admin screen,
      `rc-<screen>-*` in the agent workspace
- [ ] `@push('styles')` — `awt-glass.css` first, then your own file. Anything
      rendered from the navbar or the foot of `<body>` is linked by the layout
      instead, because the stack is already flushed
- [ ] New stylesheet named `<area>-<screen>.css`, namespaced, no `!important`,
      opening comment naming the conventions it follows
- [ ] Colours read from theme variables; any fixed hue justified in a comment
- [ ] Banner → KPI row → content
- [ ] Reused a component from §6 rather than re-deciding what it decides
- [ ] Tile tones are fixed per tile, never derived from the value; badge tones
      follow the app-wide mapping in §4 — and "not applicable" is `secondary`,
      never a shade of `success`
- [ ] A table on glass is transparent, padded off the card's corners, and its
      scroll container has no radius of its own (§8)
- [ ] Row-action handlers are delegated on a container, not bound to buttons
      that a page change will replace
- [ ] **When restyling an existing screen:** the columns, the `data` keys and
      the scripts are unchanged unless the change was asked for. Diff them
      against the previous revision rather than trusting the eye, and grep the
      page's JavaScript before renaming any class it might write
- [ ] Paginated tables wrapped in `<x-awt-table>` with a whitelisted `per_page`
      and a `TableFragment::wants()` branch
- [ ] Destructive actions go through `AwtConfirm`, not `confirm()`
- [ ] A modal that can be opened from inside another modal or a tab panel is
      mounted by the shell, not beside its trigger
- [ ] Any polled figure follows one of the two patterns in §13, and the endpoint
      has no side effects
- [ ] Icons `aria-hidden`; icon-only controls labelled
- [ ] Check it in a light variant, `dark`, and `high-contrast`
- [ ] Check it at 991 / 767 / 575 px
- [ ] Feature test asserting the page renders and each panel is present

### Bootstrapping a new Vuexy app on this kit

1. Install Vuexy; keep `vendor/` pristine.
2. Copy the portable files from §1; rename the `awt-` prefix throughout.
3. Port `AppTheme` — keep the three-step hierarchy and the
   choice-vs-inheritance rule; replace `TenantSetting` with your own org model,
   or drop step 2 if the app is single-tenant.
4. Rewrite the palettes in `awt-themes.css`. Keep the structure (`html.<prefix>-theme-*`
   blocks setting `--bs-primary` and surface variables); change the hex.
5. Build one layout with the head/foot order from §2, and copy the
   `_theme-prepaint` include. Leave the signed-out layout un-themed.
6. Port `TableFragment` and add the two `<meta>` tags.
7. Decide up front what the **shell** owns — the confirm dialog, the toasts, any
   modal openable from more than one place — and mount it there before the first
   page needs it. Retrofitting that later is what §2's two rules are the scar
   tissue from.
8. Build your first screen against the §16 checklist. It becomes the reference
   the rest of the app copies — so get the banner, the KPI row and the table
   right before writing the second one.
