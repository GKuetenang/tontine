<?php

use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repayments', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Loan::class)->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('principal_amount', 15, 2);
            $table->timestamp('paid_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['loan_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};
