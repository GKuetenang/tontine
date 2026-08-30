<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingPayoutContext')]
class MeetingPayoutContextData extends Data
{
    /**
     * @param array<int, PayoutCandidateData> $expected
     * @param array<int, PayoutCandidateData> $available
     */
    public function __construct(
        public array $expected,
        public array $available,
    ) {}
}
