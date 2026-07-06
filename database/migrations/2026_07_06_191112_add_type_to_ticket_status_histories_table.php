<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_status_histories', function (Blueprint $table) {
            $table->enum('type', ['status_change', 'comment'])->default('status_change')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_status_histories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};