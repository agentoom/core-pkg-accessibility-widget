<?php

namespace Agentoom\AccessibilityWidget\Http\Controllers;

use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AccessibilityWidgetController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $widget = AccessibilityWidget::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $this->validateDomain($widget)) {
            return response()->json(['error' => 'Domain not allowed'], 403);
        }

        return response()->json([
            'name' => $widget->name,
            'position' => $widget->position,
            'logo' => $widget->logo ? asset('storage/'.$widget->logo) : null,
            'icon_trigger' => $widget->icon_trigger ? asset('storage/'.$widget->icon_trigger) : null,
            'settings' => $widget->settings,
        ]);
    }

    public function loader(string $slug): Response
    {
        $widget = AccessibilityWidget::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $this->validateDomain($widget)) {
            return response('', 403);
        }

        $js = view('accessibility-widget::loader', [
            'slug' => $slug,
            'apiUrl' => url(config('accessibility-widget.route_prefix')),
        ])->render();

        return response($js)
            ->header('Content-Type', 'application/javascript');
    }

    protected function validateDomain(AccessibilityWidget $widget): bool
    {
        if (empty($widget->allowed_domains)) {
            return true;
        }

        $allowedDomains = $widget->allowed_domains;

        if (is_string($allowedDomains)) {
            $allowedDomains = array_map('trim', explode(',', $allowedDomains));
        }

        $origin = request()->header('Origin');
        if (! $origin) {
            $origin = request()->header('Referer');
        }

        if (! $origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        foreach ($allowedDomains as $allowed) {
            $allowed = trim($allowed);
            if (empty($allowed)) {
                continue;
            }

            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    public function widget(): Response
    {
        $activeLanguages = Language::where('is_active', true)->get();

        if ($activeLanguages->isEmpty()) {
            $locales = ['en', 'es', 'de', 'fr', 'it'];
            $availableLanguages = [
                ['language' => 'English', 'language_code' => 'en'],
                ['language' => 'Español', 'language_code' => 'es'],
                ['language' => 'Deutsch', 'language_code' => 'de'],
                ['language' => 'Français', 'language_code' => 'fr'],
                ['language' => 'Italiano', 'language_code' => 'it'],
            ];
        } else {
            $locales = $activeLanguages->pluck('code')->toArray();
            $availableLanguages = $activeLanguages->map(fn ($lang) => [
                'language' => $lang->name,
                'language_code' => $lang->code,
            ])->toArray();
        }

        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = __('accessibility-widget::widget', [], $locale);
        }

        $defaultLanguage = Language::where('is_default', true)->first();
        $defaultLocale = $defaultLanguage ? $defaultLanguage->code : 'en';

        $js = view('accessibility-widget::widget', [
            'translations' => $translations,
            'defaultLocale' => $defaultLocale,
            'supportedLocales' => $locales,
            'availableLanguages' => $availableLanguages,
        ])->render();

        return response($js)
            ->header('Content-Type', 'application/javascript');
    }
}
