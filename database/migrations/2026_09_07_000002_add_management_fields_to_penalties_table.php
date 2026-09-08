<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalties', function (Blueprint $table): void {
            $table->string('source')->default('automatic')->after('rule_name');
            $table->string('status')->default('pending')->after('source');
            $table->text('reason')->nullable()->after('status');
            $table->foreignId('created_by')->nullable()->after('assessed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('waived_at')->nullable()->after('created_by');
            $table->foreignId('waived_by')->nullable()->after('waived_at')->constrained('users')->nullOnDelete();
            $table->text('waiver_reason')->nullable()->after('waived_by');
            $table->index(['meeting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table): void {
            $table->dropIndex(['meeting_id', 'status']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('waived_by');
            $table->dropColumn(['source', 'status', 'reason', 'waived_at', 'waiver_reason']);
        });
    }
};
