<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'organizer')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        // Existing admin accounts cannot be distinguished from legacy organizers safely.
    }
};
