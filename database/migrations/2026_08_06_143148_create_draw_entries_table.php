<?php

use App\Models\Draw;
use App\Models\Membership;
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
        Schema::create('draw_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Draw::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(SessionParticipant::class)
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('position');

            /*
            * Numéro de la part du participant.
            * Exemple : part 1 et part 2.
            */
            $table->unsignedSmallInteger('entry_number')
                ->default(1);

            $table->timestamps();

            $table->unique([
                'draw_id',
                'position',
            ]);

            $table->unique([
                'draw_id',
                'session_participant_id',
                'entry_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_entries');
    }
};
