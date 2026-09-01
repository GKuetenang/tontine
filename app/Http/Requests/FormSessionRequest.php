<?php

namespace App\Http\Requests;

use App\Enums\DrawAllocationMode;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FormSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $tontine = $this->route('tontine');
        $session = $this->route('session');

        if (! $tontine instanceof Tontine) {
            return false;
        }

        if ($session instanceof Session) {
            return Gate::allows('update', $session);
        }

        return Gate::allows(
            'create',
            [Session::class, $tontine],
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tontine = $this->route('tontine');

        return [
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('tontine_sessions', 'name')
                    ->where('tontine_id', $tontine->id)
                    ->ignore($this->route()->parameter('session')),
            ],
            'description' => ['nullable', 'string'],
            'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_at'],
            'draw_allocation_mode' => Rule::enum(DrawAllocationMode::class),
            'default_contribution_amount' => ['nullable', 'numeric'],
            'base_contribution_amount' => ['nullable', 'integer', 'min:1'],
            'beneficiaries_per_meeting' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('nom'),
            'start_at' => __('date de début'),
            'end_at' => __('date de fin'),
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.after_or_equal' => __(
                'La date de fin doit être postérieure ou égale à la date de début.'
            ),
        ];
    }
}
