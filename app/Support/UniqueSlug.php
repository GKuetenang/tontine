<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class UniqueSlug
{
    /**
     * @param  Builder<Model>  $query
     */
    public function generate(
        Builder $query,
        string $value,
        int $suffixLength = 10,
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'item';
        }

        do {
            $slug = $baseSlug.'-'.Str::lower(
                Str::random($suffixLength),
            );
        } while (
            (clone $query)
                ->withTrashed()
                ->where('slug', $slug)
                ->exists()
        );

        return $slug;
    }
}
