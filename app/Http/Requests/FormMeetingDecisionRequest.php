<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormMeetingDecisionRequest extends FormRequest
{
    public function rules(): array
    {
        $meeting =
            $this->route('meeting');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
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
