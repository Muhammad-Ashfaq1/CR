<?php

namespace Tests\Feature;

use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractorPaymentEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(DemoDataSeeder::class);
    }

    public function test_owner_who_created_payment_can_access_edit_and_update(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::where('owner_id', $owner->id)->first();
        $contractor = Contractor::first();

        $payment = ContractorPayment::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 75000,
            'payment_date' => Carbon::parse('2026-03-01'),
            'payment_type' => PaymentType::Installment,
            'reference' => 'ORIG-REF-001',
            'notes' => 'Original payment note',
            'recorded_by' => $owner->id,
            'is_voided' => false,
        ]);

        // 1. Can view edit view
        $editResponse = $this->actingAs($owner)->get(route('contractor-payments.edit', $payment));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('ORIG-REF-001');

        // 2. Can update payment
        $updateResponse = $this->actingAs($owner)->put(route('contractor-payments.update', $payment), [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 95000,
            'payment_date' => '2026-03-05',
            'payment_type' => PaymentType::Advance->value,
            'reference' => 'UPDATED-REF-002',
            'notes' => 'Updated payment description',
        ]);

        $updateResponse->assertRedirect(route('contractor-payments.show', $payment));
        $updateResponse->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(95000, (float) $payment->amount);
        $this->assertEquals('2026-03-05', $payment->payment_date->format('Y-m-d'));
        $this->assertEquals(PaymentType::Advance, $payment->payment_type);
        $this->assertEquals('UPDATED-REF-002', $payment->reference);
        $this->assertEquals('Updated payment description', $payment->notes);
    }

    public function test_different_owner_cannot_edit_or_update_payment(): void
    {
        $owner1 = User::where('role', UserRole::Owner)->first();
        $project = Project::where('owner_id', $owner1->id)->first();
        $contractor = Contractor::first();

        // Create second owner
        $owner2 = User::create([
            'name' => 'Second Owner',
            'email' => 'second.owner@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Owner,
        ]);
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $owner2->assignRole($ownerRole);

        $payment = ContractorPayment::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 50000,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentType::Installment,
            'recorded_by' => $owner1->id,
            'is_voided' => false,
        ]);

        // Attempt edit as second owner
        $editResponse = $this->actingAs($owner2)->get(route('contractor-payments.edit', $payment));
        $editResponse->assertStatus(403);

        // Attempt update as second owner
        $updateResponse = $this->actingAs($owner2)->put(route('contractor-payments.update', $payment), [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 60000,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_type' => PaymentType::Installment->value,
        ]);
        $updateResponse->assertStatus(403);

        $payment->refresh();
        $this->assertEquals(50000, (float) $payment->amount);
    }

    public function test_contractor_cannot_edit_or_update_payment(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::where('owner_id', $owner->id)->first();
        $contractor = Contractor::first();

        $contractorUser = User::where('role', UserRole::Contractor)->first();

        $payment = ContractorPayment::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 30000,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentType::Installment,
            'recorded_by' => $owner->id,
            'is_voided' => false,
        ]);

        $editResponse = $this->actingAs($contractorUser)->get(route('contractor-payments.edit', $payment));
        $editResponse->assertStatus(403);

        $updateResponse = $this->actingAs($contractorUser)->put(route('contractor-payments.update', $payment), [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 99999,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_type' => PaymentType::Installment->value,
        ]);
        $updateResponse->assertStatus(403);
    }

    public function test_voided_payment_cannot_be_edited_or_updated(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::where('owner_id', $owner->id)->first();
        $contractor = Contractor::first();

        $payment = ContractorPayment::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 20000,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentType::Installment,
            'recorded_by' => $owner->id,
            'is_voided' => true,
        ]);

        $editResponse = $this->actingAs($owner)->get(route('contractor-payments.edit', $payment));
        $editResponse->assertStatus(403);

        $updateResponse = $this->actingAs($owner)->put(route('contractor-payments.update', $payment), [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 25000,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_type' => PaymentType::Installment->value,
        ]);
        $updateResponse->assertStatus(403);
    }

    public function test_owner_cannot_reassign_payment_to_project_they_do_not_own(): void
    {
        $owner1 = User::where('role', UserRole::Owner)->first();
        $project1 = Project::where('owner_id', $owner1->id)->first();
        $contractor = Contractor::first();

        // Another owner and their project
        $owner2 = User::create([
            'name' => 'Owner Two',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Owner,
        ]);
        $project2 = Project::create([
            'name' => 'Owner 2 Project',
            'owner_id' => $owner2->id,
            'status' => ProjectStatus::Active,
            'start_date' => Carbon::now(),
        ]);

        $payment = ContractorPayment::create([
            'project_id' => $project1->id,
            'contractor_id' => $contractor->id,
            'amount' => 45000,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentType::Installment,
            'recorded_by' => $owner1->id,
            'is_voided' => false,
        ]);

        $response = $this->actingAs($owner1)->put(route('contractor-payments.update', $payment), [
            'project_id' => $project2->id, // Not owned by owner1
            'contractor_id' => $contractor->id,
            'amount' => 45000,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_type' => PaymentType::Installment->value,
        ]);

        $response->assertStatus(403);
    }
}
