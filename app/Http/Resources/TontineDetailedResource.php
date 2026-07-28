<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TontineDetailedResource extends TontineResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->resource->description,
        ];
    }
}
