<?php

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MemberUser')]
class MemberUserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $username,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->full_name,
            email: $user->email,
            username: $user->username,
        );
    }
}
