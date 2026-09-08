<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandates', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status', 20)->default('draft');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'status']);
            $table->index(['group_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandates');
    }
};
