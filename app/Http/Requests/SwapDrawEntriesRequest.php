<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwapDrawEntriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_entry_id' => [
                'required',
                'integer',
                'exists:draw_entries,id',
            ],

            'target_entry_id' => [
                'required',
                'integer',
                'different:source_entry_id',
                'exists:draw_entries,id',
            ],
        ];
    }
}
