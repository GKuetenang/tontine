<?php

use App\Models\Membership;
use App\Models\Session;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Session::class)->constrained('tontine_sessions')->restrictOnDelete();
            $table->foreignIdFor(Membership::class)->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['session_id', 'membership_id']);
            $table->index(['session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_contributions');
    }
};
