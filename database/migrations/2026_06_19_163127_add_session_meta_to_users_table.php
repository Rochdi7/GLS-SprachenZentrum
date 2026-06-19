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
        Schema::table('users', function (Blueprint $table) {
            $table->string('session_ip')->nullable()->after('session_token');
            $table->string('session_device')->nullable()->after('session_ip');
            $table->timestamp('session_at')->nullable()->after('session_device');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['session_ip', 'session_device', 'session_at']);
        });
    }
};
