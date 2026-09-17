<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_admin_dashboard_and_manage_users(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();

        // Access dashboard
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);

        // Create new contractor user via admin
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Contractor User',
            'email' => 'newcontractor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Contractor->value,
            'phone' => '0312-9988776',
            'company_name' => 'Apex Construction Services',
            'contractor_cnic' => '42101-9988776-5',
            'contractor_address' => 'Main Road, Rawalpindi',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'newcontractor@test.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->isContractor());

        // Check contractor profile automatically created
        $contractor = Contractor::where('user_id', $newUser->id)->first();
        $this->assertNotNull($contractor);
        $this->assertEquals('Apex Construction Services', $contractor->company_name);
    }

    public function test_admin_can_manage_expense_categories(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();

        $response = $this->actingAs($admin)->post(route('admin.expense-categories.store'), [
            'name' => 'Plumbing & Sanitary Hardware',
            'type' => 'material',
            'icon' => 'ti-pipe',
            'color' => '#0284c7',
            'sort_order' => 15,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.expense-categories.index'));
        $this->assertDatabaseHas('expense_categories', [
            'name' => 'Plumbing & Sanitary Hardware',
            'type' => 'material',
        ]);
    }

    public function test_contractor_cannot_access_admin_area(): void
    {
        $contractorUser = User::where('role', UserRole::Contractor)->first();

        $response = $this->actingAs($contractorUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_contractor_cannot_access_other_unassigned_projects(): void
    {
        $contractorUser = User::where('role', UserRole::Contractor)->first();

        // Create an unassigned project
        $owner = User::where('role', UserRole::Owner)->first();
        $secretProject = Project::create([
            'name' => 'Private Mansion',
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($contractorUser)->get(route('projects.show', $secretProject));
        $response->assertStatus(403);
    }

    public function test_admin_can_impersonate_owner_and_leave(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $owner = User::where('role', UserRole::Owner)->first();

        // 1. Admin impersonates Owner
        $response = $this->actingAs($admin)->post(route('impersonate.user', $owner));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('impersonator_id', $admin->id);
        $this->assertEquals($owner->id, auth()->id());

        // 2. View dashboard and see impersonation banner
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Impersonating');
        $dashResponse->assertSee($owner->name);

        // 3. Impersonated user cannot access admin area
        $this->get(route('admin.dashboard'))->assertStatus(403);

        // 4. Leave impersonation
        $leaveResponse = $this->post(route('impersonate.leave'));
        $leaveResponse->assertRedirect(route('admin.users.index'));
        $leaveResponse->assertSessionMissing('impersonator_id');
        $this->assertEquals($admin->id, auth()->id());
    }

    public function test_admin_can_impersonate_contractor_user(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $contractorUser = User::where('role', UserRole::Contractor)->first();

        $response = $this->actingAs($admin)->post(route('impersonate.user', $contractorUser));
        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($contractorUser->id, auth()->id());
    }

    public function test_admin_can_impersonate_contractor_profile_directly(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();

        // Create standalone contractor with no user
        $contractor = Contractor::create([
            'name' => 'Standalone Builder',
            'phone' => '0300-1122334',
            'is_active' => true,
        ]);

        $this->assertNull($contractor->user_id);

        $response = $this->actingAs($admin)->post(route('impersonate.contractor', $contractor));
        $response->assertRedirect(route('dashboard'));

        $contractor->refresh();
        $this->assertNotNull($contractor->user_id);
        $this->assertEquals($contractor->user_id, auth()->id());
        $this->assertEquals('Standalone Builder', auth()->user()->name);
        $this->assertTrue(auth()->user()->isContractor());
    }

    public function test_non_admin_cannot_impersonate(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $contractorUser = User::where('role', UserRole::Contractor)->first();

        $response = $this->actingAs($owner)->post(route('impersonate.user', $contractorUser));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $admin1 = User::where('role', UserRole::Admin)->first();
        $admin2 = User::create([
            'name' => 'Second Admin',
            'email' => 'admin2@test.com',
            'password' => 'secret123',
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        $admin2->assignRole(UserRole::Admin->value);

        $response = $this->actingAs($admin1)->post(route('impersonate.user', $admin2));
        $response->assertSessionHas('error');
        $this->assertEquals($admin1->id, auth()->id());
    }
}
