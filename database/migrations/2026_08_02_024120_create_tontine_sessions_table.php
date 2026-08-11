<?php

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Tontine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Illuminate\Support\now;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tontine_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tontine::class)->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('default_contribution_amount')
                ->nullable();
            $table->string('draw_allocation_mode')
                ->default(DrawAllocationMode::OnePerMember->value);
            $table->unsignedBigInteger('base_contribution_amount')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('status')
                ->default(SessionStatus::Draft->value);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->unique(['tontine_id', 'name']);
            $table->unique(['tontine_id', 'slug']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tontine_sessions');
    }
};
