<?php

namespace App\Actions\Meetings;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\User;
use App\Support\UniqueSlug;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class CreateMeetingAction
{
    public function __construct(
        private readonly UniqueSlug $uniqueSlug,
    ) {}

    public function execute(
        Session $session,
        User $creator,
        string $title,
        CarbonImmutable $scheduledAt,
        ?string $description = null,
        ?string $location = null,
    ): Meeting {
        return DB::transaction(function () use (
            $session,
            $creator,
            $title,
            $scheduledAt,
            $description,
            $location,
        ): Meeting {
            $number = (
                $session
                ->meetings()
                ->max('number') ?? 0
            ) + 1;

            $meeting = new Meeting();

            $meeting->fill([
                'number' => $number,
                'title' => $title,
                'description' => $description,
                'scheduled_at' => $scheduledAt,
                'location' => $location,
            ]);

            $meeting->session()
                ->associate($session);

            $meeting->creator()
                ->associate($creator);

            $meeting->status =
                MeetingStatus::Scheduled;

            $meeting->slug =
                $this->uniqueSlug->generate(
                    $session->meetings()->getQuery(),
                    $title,
                );

            $meeting->save();

            return $meeting->refresh();
        });
    }
}
