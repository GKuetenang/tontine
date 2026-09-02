<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class GroupAbilitiesData extends Data
{
    public function __construct(
        public bool $view,
        public bool $update,
        public bool $delete,
        public bool $view_memberships,
    ) {}
}
