<?php

use App\Models\Session;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Session::class)->unique()->constrained('group_sessions')->cascadeOnDelete();
            $table->string('rrule');
            $table->dateTime('starts_at');
            $table->string('timezone');
            $table->string('default_title');
            $table->string('default_location')->nullable();
            $table->unsignedSmallInteger('default_duration_minutes');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_schedules');
    }
};
