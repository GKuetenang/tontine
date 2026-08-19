<?php

namespace App\Data;

use App\Data\MemberUserData;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Draw')]
class DrawData extends Data
{
    public function __construct(
        public int $id,
        public ?string $description,
        public ?CarbonImmutable $confirmed_at,
        public Optional|MemberUserData|null $creator,
        public Optional|MemberUserData|null $confirmer,
        /** @var array<DrawEntryData> */
        public Optional|array $entries,
    ) {}
}
