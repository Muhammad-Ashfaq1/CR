<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
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
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run complete demo & testing data seeders covering all application models.
     */
    public function run(): void
    {
        // -------------------------------------------------------------
        // 1. USERS & ROLES
        // -------------------------------------------------------------
        $owner = User::firstOrCreate(
            ['email' => 'owner@construction.test'],
            [
                'name' => 'Project Owner',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'phone' => '0300-1122334',
                'is_active' => true,
            ]
        );
        $owner->syncRoles([UserRole::Owner->value]);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@construction.test'],
            [
                'name' => 'Hammad Malik (Partner)',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'phone' => '0300-5566778',
                'is_active' => true,
            ]
        );
        $owner2->syncRoles([UserRole::Owner->value]);

        $contractorUser = User::firstOrCreate(
            ['email' => 'contractor@construction.test'],
            [
                'name' => 'Tariq Mehmood',
                'password' => Hash::make('password'),
                'role' => UserRole::Contractor,
                'phone' => '0300-9876543',
                'is_active' => true,
            ]
        );
        $contractorUser->syncRoles([UserRole::Contractor->value]);

        $contractor2User = User::firstOrCreate(
            ['email' => 'contractor2@construction.test'],
            [
                'name' => 'Haji Aslam',
                'password' => Hash::make('password'),
                'role' => UserRole::Contractor,
                'phone' => '0321-4567890',
                'is_active' => true,
            ]
        );
        $contractor2User->syncRoles([UserRole::Contractor->value]);

        $contractor3User = User::firstOrCreate(
            ['email' => 'contractor3@construction.test'],
            [
                'name' => 'Abdul Rehman',
                'password' => Hash::make('password'),
                'role' => UserRole::Contractor,
                'phone' => '0333-5566778',
                'is_active' => true,
            ]
        );
        $contractor3User->syncRoles([UserRole::Contractor->value]);

        // -------------------------------------------------------------
        // 2. CONTRACTOR PROFILES
        // -------------------------------------------------------------
        $contractor1 = Contractor::updateOrCreate(
            ['user_id' => $contractorUser->id],
            [
                'name' => 'Tariq Mehmood',
                'company_name' => 'Tariq Builders & Civil Works',
                'phone' => '0300-9876543',
                'cnic' => '42101-1234567-1',
                'address' => 'Plot 14-C, Commercial Zone, Gulberg III, Lahore',
                'is_active' => true,
            ]
        );

        $contractor2 = Contractor::updateOrCreate(
            ['user_id' => $contractor2User->id],
            [
                'name' => 'Haji Aslam',
                'company_name' => 'Aslam & Sons Shuttering Services',
                'phone' => '0321-4567890',
                'cnic' => '35202-7654321-3',
                'address' => 'Main Market, Gulberg III, Lahore',
                'is_active' => true,
            ]
        );

        $contractor3 = Contractor::updateOrCreate(
            ['user_id' => $contractor3User->id],
            [
                'name' => 'Abdul Rehman',
                'company_name' => 'Rehman MEP & Electrical Works',
                'phone' => '0333-5566778',
                'cnic' => '35201-9988112-5',
                'address' => 'Shop 8, Electrical Plaza, Hall Road, Lahore',
                'is_active' => true,
            ]
        );

        $contractor4 = Contractor::firstOrCreate(
            ['name' => 'Mian Kashif (Falcon Finishing)'],
            [
                'company_name' => 'Falcon Finishing, Paints & Tiles',
                'phone' => '0304-1239874',
                'cnic' => '35202-3344556-7',
                'address' => 'Ferozepur Road Tile Market, Lahore',
                'is_active' => true,
            ]
        );

        $contractorInactive = Contractor::firstOrCreate(
            ['name' => 'Al-Madina Excavation & Earthworks'],
            [
                'company_name' => 'Al-Madina Heavy Machinery & Demolition',
                'phone' => '0311-4455667',
                'cnic' => '35201-8877665-9',
                'address' => 'Multan Road Bypass, Lahore',
                'is_active' => false,
            ]
        );

        // -------------------------------------------------------------
        // 3. PROJECTS (Covering multiple project lifecycle statuses)
        // -------------------------------------------------------------
        $project1 = Project::firstOrCreate(
            ['name' => 'Gulberg Commercial Heights'],
            [
                'site_name' => 'Plot 42-B, Block D',
                'location' => 'Main Boulevard, Gulberg, Lahore',
                'owner_id' => $owner->id,
                'start_date' => Carbon::today()->subMonths(2)->toDateString(),
                'expected_completion_date' => Carbon::today()->addMonths(6)->toDateString(),
                'status' => ProjectStatus::Active,
                'notes' => 'Ground + 4 Storey Commercial Complex. Grey structure phase.',
            ]
        );

        $project2 = Project::firstOrCreate(
            ['name' => 'DHA Phase 6 Villa Residence'],
            [
                'site_name' => 'House 112, Sector K',
                'location' => 'DHA Phase 6, Lahore',
                'owner_id' => $owner->id,
                'start_date' => Carbon::today()->subMonth()->toDateString(),
                'expected_completion_date' => Carbon::today()->addMonths(4)->toDateString(),
                'status' => ProjectStatus::Active,
                'notes' => '1 Kanal Luxury Residential Villa. Finishing and MEP phase.',
            ]
        );

        $project3 = Project::firstOrCreate(
            ['name' => 'Bahria Town Sector C Commercial Plaza'],
            [
                'site_name' => 'Plaza 88, Commercial Zone',
                'location' => 'Sector C, Bahria Town, Lahore',
                'owner_id' => $owner->id,
                'start_date' => Carbon::today()->addDays(10)->toDateString(),
                'expected_completion_date' => Carbon::today()->addMonths(12)->toDateString(),
                'status' => ProjectStatus::Planning,
                'notes' => 'Architectural drawings finalized; awaiting LDA structural vetting.',
            ]
        );

        $project4 = Project::firstOrCreate(
            ['name' => 'Lake City Model Bungalow'],
            [
                'site_name' => 'Villa 24, Sector M-1',
                'location' => 'Lake City, Raiwind Road, Lahore',
                'owner_id' => $owner->id,
                'start_date' => Carbon::today()->subMonths(6)->toDateString(),
                'expected_completion_date' => Carbon::today()->subDays(5)->toDateString(),
                'status' => ProjectStatus::Completed,
                'notes' => 'Turnkey luxury residence completed and client handed over keys.',
            ]
        );

        $project5 = Project::firstOrCreate(
            ['name' => 'Pine View Cottages Murree'],
            [
                'site_name' => 'Plot 17, Expressway View',
                'location' => 'Murree Hills, Rawalpindi',
                'owner_id' => $owner2->id,
                'start_date' => Carbon::today()->subMonths(3)->toDateString(),
                'expected_completion_date' => Carbon::today()->addMonths(8)->toDateString(),
                'status' => ProjectStatus::OnHold,
                'notes' => 'Excavation and retaining wall completed. Construction on hold for severe winter conditions.',
            ]
        );

        // -------------------------------------------------------------
        // 4. PROJECT CONTRACTORS (Pivot table: project_contractor)
        // -------------------------------------------------------------
        $project1->contractors()->syncWithoutDetaching([
            $contractor1->id => [
                'contract_amount' => 4500000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::today()->subMonths(2)->toDateString(),
                'agreement_notes' => 'Grey structure civil work up to 4th floor.',
            ],
            $contractor2->id => [
                'contract_amount' => 1200000.00,
                'is_primary' => false,
                'assigned_date' => Carbon::today()->subMonths(1)->toDateString(),
                'agreement_notes' => 'Steel shuttering and scaffolding supply & fixing.',
            ],
            $contractor3->id => [
                'contract_amount' => 850000.00,
                'is_primary' => false,
                'assigned_date' => Carbon::today()->subWeeks(3)->toDateString(),
                'agreement_notes' => 'Electrical conduit laying and sanitation plumbing rough-in.',
            ],
        ]);

        $project2->contractors()->syncWithoutDetaching([
            $contractor1->id => [
                'contract_amount' => 2800000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::today()->subMonth()->toDateString(),
                'agreement_notes' => 'Complete residential villa grey structure contract.',
            ],
            $contractor4->id => [
                'contract_amount' => 950000.00,
                'is_primary' => false,
                'assigned_date' => Carbon::today()->subWeeks(2)->toDateString(),
                'agreement_notes' => 'Floor tiling, granite steps, and premium acrylic wall painting.',
            ],
        ]);

        $project3->contractors()->syncWithoutDetaching([
            $contractor1->id => [
                'contract_amount' => 3500000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::today()->toDateString(),
                'agreement_notes' => 'Advance contractor reservation for Plaza groundwork.',
            ],
        ]);

        $project4->contractors()->syncWithoutDetaching([
            $contractor2->id => [
                'contract_amount' => 1500000.00,
                'is_primary' => true,
                'assigned_date' => Carbon::today()->subMonths(6)->toDateString(),
                'agreement_notes' => 'Shuttering and concrete superstructure contract (Fulfilled).',
            ],
        ]);

        // -------------------------------------------------------------
        // 5. WORKERS & 6. WAGE HISTORIES
        // -------------------------------------------------------------
        $workerDefinitions = [
            // Project 1 workforce
            ['name' => 'Muhammad Rasheed', 'phone' => '0301-1112233', 'type' => 'Mason',        'wage' => 2200, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Allah Ditta',      'phone' => '0302-2223344', 'type' => 'Mason',        'wage' => 2000, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Zahid Khan',       'phone' => '0303-1212121', 'type' => 'Mason',        'wage' => 2400, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Akram Ali',        'phone' => '0303-3334455', 'type' => 'Laborer',      'wage' => 1400, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Sajid Hussain',    'phone' => '0304-4445566', 'type' => 'Laborer',      'wage' => 1400, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Bilal Ahmed',      'phone' => '0305-1122334', 'type' => 'Laborer',      'wage' => 1500, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Nadeem Abbas',     'phone' => '0305-5556677', 'type' => 'Steel Fixer',  'wage' => 2500, 'contractor' => $contractor2, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Bashir Ahmed',     'phone' => '0306-6667788', 'type' => 'Carpenter',    'wage' => 2300, 'contractor' => $contractor2, 'project' => $project1, 'status' => 'active'],
            ['name' => 'Shakeel Anjum',    'phone' => '0307-3344556', 'type' => 'Electrician',  'wage' => 2300, 'contractor' => $contractor3, 'project' => $project1, 'status' => 'active'],

            // Project 2 workforce
            ['name' => 'Farooq Zafar',     'phone' => '0307-7778899', 'type' => 'Electrician',  'wage' => 2200, 'contractor' => $contractor1, 'project' => $project2, 'status' => 'active'],
            ['name' => 'Ghulam Nabi',      'phone' => '0308-8889900', 'type' => 'Plumber',      'wage' => 2200, 'contractor' => $contractor1, 'project' => $project2, 'status' => 'active'],
            ['name' => 'Mian Nasir',       'phone' => '0309-9988776', 'type' => 'Painter',      'wage' => 2100, 'contractor' => $contractor4, 'project' => $project2, 'status' => 'active'],
            ['name' => 'Baba Rehmat',      'phone' => '0310-1234567', 'type' => 'Site Helper',  'wage' => 1500, 'contractor' => $contractor1, 'project' => $project2, 'status' => 'active'],

            // Inactive worker for testing filters
            ['name' => 'Waqas Munir',      'phone' => '0312-7766554', 'type' => 'Laborer',      'wage' => 1400, 'contractor' => $contractor1, 'project' => $project1, 'status' => 'inactive'],
        ];

        $workers = [];
        foreach ($workerDefinitions as $wd) {
            $w = Worker::firstOrCreate(
                [
                    'name' => $wd['name'],
                    'contractor_id' => $wd['contractor']->id,
                ],
                [
                    'phone' => $wd['phone'],
                    'project_id' => $wd['project']->id,
                    'worker_type' => $wd['type'],
                    'daily_wage' => $wd['wage'],
                    'joining_date' => Carbon::today()->subMonths(1)->toDateString(),
                    'status' => $wd['status'],
                    'notes' => $wd['status'] === 'inactive' ? 'Left site after excavation completion' : 'Registered active crew member',
                ]
            );

            // Initial wage history record
            WorkerWageHistory::firstOrCreate(
                [
                    'worker_id' => $w->id,
                    'effective_from' => $w->joining_date ?? Carbon::today()->subMonths(1)->toDateString(),
                ],
                [
                    'daily_wage' => $w->daily_wage,
                    'changed_by' => $owner->id,
                    'reason' => 'Initial registration daily wage',
                ]
            );

            // Realistic wage progression history: Allah Ditta started at PKR 1,800 before reaching current PKR 2,000
            if ($w->name === 'Allah Ditta') {
                WorkerWageHistory::firstOrCreate(
                    [
                        'worker_id' => $w->id,
                        'effective_from' => Carbon::today()->subMonths(2)->toDateString(),
                    ],
                    [
                        'daily_wage' => 1800,
                        'changed_by' => $owner->id,
                        'reason' => 'Probationary recruitment daily wage',
                    ]
                );
            }

            if ($w->status === 'active') {
                $workers[] = $w;
            }
        }

        // -------------------------------------------------------------
        // 7. ATTENDANCE RECORDS (Past 14 days, including Today)
        // -------------------------------------------------------------
        // Days 13 to 0 (subDays(0) is today)
        for ($i = 13; $i >= 0; $i--) {
            $attDate = Carbon::today()->subDays($i);
            $isFriday = $attDate->isFriday();

            foreach ($workers as $workerIndex => $worker) {
                // Determine shift status
                if ($isFriday) {
                    // In Pakistan construction, Fridays are off/half days
                    if ($workerIndex % 2 === 0) {
                        $status = AttendanceStatus::Leave;
                        $notes = 'Weekly Friday off/rest';
                    } else {
                        $status = AttendanceStatus::HalfDay;
                        $notes = 'Friday pre-prayer short shift';
                    }
                } elseif ($i === 3 && $workerIndex === 1) {
                    $status = AttendanceStatus::Absent;
                    $notes = 'Unannounced absence';
                } elseif ($i === 8 && $workerIndex === 4) {
                    $status = AttendanceStatus::Absent;
                    $notes = 'Sick leave - flu';
                } elseif ($i % 6 === 0 && $workerIndex % 3 === 0) {
                    $status = AttendanceStatus::HalfDay;
                    $notes = 'Half day shift (materials delivery delay)';
                } else {
                    $status = AttendanceStatus::FullDay;
                    $notes = null;
                }

                $wageAtTime = (float) $worker->daily_wage;
                $payable = Attendance::calculatePayable($status, $wageAtTime);

                // Set payment tracking:
                // Previous work week ($i >= 7): mark as settled/paid
                // Current work week ($i < 7): mostly unpaid, with 1-2 marked paid to test mixed status
                $isPaid = false;
                $paidAt = null;
                $paymentMethod = null;
                $paymentRef = null;

                if ($payable > 0) {
                    if ($i >= 7) {
                        $isPaid = true;
                        $paidAt = Carbon::today()->subDays(6);
                        $paymentMethod = ($workerIndex % 2 === 0) ? 'direct_pay:cash' : 'contractor_pay:bank_transfer';
                        $paymentRef = ($workerIndex % 2 === 0) ? 'EXP-#0012' : 'VOUCHER-#00001';
                    } elseif ($i === 5 && $workerIndex === 0) {
                        $isPaid = true;
                        $paidAt = Carbon::today()->subDays(4);
                        $paymentMethod = 'direct_pay:cash';
                        $paymentRef = 'MANUAL-SETTLE-01';
                    }
                }

                Attendance::updateOrCreate(
                    [
                        'worker_id' => $worker->id,
                        'project_id' => $worker->project_id ?? $project1->id,
                        'attendance_date' => $attDate->toDateString(),
                    ],
                    [
                        'recorded_by' => $owner->id,
                        'status' => $status,
                        'wage_at_time' => $wageAtTime,
                        'payable_amount' => $payable,
                        'is_paid' => $isPaid,
                        'paid_at' => $paidAt,
                        'payment_method' => $paymentMethod,
                        'payment_reference' => $paymentRef,
                        'notes' => $notes,
                    ]
                );
            }
        }

        // -------------------------------------------------------------
        // 8. CONTRACTOR PAYMENTS
        // -------------------------------------------------------------
        $payments = [
            [
                'project' => $project1,
                'contractor' => $contractor1,
                'amount' => 1000000.00,
                'days_ago' => 60,
                'type' => PaymentType::Advance,
                'ref' => 'CHQ-778901',
                'notes' => 'Initial mobilization advance on signing contract.',
                'voided' => false,
            ],
            [
                'project' => $project1,
                'contractor' => $contractor1,
                'amount' => 850000.00,
                'days_ago' => 30,
                'type' => PaymentType::Installment,
                'ref' => 'ONLINE-TRX-5521',
                'notes' => 'Plinth level foundation slab milestone payment.',
                'voided' => false,
            ],
            [
                'project' => $project1,
                'contractor' => $contractor2,
                'amount' => 400000.00,
                'days_ago' => 21,
                'type' => PaymentType::Installment,
                'ref' => 'CHQ-889012',
                'notes' => 'Ground floor column shuttering milestone.',
                'voided' => false,
            ],
            [
                'project' => $project1,
                'contractor' => $contractor3,
                'amount' => 250000.00,
                'days_ago' => 14,
                'type' => PaymentType::Installment,
                'ref' => 'CHQ-991204',
                'notes' => 'Basement electrical conduit rough-in milestone.',
                'voided' => false,
            ],
            [
                'project' => $project2,
                'contractor' => $contractor1,
                'amount' => 600000.00,
                'days_ago' => 20,
                'type' => PaymentType::Installment,
                'ref' => 'ONLINE-TRX-7734',
                'notes' => 'Ground floor roof slab casting milestone.',
                'voided' => false,
            ],
            [
                'project' => $project1,
                'contractor' => $contractor2,
                'amount' => 180000.00,
                'days_ago' => 6,
                'type' => PaymentType::LaborWage,
                'ref' => 'VOUCHER-#00001',
                'notes' => 'Labor wage payout for steel fixers and shuttering team.',
                'voided' => false,
            ],
            [
                'project' => $project2,
                'contractor' => $contractor4,
                'amount' => 200000.00,
                'days_ago' => 10,
                'type' => PaymentType::Installment,
                'ref' => 'CHQ-VOID-001',
                'notes' => 'Cheque bounced due to signature mismatch; voided in ledger.',
                'voided' => true,
            ],
        ];

        foreach ($payments as $p) {
            ContractorPayment::firstOrCreate(
                ['reference' => $p['ref']],
                [
                    'project_id' => $p['project']->id,
                    'contractor_id' => $p['contractor']->id,
                    'recorded_by' => $owner->id,
                    'amount' => $p['amount'],
                    'payment_date' => Carbon::today()->subDays($p['days_ago'])->toDateString(),
                    'payment_type' => $p['type']->value ?? $p['type'],
                    'notes' => $p['notes'],
                    'is_voided' => $p['voided'],
                ]
            );
        }

        // -------------------------------------------------------------
        // 9. EXPENSES (With correct category slugs)
        // -------------------------------------------------------------
        $catMap = [
            'cement' => ExpenseCategory::where('slug', 'cement')->first(),
            'steel' => ExpenseCategory::where('slug', 'steel')->first(),
            'bricks' => ExpenseCategory::where('slug', 'bricks')->first(),
            'sand' => ExpenseCategory::where('slug', 'sand')->first(),
            'gravel' => ExpenseCategory::where('slug', 'gravel')->first(),
            'machinery' => ExpenseCategory::where('slug', 'machinery')->first(),
            'water' => ExpenseCategory::where('slug', 'water')->first(),
            'electrical' => ExpenseCategory::where('slug', 'electrical')->first(),
            'plumbing' => ExpenseCategory::where('slug', 'plumbing')->first(),
            'tiles' => ExpenseCategory::where('slug', 'tiles')->first(),
            'labor' => ExpenseCategory::where('slug', 'labor')->first(),
        ];

        $expenseData = [
            [
                'project' => $project1,
                'cat' => $catMap['cement'],
                'amount' => 450000,
                'vendor' => 'Bestway Cement Agency',
                'desc' => '300 Bags OPC Cement delivered to site',
                'method' => 'Bank Transfer',
                'days_ago' => 25,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['steel'],
                'amount' => 890000,
                'vendor' => 'Mughal Steel Traders',
                'desc' => '8 Ton Grade 60 Deformed Sarya',
                'method' => 'Cheque',
                'days_ago' => 20,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['bricks'],
                'amount' => 280000,
                'vendor' => 'Bismillah Brick Kiln',
                'desc' => '20,000 A-Grade Red Bricks',
                'method' => 'Cash',
                'days_ago' => 15,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['sand'],
                'amount' => 95000,
                'vendor' => 'Chenab Sand Suppliers',
                'desc' => '3 Dumpers Chenab River Sand',
                'method' => 'Cash',
                'days_ago' => 10,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['gravel'],
                'amount' => 115000,
                'vendor' => 'Margalla Stone Crushers',
                'desc' => '2 Dumpers Margalla Crushed Stone (Bajri)',
                'method' => 'Bank Transfer',
                'days_ago' => 9,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['machinery'],
                'amount' => 75000,
                'vendor' => 'Al-Rehman Crane & Transit Mixers',
                'desc' => 'Transit mixer & mobile concrete pump daily rental for slab pouring',
                'method' => 'Cash',
                'days_ago' => 5,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['water'],
                'amount' => 18000,
                'vendor' => 'Al-Madina Water Supply',
                'desc' => '6 Water Tankers for curing & brick soaking',
                'method' => 'Cash',
                'days_ago' => 2,
            ],
            [
                'project' => $project1,
                'cat' => $catMap['labor'],
                'amount' => 125000,
                'vendor' => 'Workforce Direct Labor',
                'desc' => 'Disbursed Direct Wages for workforce shifts (Sat – Thu week)',
                'method' => 'Cash',
                'days_ago' => 6,
            ],
            [
                'project' => $project2,
                'cat' => $catMap['cement'],
                'amount' => 220000,
                'vendor' => 'Lucky Cement Agency',
                'desc' => '150 Bags Falcon Brand Cement',
                'method' => 'Bank Transfer',
                'days_ago' => 8,
            ],
            [
                'project' => $project2,
                'cat' => $catMap['bricks'],
                'amount' => 140000,
                'vendor' => 'Rajput Kiln',
                'desc' => '10,000 Hand-Picked Facing Bricks',
                'method' => 'Cash',
                'days_ago' => 4,
            ],
            [
                'project' => $project2,
                'cat' => $catMap['electrical'],
                'amount' => 85000,
                'vendor' => 'Pak Cables Authorized Dealer',
                'desc' => 'PVC Conduits, ceiling fan boxes, and flexible pipes',
                'method' => 'Online',
                'days_ago' => 6,
            ],
            [
                'project' => $project2,
                'cat' => $catMap['plumbing'],
                'amount' => 110000,
                'vendor' => 'Master Sanitary & Hardware',
                'desc' => 'UPVC and PPRC piping bundle for drain and hot/cold water',
                'method' => 'Bank Transfer',
                'days_ago' => 11,
            ],
            [
                'project' => $project2,
                'cat' => $catMap['tiles'],
                'amount' => 340000,
                'vendor' => 'Shabbir Tiles & Ceramics',
                'desc' => 'Spanish Porcelain Floor Tiles (60x120cm) 45 boxes',
                'method' => 'Cheque',
                'days_ago' => 3,
            ],
        ];

        foreach ($expenseData as $e) {
            if ($e['cat']) {
                Expense::firstOrCreate(
                    [
                        'project_id' => $e['project']->id,
                        'description' => $e['desc'],
                        'expense_date' => Carbon::today()->subDays($e['days_ago'])->toDateString(),
                    ],
                    [
                        'expense_category_id' => $e['cat']->id,
                        'recorded_by' => $owner->id,
                        'amount' => $e['amount'],
                        'vendor' => $e['vendor'],
                        'payment_method' => $e['method'],
                        'notes' => 'Seeded commercial demo transaction record',
                    ]
                );
            }
        }

        // -------------------------------------------------------------
        // 10. ACTIVITY LOGS (Auditable events for timeline view)
        // -------------------------------------------------------------
        $activityEvents = [
            [
                'project_id' => $project1->id,
                'event' => 'project_created',
                'description' => "Project '{$project1->name}' created by {$owner->name}",
                'subject' => $project1,
                'created_at' => Carbon::today()->subMonths(2),
            ],
            [
                'project_id' => $project1->id,
                'event' => 'contractor_assigned',
                'description' => "Assigned contractor {$contractor1->company_name} to {$project1->name} with contract PKR 4,500,000",
                'subject' => $contractor1,
                'created_at' => Carbon::today()->subMonths(2)->addDays(1),
            ],
            [
                'project_id' => $project1->id,
                'event' => 'contractor_payment_created',
                'description' => 'Recorded contractor advance payment of PKR 1,000,000 via CHQ-778901',
                'subject' => $project1,
                'created_at' => Carbon::today()->subMonths(2)->addDays(2),
            ],
            [
                'project_id' => $project1->id,
                'event' => 'expense_created',
                'description' => 'Recorded material expense of PKR 450,000 for 300 Bags OPC Cement',
                'subject' => $project1,
                'created_at' => Carbon::today()->subDays(25),
            ],
            [
                'project_id' => $project1->id,
                'event' => 'attendance_bulk_payment_status_updated',
                'description' => 'Bulk updated payment status to Paid for 12 attendance record(s)',
                'subject' => $project1,
                'created_at' => Carbon::today()->subDays(6),
            ],
            [
                'project_id' => $project2->id,
                'event' => 'project_created',
                'description' => "Project '{$project2->name}' initiated by {$owner->name}",
                'subject' => $project2,
                'created_at' => Carbon::today()->subMonth(),
            ],
        ];

        foreach ($activityEvents as $ae) {
            ActivityLog::firstOrCreate(
                [
                    'project_id' => $ae['project_id'],
                    'event' => $ae['event'],
                    'description' => $ae['description'],
                ],
                [
                    'user_id' => $owner->id,
                    'subject_type' => get_class($ae['subject']),
                    'subject_id' => $ae['subject']->id,
                    'properties' => ['seeded' => true],
                    'created_at' => $ae['created_at'],
                    'updated_at' => $ae['created_at'],
                ]
            );
        }
    }
}
