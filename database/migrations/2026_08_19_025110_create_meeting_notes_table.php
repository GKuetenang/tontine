<?php

use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_notes', function (Blueprint $table): void {
            $table->id();

            $table->foreignIdFor(Meeting::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(
                MeetingAgendaItem::class,
                'meeting_agenda_item_id'
            )
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->longText('content');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'meeting_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_notes');
    }
};
