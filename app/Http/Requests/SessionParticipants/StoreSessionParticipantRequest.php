<?php

namespace App\Http\Requests\SessionParticipants;

use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSessionParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof Session
            && Gate::allows(
                'create',
                [
                    SessionParticipant::class,
                    $session,
                ],
            );
    }

    public function rules(): array
    {
        /** @var Session $session */
        $session = $this->route('session');

        /** @var Tontine $tontine */
        $tontine = $this->route('tontine');

        return [
            'membership_id' => [
                'required',
                'integer',
                Rule::exists(
                    'memberships',
                    'id',
                )->where(
                    'tontine_id',
                    $tontine->id,
                ),
                Rule::unique(
                    'session_participants',
                    'membership_id',
                )->where(
                    'session_id',
                    $session->id,
                ),
            ],

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
