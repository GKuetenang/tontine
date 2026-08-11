<?php

namespace App\Data;

use App\Enums\SessionStatus;
use App\Models\Session;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Session')]
class SessionData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,

        public Optional|int $id,
        public Optional|string|null $description,

        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public Optional|CarbonImmutable|null $start_at,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public Optional|CarbonImmutable|null $end_at,
        public Optional|int|null $default_contribution_amount,

        public Optional|SessionStatus $status,
        public Optional|int $participants_count,

        public Optional|CarbonImmutable|null $activated_at,
        public Optional|CarbonImmutable|null $closed_at,

        public Optional|CarbonImmutable $created_at,
        public Optional|CarbonImmutable $updated_at,
    ) {}

    // public static function fromModel(Session $session): self
    // {
    //     return self::from([
    //         'id' => $session->id,
    //         'name' => $session->name,
    //         'slug' => $session->slug,
    //         'description' => $session->description,

    //         'start_at' => $session->start_at,
    //         'end_at' => $session->end_at,

    //         'status' => $session->status,

    //         'activated_at' => $session->activated_at,
    //         'closed_at' => $session->closed_at,

    //         'created_at' => $session->created_at,
    //         'updated_at' => $session->updated_at,
    //     ]);
    // }
}
