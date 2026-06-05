# Ad Management System

## Overview

The project uses a dual-layer ad system:

1. **Legacy Direct AdSense** — hardcoded config-based slots in `config/services.php` (`adsense.*`).
2. **Dynamic DB-Driven System** — `GlobalAdBlock` model + `<x-ad-slot>` component for full control via admin panel.

This document covers the **dynamic `x-ad-slot` system** (recommended for all new placements).

---

## How `x-ad-slot` Works

The component accepts a `name` parameter, maps it to a `GlobalAdBlock.position` value, and renders all active scripts for that position.

```blade
<x-ad-slot name="sidebar-right-top" />
```

Internally it:
1. Looks up `name` in a position map (e.g. `sidebar-right-top` → `right_sidebar_top`).
2. Queries `GlobalAdBlock WHERE active=1 AND position='right_sidebar_top'`.
3. Caches the result for 3600 seconds.
4. Renders each script inside a semantic `<div>` container.

---

## Available Slot Names

| Slot Name              | DB Position          | Container Class          |
|------------------------|----------------------|--------------------------|
| `sidebar-right-top`    | `right_sidebar_top`  | `.ad-slot.ad-slot-vertical` |
| `sidebar-right-mid`    | `right_sidebar_mid`  | `.ad-slot.ad-slot-vertical` |
| `sidebar-right-bottom` | `right_sidebar_bottom` | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-top`     | `left_sidebar_top`   | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-mid`     | `left_sidebar_mid`   | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-bottom`  | `left_sidebar_bottom`| `.ad-slot.ad-slot-vertical` |
| `content-top`          | `above_content`      | `.ad-slot`               |
| `content-bottom`       | `below_content`      | `.ad-slot`               |
| `after-h1`             | `under_h1`           | `.ad-slot`               |
| `after-breadcrumb`     | `after_breadcrumb`   | `.ad-slot`               |
| `above-footer`         | `above_footer`       | `.ad-slot`               |
| `below-footer`         | `below_footer`       | `.ad-slot`               |
| `header-left`          | `header_left`        | `.ad-slot`               |
| `header-right`         | `header_right`       | `.ad-slot`               |
| `footer-left`          | `footer_left`        | `.ad-slot`               |
| `footer-right`         | `footer_right`       | `.ad-slot`               |
| `sticky-bottom`        | `sticky_bottom`      | `.ad-slot-sticky.fixed.bottom-0` |
| `sticky-left`          | `sticky_left`        | `.ad-slot-sticky.fixed.left-0` |
| `sticky-right`         | `sticky_right`       | `.ad-slot-sticky.fixed.right-0` |
| `mid-content-1`        | `mid_content_1`      | `.ad-slot`               |
| `mid-content-2`        | `mid_content_2`      | `.ad-slot`               |
| `mid-content-3`        | `mid_content_3`      | `.ad-slot`               |

If the name does not match any map entry, it is used directly as the DB position (fallback).

---

## Adding a New Ad Slot

### Step 1 — Register the DB Position

Add the new position key to `GlobalAdBlock::POSITIONS` array in `app/Models/GlobalAdBlock.php`:

```php
const POSITIONS = [
    // ... existing positions ...
    'my_new_spot' => '🆕 My New Spot',
];
```

### Step 2 — (Optional) Add a Name Alias

If you want a kebab-case shortcut, add an entry in the `$positionMap` inside `resources/views/components/ad-slot.blade.php`:

```php
$positionMap = [
    // ... existing mappings ...
    'my-new-spot' => 'my_new_spot',
];
```

### Step 3 — Place in Blade

```blade
<x-ad-slot name="my-new-spot" />
```

### Step 4 — Admin Panel Entry

Go to **Global Ad Blocks** → **Create** and fill:
- **Name** — friendly label (e.g. "My New Spot")
- **Position** — select `my_new_spot` from dropdown
- **Script** — paste the full ad code (AdSense `<ins>` + `<script>`, or any custom HTML/JS)
- **Network Type** — `Safe` or `Restricted`
- **Active** — ✅
- **Is Global** — ✅

Save. The ad will appear on the next page load (cache clears automatically on save/delete).

---

## Admin Panel — Global Ad Blocks

> **Module: `GlobalAdBlock`** (`App\Models\GlobalAdBlock`)
> **Filament Resource:** (create if not exists: `app/Filament/Resources/GlobalAdBlockResource.php`)

If the Filament resource does not exist yet, manually create it with these fields:

| Field            | Type         | Notes                                      |
|------------------|-------------|--------------------------------------------|
| `name`           | Text         | Internal label                              |
| `position`       | Select       | One of `POSITIONS` keys                     |
| `script`         | Code editor  | Raw ad HTML/JS (AdSense, custom, etc.)      |
| `network_type`   | Select       | `Safe` or `Restricted`                      |
| `active`         | Toggle       | Enable/disable without deleting             |
| `is_global`      | Toggle       | Applies site-wide vs taxonomy-specific       |
| `taxonomy_id`    | Select       | (Optional) Restrict to a specific category   |

### Quick CRUD via Tinker

```bash
php artisan tinker
>>> GlobalAdBlock::create([
    'name' => 'Sidebar Right Top',
    'position' => 'right_sidebar_top',
    'script' => '<ins class="adsbygoogle" ...></ins><script>...</script>',
    'network_type' => 'Safe',
    'active' => true,
    'is_global' => true,
]);
```

---

## Current Placements in Layout

`resources/views/layouts/app.blade.php`:

```
Line ~152:   <x-ad-renderer position="global_header" />   (legacy — keep)
Line ~255:   <x-ad-slot name="content-top" />
Line ~258:   @yield('content')
Line ~260:   <x-ad-slot name="content-bottom" />
Line ~268:   <x-ad-slot name="above-footer" />
Line ~306:   <x-ad-slot name="footer-left" />  (inside footer .grid)
Line ~316:   <x-ad-slot name="footer-right" /> (inside footer .grid)
```

Child views can add more slots as needed using the same `name` convention.
