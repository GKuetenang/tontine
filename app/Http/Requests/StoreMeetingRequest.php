<?php

namespace App\Http\Requests;

use App\Models\Meeting;
use App\Models\Session;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof Session
            && Gate::allows(
                'create',
                [Meeting::class, $session],
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
