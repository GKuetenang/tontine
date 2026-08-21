<?php

use App\Models\Meeting;
use App\Models\SessionParticipant;
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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Meeting::class)
                ->constrained()
                ->restrictOnDelete();

            $table->foreignIdFor(SessionParticipant::class)
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedBigInteger('amount_due');

            $table->timestamps();

            $table->unique([
                'meeting_id',
                'session_participant_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
