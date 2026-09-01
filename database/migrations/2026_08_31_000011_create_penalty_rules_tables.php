<?php

use App\Models\Tontine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Tontine::class)->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('trigger');
            $table->string('calculation_type')->default('fixed');
            $table->decimal('value', 15, 2)->nullable();
            $table->unsignedInteger('grace_period')->nullable();
            $table->string('grace_unit')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['tontine_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_rules');
    }
};
