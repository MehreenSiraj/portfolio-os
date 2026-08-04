# Design consistency audit

**Score: 7.5 / 10** — One coherent teal-on-canvas design system (tokens, `x-button` / `x-input` / `x-badge`, sidebar shell). Foundation, Work, and People pages mostly match. Money screens read slightly like a later bolt-on (titles, tables, empty patterns). No catastrophic “different apps” split by role; access is nav-filtered, same shell.

## What is consistent

- **Tokens** in `resources/css/app.css`: `canvas`, `surface`, `ink`, `muted`, `line`, `accent` (#0f766e), danger/success/warn softs; Plus Jakarta Sans + IBM Plex Mono.
- **App shell** (`layouts/app.blade.php`): mono eyebrow “Portfolio”, accent-soft active nav, canvas → surface cards (`rounded-xl border border-line bg-surface`).
- **Page chrome (M1–4)**: mono section label + `h1` `text-3xl font-semibold tracking-tight` + muted subtitle + primary `x-button` top-right.
- **Components**: primary/secondary/ghost/danger buttons; labeled `x-input`; tone badges; dashed `x-empty-state`; credential/task uploads via `x-file-input`.
- **Auth** (`layouts/guest.blade.php` + login): same token language and focus rings as app forms.
- **Role UIs**: same layout; sidebars differ only by permission gates (not alternate themes).

## Inconsistencies by severity

### High

| Page / area | Element | Recommendation |
|-------------|---------|----------------|
| `layouts/app.blade.php` mobile nav | Was missing Money, Login history, Task templates (desktop had them) | **Fixed:** mobile strip now mirrors desktop permissions + `shrink-0`. |
| Money index tables | Row Edit/Delete were raw `<button class="text-xs">` vs ghost `x-button` elsewhere | **Fixed** on Revenue & Expenses. |
| Dashboard Home | Month revenue caption still said “wired for M5” | **Fixed** → “PKR · this month”. |

### Medium

| Page / path | Element | Recommendation |
|-------------|---------|----------------|
| All `livewire/money/*` | Page titles `text-2xl`, no mono eyebrow (“Money”) | Match Work pattern: eyebrow + `text-3xl` + subtitle. |
| Tasks / Links / Attendance / Money tables | Hand-rolled `<table>` vs `x-table` | Prefer `x-table` or shared thead classes (`bg-canvas/70`, same paddings). |
| Money / Distributions / P&L empty | Inline “No … yet” table cell | Use `x-empty-state` when list empty (tasks/projects pattern). |
| Forms app-wide | Bare `<select>` / `<textarea>` next to `x-input` | Add `x-select` / `x-textarea` (or document bare selects as OK if styled token-matched). |
| Articles index | Card list (`p-4`) vs Links/Tasks tables | Pick one list pattern per density need; articles card is fine if intentional. |
| Settings form cards | `p-6` panels | Align to common `p-5` card padding. |
| `x-skeleton` | Defined, almost unused | Wire on Livewire loading targets for list pages. |

### Low

| Page / path | Element | Recommendation |
|-------------|---------|----------------|
| Card padding | Mix of `p-4` / `p-5` / `p-6` / `sm:p-6` | Standardize: filters `px-4 py-3`, content cards `p-5`. |
| Project/task detail | Back/quick links use secondary-style anchors, not `x-button` | Use `variant="secondary"` or ghost for parity. |
| Attendance “Clear” | Raw text button (like old money rows) | Ghost `x-button` size `sm`. |
| Guest h1 vs app h1 | Guest “Portfolio OS” `text-2xl` | Fine for marketing frame; leave. |
| Typography on money | Mono table headers slightly denser | Cosmetically align with `x-table` header class string. |

## Recommended next polish pass (top 5)

1. **Unify Money page headers** with M1–4 mono eyebrow + `text-3xl`.
2. **Normalize data tables** onto `x-table` (or one shared partial).
3. **Empty states** on Money (and any still-inline empties) via `x-empty-state`.
4. **Add `x-select` / tighten form stack** so filters and forms don’t mix control heights.
5. **Optional loading skeletons** on paginated indexes (Tasks, Projects, Revenue).
