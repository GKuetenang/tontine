<?php

namespace App\Http\Requests\SessionParticipants;

use App\Models\SessionParticipant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateSessionParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $participant = $this->route(
            'participant'
        );

        return $participant instanceof SessionParticipant
            && Gate::allows(
                'update',
                $participant,
            );
    }

    public function rules(): array
    {
        return [
            'contribution_amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'draw_entries_count' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
