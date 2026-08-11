<?php

namespace App\Data;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Membership')]
class MembershipData extends Data
{
    public function __construct(
        public int $id,

        public string $member_number,

        public MembershipStatus $status,

        public Optional|CarbonImmutable|null $verified_at,

        public Optional|CarbonImmutable|null $joined_at,

        public Optional|CarbonImmutable|null $left_at,

        public Optional|CarbonImmutable $created_at,

        public Optional|CarbonImmutable $updated_at,

        public Optional|CarbonImmutable|null $deleted_at,

        public Optional|MemberUserData $user,

        public Optional|MemberUserData|null $inviter,

        public Optional|MemberUserData|null $creator,
    ) {}

    public static function fromModel(
        Membership $membership,
    ): self {
        return new self(
            id: $membership->id,

            member_number: $membership->member_number,

            status: $membership->status,

            verified_at: $membership->verified_at,

            joined_at: $membership->joined_at,

            left_at: $membership->left_at,

            created_at: $membership->created_at,

            updated_at: $membership->updated_at,

            deleted_at: $membership->deleted_at,

            user: $membership->relationLoaded('user')
                ? MemberUserData::from($membership->user)
                : Optional::create(),

            inviter: $membership->relationLoaded('inviter')
                ? (
                    $membership->inviter
                    ? MemberUserData::from($membership->inviter)
                    : null
                )
                : Optional::create(),

            creator: $membership->relationLoaded('creator')
                ? (
                    $membership->creator
                    ? MemberUserData::from($membership->creator)
                    : null
                )
                : Optional::create(),
        );
    }
}
