<?php

use App\Models\Meeting;
use App\Models\SessionParticipant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meeting_attendances',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignIdFor(Meeting::class)
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignIdFor(
                    SessionParticipant::class,
                )
                    ->constrained()
                    ->restrictOnDelete();

                $table->string('status');

                $table->timestamp('checked_in_at')
                    ->nullable();

                $table->text('note')
                    ->nullable();

                $table->timestamps();

                $table->unique([
                    'meeting_id',
                    'session_participant_id',
                ]);

                $table->index([
                    'meeting_id',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'meeting_attendances',
        );
    }
};
