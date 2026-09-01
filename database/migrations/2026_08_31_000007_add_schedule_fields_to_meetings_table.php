<?php

use App\Models\MeetingSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table): void {
            $table->foreignIdFor(MeetingSchedule::class)->nullable()->after('session_id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('location');
            $table->unique(['meeting_schedule_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table): void {
            $table->dropUnique(['meeting_schedule_id', 'scheduled_at']);
            $table->dropConstrainedForeignIdFor(MeetingSchedule::class);
            $table->dropColumn('duration_minutes');
        });
    }
};
