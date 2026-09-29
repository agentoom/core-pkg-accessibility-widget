<?php

namespace Agentoom\AccessibilityWidget\Providers;

use Agentoom\AccessibilityWidget\DataTransfer\Sections\AccessibilityWidgetsSection;
use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\DataTransfer\ImportExportRegistry;
use Agentoom\Core\Services\PermissionRegistry;
use Agentoom\Core\Services\ResourceUsageRegistry;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AccessibilityWidgetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/accessibility-widget.php', 'accessibility-widget'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'accessibility-widget');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'accessibility-widget');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Livewire::addNamespace('accessibility-widget', null, 'Agentoom\\AccessibilityWidget\\Livewire');

        $this->app->make(PermissionRegistry::class)->register('accessibility_widgets', ['accessibility-widgets.view', 'accessibility-widgets.create', 'accessibility-widgets.edit', 'accessibility-widgets.delete']);

        $this->app->make(ResourceUsageRegistry::class)->register(
            'accessibility_widgets',
            fn (): int => AccessibilityWidget::count(),
        );

        $this->app->make(ImportExportRegistry::class)->register(new AccessibilityWidgetsSection);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/accessibility-widget.php' => config_path('accessibility-widget.php'),
            ], 'accessibility-widget-config');

            $this->publishes([
                __DIR__.'/../../resources/js' => public_path('vendor/accessibility-widget'),
            ], 'accessibility-widget-assets');
        }
    }
}
