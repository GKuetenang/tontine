<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormMeetingNoteRequest extends FormRequest
{
    public function rules(): array
    {
        $meeting = $this->route('meeting');

        return [
            'content' => [
                'required',
                'string',
            ],

            'meeting_agenda_item_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'meeting_agenda_items',
                    'id',
                )->where(
                    'meeting_id',
                    $meeting->id,
                ),
            ],
        ];
    }
}
