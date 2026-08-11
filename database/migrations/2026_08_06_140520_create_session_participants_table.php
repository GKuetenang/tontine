<?php

use App\Models\Membership;
use App\Models\Session;
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
        Schema::create('session_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Session::class)
                ->constrained()
                ->restrictOnDelete();

            $table->foreignIdFor(Membership::class)
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedBigInteger(
                'contribution_amount'
            );

            $table->unsignedSmallInteger('draw_entries_count')
                ->default(1);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamp('joined_at')
                ->useCurrent();

            $table->timestamp('left_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'session_id',
                'membership_id',
            ]);

            $table->index([
                'session_id',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_participants');
    }
};
