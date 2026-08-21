<?php

namespace App\Http\Requests;

use App\Models\Meeting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meeting = $this->route('meeting');

        return $meeting instanceof Meeting
            && Gate::allows(
                'update',
                $meeting,
            );
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'scheduled_at' => [
                'required',
                'date',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
