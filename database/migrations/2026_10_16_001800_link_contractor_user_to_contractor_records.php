<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $user = DB::table('users')->where('email', 'contractor@construction.test')->first();

        if ($user) {
            // 1. Delete the empty dummy contractor (ID 2) which has no workers, projects, or payments
            DB::table('contractors')
                ->where('id', 2)
                ->where('user_id', $user->id)
                ->delete();

            // 2. Link Contractor #1 (which owns all 7 staff, active project, and attendance) to this user
            DB::table('contractors')
                ->where('id', 1)
                ->update([
                    'user_id' => $user->id,
                ]);

            // 3. Sync User display name and phone with the contractor profile
            $contractor = DB::table('contractors')->where('id', 1)->first();
            if ($contractor) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'name' => $contractor->name,
                        'phone' => $contractor->phone,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = DB::table('users')->where('email', 'contractor@construction.test')->first();

        if ($user) {
            DB::table('contractors')
                ->where('id', 1)
                ->where('user_id', $user->id)
                ->update([
                    'user_id' => null,
                ]);

            DB::table('contractors')->insert([
                'id' => 2,
                'user_id' => $user->id,
                'name' => 'Main Contractor',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => 'Main Contractor',
                ]);
        }
    }
};
