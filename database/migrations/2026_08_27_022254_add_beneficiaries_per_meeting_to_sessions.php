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
        Schema::table('tontine_sessions', function (Blueprint $table) {
            $table->unsignedSmallInteger(
                'beneficiaries_per_meeting',
            )
                ->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tontine_sessions', function (Blueprint $table) {
            $table->dropColumn('beneficiaries_per_meeting');
        });
    }
};
