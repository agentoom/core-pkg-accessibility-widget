<?php

namespace Agentoom\AccessibilityWidget\Tests\Feature;

use Agentoom\Core\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class AccessibilityWidgetAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_accessible_by_admin(): void
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user);

        $response = $this->get(route('admin.accessibility-widgets.index'));

        $response->assertStatus(200);
    }

    public function test_admin_routes_are_not_accessible_by_guest(): void
    {
        $response = $this->get(route('admin.accessibility-widgets.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
