<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('site_name')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->string('status')->default('planning');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
