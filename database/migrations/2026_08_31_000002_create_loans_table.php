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
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Session::class)->constrained('tontine_sessions')->restrictOnDelete();
            $table->foreignIdFor(Membership::class)->constrained()->restrictOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 7, 2);
            $table->unsignedSmallInteger('term_months');
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_due', 15, 2);
            $table->date('due_at');
            $table->text('reason')->nullable();
            $table->string('status');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['session_id', 'status']);
            $table->index(['membership_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
