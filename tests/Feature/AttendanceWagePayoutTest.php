<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceWagePayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(DemoDataSeeder::class);
    }

    public function test_user_can_view_attendance_history_with_work_week_preset(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();

        $response = $this->actingAs($owner)->get(route('attendance.history', [
            'preset' => 'this_work_week',
            'payment_status' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Attendance History &amp; Wage Settlement', false);
        $response->assertSee('This Week (Sat – Thu)');
        $response->assertSee('Worker Wage Collection');
    }

    public function test_user_can_pay_all_wages_via_direct_pay(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::create([
            'name' => 'Direct Wage Test Project',
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Active,
            'start_date' => now()->toDateString(),
        ]);
        $contractor = Contractor::first();

        $worker = Worker::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'name' => 'Direct Test Worker',
            'daily_wage' => 2500,
            'status' => 'active',
            'worker_type' => 'mason',
        ]);

        $testDate = Carbon::today()->toDateString();

        $attendance = Attendance::create([
            'project_id' => $project->id,
            'worker_id' => $worker->id,
            'attendance_date' => $testDate,
            'status' => AttendanceStatus::FullDay,
            'wage_at_time' => 2500,
            'payable_amount' => 2500,
            'is_paid' => false,
            'recorded_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->post(route('attendance.pay-all'), [
            'project_id' => $project->id,
            'from_date' => $testDate,
            'to_date' => $testDate,
            'payment_destination' => 'direct_pay',
            'payment_method' => 'cash',
            'payment_date' => $testDate,
            'notes' => 'Weekly payout test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'is_paid' => true,
            'payment_method' => 'direct_pay:cash',
        ]);

        $this->assertDatabaseHas('expenses', [
            'project_id' => $project->id,
            'amount' => 2500,
            'payment_method' => 'Cash',
        ]);
    }

    public function test_user_can_pay_all_wages_via_contractor_pay(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::create([
            'name' => 'Contractor Wage Test Project',
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Active,
            'start_date' => now()->toDateString(),
        ]);
        $contractor = Contractor::first();

        // Attach contractor to project
        $project->contractors()->attach($contractor->id, ['contract_amount' => 500000]);

        $worker = Worker::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'name' => 'Contractor Test Worker',
            'daily_wage' => 3000,
            'status' => 'active',
            'worker_type' => 'carpenter',
        ]);

        $testDate = Carbon::today()->toDateString();

        $attendance = Attendance::create([
            'project_id' => $project->id,
            'worker_id' => $worker->id,
            'attendance_date' => $testDate,
            'status' => AttendanceStatus::FullDay,
            'wage_at_time' => 3000,
            'payable_amount' => 3000,
            'is_paid' => false,
            'recorded_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->post(route('attendance.pay-all'), [
            'project_id' => $project->id,
            'from_date' => $testDate,
            'to_date' => $testDate,
            'payment_destination' => 'contractor_pay',
            'payment_method' => 'bank_transfer',
            'payment_date' => $testDate,
            'notes' => 'Contractor disbursement test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'is_paid' => true,
            'payment_method' => 'contractor_pay:bank_transfer',
        ]);

        $this->assertDatabaseHas('contractor_payments', [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => 3000,
            'payment_type' => PaymentType::LaborWage->value,
        ]);
    }

    public function test_user_can_view_contractor_payment_voucher(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $payment = ContractorPayment::first();

        $response = $this->actingAs($owner)->get(route('contractor-payments.show', $payment));

        $response->assertStatus(200);
        $response->assertSee('Payment Voucher #'.str_pad($payment->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee($payment->contractor->name);
        $response->assertSee('Total Paid to Date');
        $response->assertSee('Agreed Contract Value');
    }

    public function test_user_can_directly_toggle_attendance_payment_status(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::create([
            'name' => 'Single Status Toggle Project',
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Active,
            'start_date' => now()->toDateString(),
        ]);
        $contractor = Contractor::first();

        $worker = Worker::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'name' => 'Single Status Worker',
            'daily_wage' => 2000,
            'status' => 'active',
            'worker_type' => 'mason',
        ]);

        $attendance = Attendance::create([
            'project_id' => $project->id,
            'worker_id' => $worker->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::FullDay,
            'wage_at_time' => 2000,
            'payable_amount' => 2000,
            'is_paid' => false,
            'recorded_by' => $owner->id,
        ]);

        // Toggle to Paid
        $response = $this->actingAs($owner)->patch(route('attendance.toggle-payment-status', $attendance), [
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('active_tab', 'shiftsPane');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'is_paid' => true,
        ]);

        // Toggle back to Unpaid
        $response2 = $this->actingAs($owner)->patch(route('attendance.toggle-payment-status', $attendance), [
            'status' => 'unpaid',
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('active_tab', 'shiftsPane');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'is_paid' => false,
        ]);
    }

    public function test_user_can_bulk_update_attendance_payment_status(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::create([
            'name' => 'Bulk Status Project',
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Active,
            'start_date' => now()->toDateString(),
        ]);
        $contractor = Contractor::first();

        $worker = Worker::create([
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'name' => 'Bulk Status Worker',
            'daily_wage' => 2200,
            'status' => 'active',
            'worker_type' => 'mason',
        ]);

        $att1 = Attendance::create([
            'project_id' => $project->id,
            'worker_id' => $worker->id,
            'attendance_date' => now()->subDay()->toDateString(),
            'status' => AttendanceStatus::FullDay,
            'wage_at_time' => 2200,
            'payable_amount' => 2200,
            'is_paid' => false,
            'recorded_by' => $owner->id,
        ]);

        $att2 = Attendance::create([
            'project_id' => $project->id,
            'worker_id' => $worker->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::FullDay,
            'wage_at_time' => 2200,
            'payable_amount' => 2200,
            'is_paid' => false,
            'recorded_by' => $owner->id,
        ]);

        // Bulk Mark as Paid
        $response = $this->actingAs($owner)->post(route('attendance.bulk-payment-status'), [
            'attendance_ids' => [$att1->id, $att2->id],
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('active_tab', 'shiftsPane');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', ['id' => $att1->id, 'is_paid' => true]);
        $this->assertDatabaseHas('attendance', ['id' => $att2->id, 'is_paid' => true]);

        // Bulk Mark as Unpaid
        $response2 = $this->actingAs($owner)->post(route('attendance.bulk-payment-status'), [
            'attendance_ids' => [$att1->id, $att2->id],
            'status' => 'unpaid',
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('active_tab', 'shiftsPane');

        $this->assertDatabaseHas('attendance', ['id' => $att1->id, 'is_paid' => false]);
        $this->assertDatabaseHas('attendance', ['id' => $att2->id, 'is_paid' => false]);
    }

    public function test_unauthorized_user_cannot_bulk_or_toggle_payment_status(): void
    {
        $nonOwnerUser = User::where('role', '!=', UserRole::Owner)
            ->where('role', '!=', UserRole::Admin)
            ->first();

        $attendance = Attendance::first();

        if ($nonOwnerUser && $attendance) {
            $toggleResponse = $this->actingAs($nonOwnerUser)->patch(route('attendance.toggle-payment-status', $attendance), [
                'status' => 'paid',
            ]);
            $toggleResponse->assertStatus(403);

            $bulkResponse = $this->actingAs($nonOwnerUser)->post(route('attendance.bulk-payment-status'), [
                'attendance_ids' => [$attendance->id],
                'status' => 'paid',
            ]);
            $bulkResponse->assertStatus(403);
        }
    }
}
