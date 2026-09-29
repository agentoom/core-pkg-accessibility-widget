<?php

namespace Agentoom\AccessibilityWidget\Tests\Feature;

use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\Models\Project;
use Agentoom\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessibilityWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_widget_config(): void
    {
        $project = Project::factory()->create();
        $widget = AccessibilityWidget::create([
            'project_id' => $project->id,
            'name' => 'Test Widget',
            'slug' => 'test-slug',
            'is_active' => true,
            'settings' => [
                'contrast' => true,
                'font_size' => 120,
            ],
        ]);

        $response = $this->getJson('/api/accessibility/config/test-slug');

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Test Widget',
                'settings' => [
                    'contrast' => true,
                    'font_size' => 120,
                ],
            ]);
    }

    public function test_cannot_get_inactive_widget_config(): void
    {
        AccessibilityWidget::create([
            'name' => 'Inactive Widget',
            'slug' => 'inactive-slug',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/accessibility/config/inactive-slug');

        $response->assertStatus(404);
    }

    public function test_can_get_loader_script(): void
    {
        AccessibilityWidget::create([
            'name' => 'Loader Test',
            'slug' => 'loader-slug',
            'is_active' => true,
        ]);

        $response = $this->get('/api/accessibility/loader/loader-slug.js');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript')
            ->assertSee('loader-slug')
            ->assertSee('api/accessibility');
    }

    public function test_can_get_widget_script(): void
    {
        $response = $this->get('/api/accessibility/widget.js');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript')
            ->assertSee('AgentoomAccessibilityWidget');
    }

    public function test_domain_restriction(): void
    {
        $widget = AccessibilityWidget::create([
            'name' => 'Restricted Widget',
            'slug' => 'restricted-slug',
            'is_active' => true,
            'allowed_domains' => ['example.com'],
        ]);

        // Fail without Origin/Referer
        $response = $this->getJson('/api/accessibility/config/restricted-slug');
        $response->assertStatus(403);

        // Fail with wrong Origin
        $response = $this->withHeaders(['Origin' => 'https://malicious.com'])
            ->getJson('/api/accessibility/config/restricted-slug');
        $response->assertStatus(403);

        // Pass with correct Origin
        $response = $this->withHeaders(['Origin' => 'https://example.com'])
            ->getJson('/api/accessibility/config/restricted-slug');
        $response->assertStatus(200);

        // Pass with subdomain
        $response = $this->withHeaders(['Origin' => 'https://sub.example.com'])
            ->getJson('/api/accessibility/config/restricted-slug');
        $response->assertStatus(200);
    }
}
