# Agentoom Accessibility Widget (`agentoom/core-accessibility-widget`)

An embeddable, customizable web accessibility toolbar and assistive UX suite designed for **Agentoom** web applications and public agent portals.

---

> [!IMPORTANT]
> **Post-Splitting Versioning & Pre-Release Notice (`v0.8.0`)**
> Version `0.8.0` represents the initial standalone release following the modular package extraction and architectural splitting from the Agentoom core monorepo. This package is currently in a pre-release state undergoing integration testing and requires appropriate validation and test coverage across target environments before being designated as production-stable (`1.0.0`).

## 🌟 Overview

The `agentoom/core-accessibility-widget` package provides digital accessibility features to comply with WCAG 2.1 / ADA standards. It allows site operators to configure, style, and embed an accessible assistive panel directly into websites hosting Agentoom chat widgets or client applications.

- **Package Name**: `agentoom/core-accessibility-widget`
- **Version**: `0.8.0` (Pre-release / Post-split testing)
- **Namespace**: `Agentoom\AccessibilityWidget\`
- **Service Provider**: `Agentoom\AccessibilityWidget\Providers\AccessibilityWidgetServiceProvider`

---

## 🚀 Key Features

### 1. Comprehensive Accessibility Capabilities
The widget provides users with client-side controls to adapt page readability and interaction:
- **Visual Adjustments**: Contrast toggle (high contrast / inverted), saturation levels, monochrome mode, and hide images.
- **Typography & Layout**: Scalable font size (percentage), OpenDyslexic font switcher, adjustable line height, and letter spacing.
- **Reading Aids**: Interactive reading guide (ruler line) and enlarged high-visibility cursor.
- **Content Navigation**: Highlight clickable links, highlight headings/titles, page structure outline, and keyboard navigation enhancements.
- **Cognitive & Motor Support**: Stop animations (disables CSS animations and GIFs), tooltips, and dictionary integration.
- **Speech & Audio**: Screen reader and text-to-speech reading support.

### 2. Embeddable JavaScript Loader & Runtime
- **Lightweight Script Tag**: Simple drop-in `<script>` tag embedding via `/api/accessibility/loader/{slug}.js`.
- **Dynamic Configuration Endpoint**: Serves widget styling and active feature flags from `/api/accessibility/config/{slug}`.
- **Self-Contained Bundle**: The compiled runtime `/api/accessibility/widget.js` runs client-side with zero external JavaScript framework dependencies on host sites.

### 3. Multi-Tenant Widget Management
- Create multiple independent accessibility configurations (`AccessibilityWidget` model) mapped to different domains or agents.
- Customize position (bottom-left, bottom-right, top-left, top-right), primary color, icon style, and toggle specific tools on or off.

### 4. Admin Management Dashboard
- **Livewire Index & Editor**: Dedicated administrative UI (`AccessibilityWidgetIndex`, `AccessibilityWidgetCreateEdit`) for configuring widgets, testing live previews, and generating embed code snippets.
- **Import / Export**: Built-in data transfer support (`AccessibilityWidgetsSection`) for transferring widget configurations across environments.

### 5. Multi-Language Support
- Localized in 5 languages: English (`en`), German (`de`), Spanish (`es`), French (`fr`), and Italian (`it`).

---

## 📁 Package Structure

```
core-accessibility-widget/
├── config/
│   └── accessibility-widget.php    # Default feature settings and route prefix
├── database/migrations/            # Table schemas for accessibility_widgets
├── resources/
│   ├── lang/                       # Translations (de, en, es, fr, it)
│   └── views/
│       ├── loader.blade.php        # Dynamic JS loader template
│       ├── widget.blade.php        # Widget HTML and client-side styling
│       └── livewire/admin/         # Livewire admin management views
├── routes/
│   ├── api.php                     # Endpoints for loader, config, and bundle
│   └── web.php                     # Admin configuration routes
└── src/
    ├── DataTransfer/Sections/      # Import/export handlers
    ├── Http/Controllers/           # AccessibilityWidgetController
    ├── Livewire/Admin/             # Livewire components (Index & Edit)
    ├── Models/                     # AccessibilityWidget Eloquent model
    └── Providers/                  # AccessibilityWidgetServiceProvider
```

---

## 🛠️ Configuration & Installation

Publish configuration and migration assets if necessary:

```bash
php artisan vendor:publish --provider="Agentoom\AccessibilityWidget\Providers\AccessibilityWidgetServiceProvider"
php artisan migrate
```

Configuration file: `config/accessibility-widget.php`:

```php
return [
    'defaults' => [
        'contrast' => false,
        'saturation' => false,
        'monochrome' => false,
        'font_size' => 100,
        'dyslexic_font' => false,
        'reading_guide' => false,
        'high_cursor' => false,
        'highlight_links' => false,
        'highlight_titles' => false,
        'line_height' => 1.5,
        'letter_spacing' => 0,
        'stop_animations' => false,
        'screen_reader' => false,
        'keyboard_navigation' => false,
        'hide_images' => false,
    ],
    'route_prefix' => 'api/accessibility',
];
```

---

## 💻 Embedding the Widget

Include the generated loader script on any website:

```html
<script 
    src="https://your-agentoom-domain.com/api/accessibility/loader/my-widget-slug.js" 
    async>
</script>
```
