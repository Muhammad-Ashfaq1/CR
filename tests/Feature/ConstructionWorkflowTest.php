<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Contractor;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConstructionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(DemoDataSeeder::class);
    }

    public function test_owner_can_view_dashboard(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('System Administration');
    }

    public function test_contractor_can_view_dashboard(): void
    {
        $contractor = User::where('role', UserRole::Contractor)->first();

        $response = $this->actingAs($contractor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_owner_can_create_project_and_assign_contractor(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $contractor = Contractor::first();

        $response = $this->actingAs($owner)->post(route('projects.store'), [
            'name' => 'Alpha Heights Plaza',
            'site_name' => 'Plot 55',
            'location' => 'Islamabad',
            'status' => ProjectStatus::Active->value,
            'start_date' => '2026-01-01',
            'expected_completion_date' => '2026-12-31',
            'contractor_id' => $contractor->id,
            'contract_amount' => 3000000,
            'agreement_notes' => 'Grey structure contract',
        ]);

        $project = Project::where('name', 'Alpha Heights Plaza')->first();
        $this->assertNotNull($project);
        $this->assertEquals(3000000, $project->totalContractAmount());
        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_contractor_payment_updates_balance_correctly(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::first();
        $contractor = $project->contractors->first();

        $initialPaid = $contractor->totalPaidForProject($project->id);

        $paymentAmount = 50000;
        $response = $this->actingAs($owner)->post(route('contractor-payments.store'), [
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'amount' => $paymentAmount,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_type' => PaymentType::Installment->value,
            'reference' => 'TEST-CHQ-101',
        ]);

        $response->assertRedirect();
        $newPaid = $contractor->totalPaidForProject($project->id);
        $this->assertEquals($initialPaid + $paymentAmount, $newPaid);
    }

    public function test_attendance_multipliers_calculate_accurate_payable_wages(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $worker = Worker::first();
        $project = Project::first();
        $date = Carbon::now()->addDay()->toDateString();

        $dailyWage = (float) $worker->daily_wage;

        // Test Full Day (100%)
        $response = $this->actingAs($owner)->post(route('attendance.store-daily'), [
            'project_id' => $project->id,
            'attendance_date' => $date,
            'attendance' => [
                $worker->id => [
                    'status' => AttendanceStatus::FullDay->value,
                    'notes' => 'Full day shift',
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $attendance = Attendance::where('worker_id', $worker->id)->whereDate('attendance_date', $date)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals($dailyWage, (float) $attendance->payable_amount);

        // Test Half Day (50%)
        $response = $this->actingAs($owner)->post(route('attendance.store-daily'), [
            'project_id' => $project->id,
            'attendance_date' => $date,
            'attendance' => [
                $worker->id => [
                    'status' => AttendanceStatus::HalfDay->value,
                    'notes' => 'Half day shift',
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $attendance = Attendance::where('worker_id', $worker->id)->whereDate('attendance_date', $date)->first();
        $this->assertEquals(round($dailyWage * 0.5, 2), (float) $attendance->payable_amount);

        // Test Absent (0%)
        $response = $this->actingAs($owner)->post(route('attendance.store-daily'), [
            'project_id' => $project->id,
            'attendance_date' => $date,
            'attendance' => [
                $worker->id => [
                    'status' => AttendanceStatus::Absent->value,
                ],
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $attendance = Attendance::where('worker_id', $worker->id)->whereDate('attendance_date', $date)->first();
        $this->assertEquals(0.00, (float) $attendance->payable_amount);
    }

    public function test_worker_wage_revision_preserves_wage_history(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $worker = Worker::first();

        $oldWage = (float) $worker->daily_wage;
        $newWage = $oldWage + 500;

        $response = $this->actingAs($owner)->put(route('workers.update', $worker), [
            'contractor_id' => $worker->contractor_id,
            'project_id' => $worker->project_id,
            'name' => $worker->name,
            'worker_type' => $worker->worker_type,
            'daily_wage' => $newWage,
            'wage_effective_date' => Carbon::now()->toDateString(),
            'wage_change_reason' => 'Annual Performance Increment',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('workers.show', $worker));
        $this->assertEquals($newWage, (float) $worker->fresh()->daily_wage);
        $this->assertDatabaseHas('worker_wage_history', [
            'worker_id' => $worker->id,
            'daily_wage' => $newWage,
            'reason' => 'Annual Performance Increment',
        ]);
    }

    public function test_expense_can_be_recorded_with_category(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();
        $project = Project::first();
        $category = ExpenseCategory::first();

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'project_id' => $project->id,
            'expense_category_id' => $category->id,
            'amount' => 45000,
            'expense_date' => Carbon::now()->toDateString(),
            'vendor' => 'Hardware Store',
            'description' => 'Nails and binding wire',
            'payment_method' => 'Cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'project_id' => $project->id,
            'expense_category_id' => $category->id,
            'amount' => 45000,
            'vendor' => 'Hardware Store',
        ]);
    }

    public function test_reports_pages_render_successfully(): void
    {
        $owner = User::where('role', UserRole::Owner)->first();

        $this->actingAs($owner)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($owner)->get(route('reports.expenses'))->assertStatus(200);
        $this->actingAs($owner)->get(route('reports.wages'))->assertStatus(200);
        $this->actingAs($owner)->get(route('reports.contractor-ledger'))->assertStatus(200);
        $this->actingAs($owner)->get(route('reports.project-summary'))->assertStatus(200);
    }
}
