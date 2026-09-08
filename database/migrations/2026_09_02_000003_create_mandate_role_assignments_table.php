<?php

use App\Models\Mandate;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandate_role_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Mandate::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Membership::class)->constrained()->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->foreignIdFor(User::class, 'appointed_by')->constrained('users')->restrictOnDelete();
            $table->foreignIdFor(User::class, 'ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('end_reason')->nullable();
            $table->timestamps();

            $table->index(['mandate_id', 'starts_at', 'ends_at']);
            $table->index(['membership_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandate_role_assignments');
    }
};
