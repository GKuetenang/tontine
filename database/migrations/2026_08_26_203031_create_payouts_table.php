<?php

use App\Models\DrawEntry;
use App\Models\Meeting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table): void {
            $table->id();

            $table->foreignIdFor(Meeting::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(DrawEntry::class)
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedBigInteger('amount');

            $table->string('status');

            $table->timestamp('paid_at')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                'draw_entry_id',
            );
            $table->index([
                'meeting_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'payouts',
        );
    }
};
