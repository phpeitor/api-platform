<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('last_used_at');
        });

        DB::table('api_tokens')
            ->whereNull('expires_at')
            ->update(['expires_at' => now()->addDays(30)]);
    }

    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
