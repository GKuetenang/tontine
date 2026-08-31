<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingReportSummary')]
class MeetingReportSummaryData extends Data
{
    public function __construct(
        public int $attendances_total,
        public int $present_total,
        public int $late_total,
        public int $absent_total,
        public int $excused_total,
        public int $pending_total,
        public string $contributions_due,
        public string $contributions_paid,
        public string $contributions_remaining,
        public string $payouts_paid,
    ) {}
}
