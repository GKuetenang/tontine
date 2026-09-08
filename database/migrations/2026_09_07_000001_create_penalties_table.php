<?php

use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\Membership;
use App\Models\PenaltyRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(PenaltyRule::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Meeting::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Membership::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Contribution::class)->nullable()->constrained()->restrictOnDelete();
            $table->string('trigger');
            $table->decimal('amount', 15, 2);
            $table->string('rule_name');
            $table->timestamp('assessed_at');
            $table->timestamps();

            $table->unique(['penalty_rule_id', 'meeting_id', 'membership_id']);
            $table->index(['membership_id', 'assessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
