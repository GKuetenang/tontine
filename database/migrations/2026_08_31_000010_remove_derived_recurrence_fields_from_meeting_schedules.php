<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['recurrence', 'monthly_pattern'] as $column) {
            if (Schema::hasColumn('meeting_schedules', $column)) {
                Schema::table('meeting_schedules', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('meeting_schedules', 'recurrence')) {
            Schema::table('meeting_schedules', function (Blueprint $table): void {
                $table->string('recurrence')->nullable()->after('session_id');
            });
        }

        if (! Schema::hasColumn('meeting_schedules', 'monthly_pattern')) {
            Schema::table('meeting_schedules', function (Blueprint $table): void {
                $table->string('monthly_pattern')->nullable()->after('recurrence');
            });
        }

        DB::table('meeting_schedules')
            ->orderBy('id')
            ->each(function (object $schedule): void {
                $recurrence = str_contains($schedule->rrule, 'FREQ=MONTHLY')
                    ? 'monthly'
                    : (str_contains($schedule->rrule, 'INTERVAL=2')
                        ? 'biweekly'
                        : 'weekly');

                DB::table('meeting_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'recurrence' => $recurrence,
                        'monthly_pattern' => str_contains($schedule->rrule, 'BYDAY=')
                            ? 'weekday_ordinal'
                            : 'day_of_month',
                    ]);
            });

        Schema::table('meeting_schedules', function (Blueprint $table): void {
            $table->string('recurrence')->nullable(false)->change();
            $table->string('monthly_pattern')
                ->default('day_of_month')
                ->nullable(false)
                ->change();
        });
    }
};
