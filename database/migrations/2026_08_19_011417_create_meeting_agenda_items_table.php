<?php

use App\Models\Meeting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meeting_agenda_items',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignIdFor(Meeting::class)
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('title');

                $table->text('description')
                    ->nullable();

                $table->unsignedInteger('position');

                $table->timestamps();

                $table->unique([
                    'meeting_id',
                    'position',
                ]);

                $table->index([
                    'meeting_id',
                    'created_at',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'meeting_agenda_items',
        );
    }
};
