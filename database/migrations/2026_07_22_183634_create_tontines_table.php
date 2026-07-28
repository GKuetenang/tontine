<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tontines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->string('name', 200);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('currency', 3)->default('XAF');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_active']);
            $table->index(['is_public', 'is_active']);
            $table->index(['is_public', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tontines');
    }
};
