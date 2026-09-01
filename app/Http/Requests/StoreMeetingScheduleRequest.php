<?php

namespace App\Http\Requests;

use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Models\Meeting;
use App\Models\Session;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreMeetingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof Session
            && Gate::allows('create', [Meeting::class, $session]);
    }

    public function rules(): array
    {
        return [
            'recurrence' => [
                'required',
                Rule::in([
                    MeetingRecurrence::Weekly->value,
                    MeetingRecurrence::Monthly->value,
                ]),
            ],
            'interval' => ['required', 'integer', 'min:1', 'max:60'],
            'monthly_pattern' => ['nullable', Rule::enum(MeetingMonthlyPattern::class)],
            'starts_at' => ['required', 'date'],
            'timezone' => ['required', 'timezone:all'],
            'default_title' => ['required', 'string', 'max:200'],
            'default_location' => ['nullable', 'string', 'max:255'],
            'default_duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ];
    }
}
