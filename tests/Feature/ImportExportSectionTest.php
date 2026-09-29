<?php

namespace Agentoom\AccessibilityWidget\Tests\Feature;

use Agentoom\AccessibilityWidget\DataTransfer\Sections\AccessibilityWidgetsSection;
use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\DataTransfer\ImportExportRegistry;
use Agentoom\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportExportSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_package_registers_its_import_export_section(): void
    {
        $registry = $this->app->make(ImportExportRegistry::class);

        $this->assertTrue($registry->has('accessibility_widgets'));

        $section = $registry->get('accessibility_widgets');

        $this->assertInstanceOf(AccessibilityWidgetsSection::class, $section);
        $this->assertSame(ImportExportRegistry::GROUP_COMMUNICATION, $section->group());
        $this->assertSame('accessibility-widgets.view', $section->permission());
    }

    public function test_a_widget_can_be_exported_deleted_and_imported_again(): void
    {
        $widget = AccessibilityWidget::create([
            'name' => 'Round Trip Widget',
            'slug' => 'round-trip-widget',
            'is_active' => true,
            'position' => 'bottom-left',
            'settings' => ['color' => '#123456'],
            'allowed_domains' => ['example.com'],
        ]);

        $section = $this->app->make(ImportExportRegistry::class)->get('accessibility_widgets');

        $payload = $section->export([$widget->id]);

        $this->assertSame('round-trip-widget', $payload[0]['slug']);

        $widget->delete();
        $this->assertDatabaseMissing('accessibility_widgets', ['slug' => 'round-trip-widget']);

        $section->import($payload);

        $this->assertDatabaseHas('accessibility_widgets', [
            'slug' => 'round-trip-widget',
            'name' => 'Round Trip Widget',
            'position' => 'bottom-left',
        ]);

        $restored = AccessibilityWidget::where('slug', 'round-trip-widget')->firstOrFail();

        $this->assertTrue($restored->is_active);
        $this->assertSame(['color' => '#123456'], $restored->settings);
        $this->assertSame(['example.com'], $restored->allowed_domains);
    }
}
