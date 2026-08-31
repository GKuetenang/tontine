<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingReport')]
class MeetingReportData extends Data
{
    public function __construct(
        public MeetingData $meeting,
        public MeetingReportSummaryData $summary,
    ) {}
}
