<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Models\Attendance;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerWageHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@construction.test')->first();
        $contractorUser = User::where('email', 'contractor@construction.test')->first();

        // 1. Create or update contractor profile
        $contractor1 = Contractor::firstOrCreate(
            ['user_id' => $contractorUser?->id],
            [
                'name' => 'Tariq Mehmood',
                'company_name' => 'Tariq Builders & Civil Works',
                'phone' => '0300-9876543',
                'cnic' => '42101-1234567-1',
                'address' => 'Plot 14-C, Commercial Zone, Lahore',
                'is_active' => true,
            ]
        );

        $contractor2 = Contractor::firstOrCreate(
            ['name' => 'Haji Aslam Contractors'],
            [
                'company_name' => 'Aslam & Sons Shuttering Services',
                'phone' => '0321-4567890',
                'cnic' => '35202-7654321-3',
                'address' => 'Main Market, Gulberg III, Lahore',
                'is_active' => true,
            ]
        );

        // 2. Create Projects
        $project1 = Project::firstOrCreate(
            ['name' => 'Gulberg Commercial Heights'],
            [
                'site_name' => 'Plot 42-B, Block D',
                'location' => 'Main Boulevard, Gulberg, Lahore',
                'owner_id' => $owner?->id ?? 1,
                'start_date' => Carbon::now()->subMonths(2)->toDateString(),
                'expected_completion_date' => Carbon::now()->addMonths(6)->toDateString(),
                'status' => ProjectStatus::Active,
                'notes' => 'Ground + 4 Storey Commercial Complex. Grey structure phase.',
            ]
        );

        $project2 = Project::firstOrCreate(
            ['name' => 'DHA Phase 6 Villa Residence'],
            [
                'site_name' => 'House 112, Sector K',
                'location' => 'DHA Phase 6, Lahore',
                'owner_id' => $owner?->id ?? 1,
                'start_date' => Carbon::now()->subMonth()->toDateString(),
                'expected_completion_date' => Carbon::now()->addMonths(4)->toDateString(),
                'status' => ProjectStatus::Active,
                'notes' => '1 Kanal Luxury Residential Villa.',
            ]
        );

        // 3. Attach Contractors with contract amounts
        $project1->contractors()->syncWithoutDetaching([
            $contractor1->id => [
                'contract_amount' => 4500000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::now()->subMonths(2)->toDateString(),
                'agreement_notes' => 'Grey structure labor + civil work agreement up to slab 4.',
            ],
            $contractor2->id => [
                'contract_amount' => 1200000.00,
                'is_primary' => false,
                'assigned_date' => Carbon::now()->subMonths(1)->toDateString(),
                'agreement_notes' => 'Steel shuttering and scaffolding services.',
            ],
        ]);

        $project2->contractors()->syncWithoutDetaching([
            $contractor1->id => [
                'contract_amount' => 2800000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::now()->subMonth()->toDateString(),
                'agreement_notes' => 'Complete residential grey structure contract.',
            ],
        ]);

        // 4. Create Workers
        $workerData = [
            ['name' => 'Muhammad Rasheed', 'phone' => '0301-1112233', 'type' => 'Mason', 'wage' => 2200, 'contractor' => $contractor1, 'project' => $project1],
            ['name' => 'Allah Ditta', 'phone' => '0302-2223344', 'type' => 'Mason', 'wage' => 2000, 'contractor' => $contractor1, 'project' => $project1],
            ['name' => 'Akram Ali', 'phone' => '0303-3334455', 'type' => 'Laborer', 'wage' => 1400, 'contractor' => $contractor1, 'project' => $project1],
            ['name' => 'Sajid Hussain', 'phone' => '0304-4445566', 'type' => 'Laborer', 'wage' => 1400, 'contractor' => $contractor1, 'project' => $project1],
            ['name' => 'Nadeem Abbas', 'phone' => '0305-5556677', 'type' => 'Steel Fixer', 'wage' => 2500, 'contractor' => $contractor2, 'project' => $project1],
            ['name' => 'Bashir Ahmed', 'phone' => '0306-6667788', 'type' => 'Carpenter', 'wage' => 2300, 'contractor' => $contractor2, 'project' => $project1],
            ['name' => 'Farooq Zafar', 'phone' => '0307-7778899', 'type' => 'Electrician', 'wage' => 2200, 'contractor' => $contractor1, 'project' => $project2],
            ['name' => 'Ghulam Nabi', 'phone' => '0308-8889900', 'type' => 'Plumber', 'wage' => 2200, 'contractor' => $contractor1, 'project' => $project2],
        ];

        $workers = [];
        foreach ($workerData as $wd) {
            $w = Worker::firstOrCreate(
                ['name' => $wd['name'], 'contractor_id' => $wd['contractor']->id],
                [
                    'phone' => $wd['phone'],
                    'project_id' => $wd['project']->id,
                    'worker_type' => $wd['type'],
                    'daily_wage' => $wd['wage'],
                    'joining_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'status' => 'active',
                ]
            );

            WorkerWageHistory::firstOrCreate(
                ['worker_id' => $w->id],
                [
                    'daily_wage' => $w->daily_wage,
                    'effective_from' => $w->joining_date ?? Carbon::now()->subMonths(1)->toDateString(),
                    'changed_by' => 1,
                    'reason' => 'Initial registration wage',
                ]
            );

            $workers[] = $w;
        }

        // 5. Create Attendance for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $attDate = Carbon::now()->subDays($i);
            foreach ($workers as $worker) {
                $status = ($i % 5 === 0) ? AttendanceStatus::HalfDay : AttendanceStatus::FullDay;
                if ($i === 2 && $worker->worker_type === 'Steel Fixer') {
                    $status = AttendanceStatus::Absent;
                }

                $wageAtTime = $worker->daily_wage;
                $payable = Attendance::calculatePayable($status, $wageAtTime);

                Attendance::updateOrCreate(
                    [
                        'worker_id' => $worker->id,
                        'project_id' => $worker->project_id ?? $project1->id,
                        'attendance_date' => $attDate->toDateString(),
                    ],
                    [
                        'recorded_by' => $owner?->id ?? 1,
                        'status' => $status,
                        'wage_at_time' => $wageAtTime,
                        'payable_amount' => $payable,
                        'notes' => $status === AttendanceStatus::HalfDay ? 'Left early afternoon' : null,
                    ]
                );
            }
        }

        // 6. Create Contractor Payments
        ContractorPayment::firstOrCreate(
            ['reference' => 'CHQ-778901'],
            [
                'project_id' => $project1->id,
                'contractor_id' => $contractor1->id,
                'recorded_by' => $owner?->id ?? 1,
                'amount' => 1000000.00,
                'payment_date' => Carbon::now()->subMonths(2)->toDateString(),
                'payment_type' => PaymentType::Advance,
                'notes' => 'Initial mobilization advance on signing contract.',
                'is_voided' => false,
            ]
        );

        ContractorPayment::firstOrCreate(
            ['reference' => 'ONLINE-TRX-5521'],
            [
                'project_id' => $project1->id,
                'contractor_id' => $contractor1->id,
                'recorded_by' => $owner?->id ?? 1,
                'amount' => 850000.00,
                'payment_date' => Carbon::now()->subMonth()->toDateString(),
                'payment_type' => PaymentType::Installment,
                'notes' => 'Plinth level foundation slab milestone payment.',
                'is_voided' => false,
            ]
        );

        ContractorPayment::firstOrCreate(
            ['reference' => 'CHQ-889012'],
            [
                'project_id' => $project1->id,
                'contractor_id' => $contractor2->id,
                'recorded_by' => $owner?->id ?? 1,
                'amount' => 400000.00,
                'payment_date' => Carbon::now()->subWeeks(3)->toDateString(),
                'payment_type' => PaymentType::Installment,
                'notes' => 'Ground floor column shuttering milestone.',
                'is_voided' => false,
            ]
        );

        // 7. Create Direct Expenses
        $cementCat = ExpenseCategory::where('slug', 'cement')->first();
        $bricksCat = ExpenseCategory::where('slug', 'bricks')->first();
        $steelCat = ExpenseCategory::where('slug', 'steel')->first();
        $sandCat = ExpenseCategory::where('slug', 'sand-crush')->first();
        $fuelCat = ExpenseCategory::where('slug', 'fuel-generator')->first();
        $waterCat = ExpenseCategory::where('slug', 'water-tankers')->first();

        $expenses = [
            ['project' => $project1, 'cat' => $cementCat, 'amount' => 450000, 'vendor' => 'Bestway Cement Agency', 'desc' => '300 Bags OPC Cement delivered to site', 'days_ago' => 25],
            ['project' => $project1, 'cat' => $steelCat, 'amount' => 890000, 'vendor' => 'Mughal Steel Traders', 'desc' => '8 Ton Grade 60 Deformed Bars', 'days_ago' => 20],
            ['project' => $project1, 'cat' => $bricksCat, 'amount' => 280000, 'vendor' => 'Bismillah Brick Kiln', 'desc' => '20,000 A-Grade Red Bricks', 'days_ago' => 15],
            ['project' => $project1, 'cat' => $sandCat, 'amount' => 95000, 'vendor' => 'Chenab Sand Suppliers', 'desc' => '3 Dumpers Chenab Sand & Margalla Crush', 'days_ago' => 10],
            ['project' => $project1, 'cat' => $fuelCat, 'amount' => 35000, 'vendor' => 'PSO Pump Main Blvd', 'desc' => '120 Liters Diesel for concrete mixer machine', 'days_ago' => 5],
            ['project' => $project1, 'cat' => $waterCat, 'amount' => 18000, 'vendor' => 'Al-Madina Water Supply', 'desc' => '6 Water Tankers for curing', 'days_ago' => 2],
            ['project' => $project2, 'cat' => $cementCat, 'amount' => 220000, 'vendor' => 'Lucky Cement Agency', 'desc' => '150 Bags Cement', 'days_ago' => 8],
            ['project' => $project2, 'cat' => $bricksCat, 'amount' => 140000, 'vendor' => 'Rajput Kiln', 'desc' => '10,000 Bricks', 'days_ago' => 4],
        ];

        foreach ($expenses as $e) {
            if ($e['cat']) {
                Expense::create([
                    'project_id' => $e['project']->id,
                    'expense_category_id' => $e['cat']->id,
                    'recorded_by' => $owner?->id ?? 1,
                    'amount' => $e['amount'],
                    'expense_date' => Carbon::now()->subDays($e['days_ago'])->toDateString(),
                    'vendor' => $e['vendor'],
                    'description' => $e['desc'],
                    'payment_method' => 'Bank Transfer',
                ]);
            }
        }
    }
}
