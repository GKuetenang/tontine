<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'DrawEntry')]
class DrawEntryData extends Data
{
    public function __construct(
        public int $id,
        public int $position,
        public int $entry_number,
        public Optional|SessionParticipantData $session_participant,
    ) {}
}
