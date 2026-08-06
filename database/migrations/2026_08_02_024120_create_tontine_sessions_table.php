<?php

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
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->index(['tontine_id', 'is_active']);
            $table->index(['tontine_id', 'is_closed']);
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
