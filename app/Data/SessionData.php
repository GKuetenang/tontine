<?php

namespace App\Data;

use App\Enums\DrawAllocationMode;
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
        public Optional|int|null $base_contribution_amount,
        public int $beneficiaries_per_meeting,

        public Optional|SessionStatus $status,
        public Optional|int $participants_count,
        public Optional|int $meetings_count,
        public Optional|DrawAllocationMode $draw_allocation_mode,
        public Optional|string $draw_allocation_mode_label,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable|null $activated_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable|null $closed_at,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $created_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        Session $session,
    ): self {
        return new self(
            name: $session->name,

            slug: $session->slug,

            id: $session->id,

            description: $session->description,

            start_at: $session->start_at,

            end_at: $session->end_at,

            default_contribution_amount: $session->default_contribution_amount,

            base_contribution_amount: $session->base_contribution_amount,

            status: $session->status,

            beneficiaries_per_meeting: $session->beneficiaries_per_meeting,

            participants_count: array_key_exists(
                'participants_count',
                $session->getAttributes(),
            )
                ? $session->participants_count
                : Optional::create(),

            meetings_count: array_key_exists(
                'meetings_count',
                $session->getAttributes(),
            )
                ? (int) $session->meetings_count
                : Optional::create(),

            draw_allocation_mode: $session->draw_allocation_mode,
            draw_allocation_mode_label: $session->draw_allocation_mode->label(),

            activated_at: $session->activated_at,

            closed_at: $session->closed_at,

            created_at: $session->created_at,

            updated_at: $session->updated_at,
        );
    }
}
