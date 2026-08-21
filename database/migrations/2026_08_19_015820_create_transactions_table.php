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
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();

            $table->foreignIdFor(Session::class)
                ->constrained('tontine_sessions')
                ->restrictOnDelete();

            $table->foreignIdFor(Membership::class)
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->nullableMorphs('transactionable');

            $table->string('type');

            $table->string('direction');

            $table->unsignedBigInteger('amount');

            $table->text('description')
                ->nullable();

            $table->timestamp('occurred_at');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'session_id',
                'type',
            ]);

            $table->index([
                'membership_id',
                'type',
            ]);

            $table->index([
                'occurred_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
