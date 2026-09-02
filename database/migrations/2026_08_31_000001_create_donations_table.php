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
        Schema::create('donations', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Session::class)->constrained('group_sessions')->restrictOnDelete();
            $table->foreignIdFor(Membership::class)->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
