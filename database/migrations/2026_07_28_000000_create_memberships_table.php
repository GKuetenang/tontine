<?php

use App\Models\Tontine;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Tontine::class)->constrained()->restrictOnDelete();
            $table->string('member_number', 30);
            $table->string('status', 20)->default('active');
            $table->timestamp('verified_at')->useCurrent();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'tontine_id']);
            $table->unique(['tontine_id', 'member_number']);
            $table->index(['tontine_id', 'status']);
            $table->index(['tontine_id', 'joined_at']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'left_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
