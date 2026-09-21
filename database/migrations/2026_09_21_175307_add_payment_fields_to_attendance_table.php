<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table): void {
            $table->boolean('is_paid')->default(false)->after('payable_amount')->index();
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->string('payment_method')->nullable()->after('paid_at'); // direct_pay | contractor_pay | cash | etc
            $table->string('payment_reference')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table): void {
            $table->dropColumn(['is_paid', 'paid_at', 'payment_method', 'payment_reference']);
        });
    }
};
