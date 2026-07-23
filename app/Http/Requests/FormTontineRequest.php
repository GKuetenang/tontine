<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormTontineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200',
                Rule::unique('tontines', 'name')
                    ->where(fn($query) => $query->where('user_id', $this->user()->id)
                    )->ignore($this->route()->parameter('tontine'))
            ],
            'slug' => ['
                nullable', 'string',
                Rule::unique('tontines', 'slug')
                    ->ignore($this->route()->parameter('tontine')),
            ],
            'description' => 'nullable|string',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') ?: Str::slug($this->input('name')),
        ]);
    }
}
