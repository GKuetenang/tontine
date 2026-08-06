<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'start_at' => $this->resource->start_at,
            'end_at' => $this->resource->end_at,
            'is_active' => $this->resource->is_active,
            'is_closed' => $this->resource->is_closed,
            'activated_at' => $this->resource->activated_at,
            'closed_at' => $this->resource->closed_at,
        ];
    }
}
