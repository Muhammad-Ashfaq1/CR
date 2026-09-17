<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('full_day'); // full_day | half_day | absent | leave
            $table->decimal('wage_at_time', 10, 2); // Snapshot of wage at time of recording
            $table->decimal('payable_amount', 10, 2)->default(0); // Calculated payable for this record
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['worker_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
