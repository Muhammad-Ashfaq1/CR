<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_contractor', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->decimal('contract_amount', 15, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->date('assigned_date')->nullable();
            $table->text('agreement_notes')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'contractor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contractor');
    }
};
