<?php

namespace App\Actions\Payouts;

use App\Enums\MeetingStatus;
use App\Enums\PayoutStatus;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class CreatePayoutAction
{
    public function execute(
        Meeting $meeting,
        DrawEntry $drawEntry,
        User $creator,
        string $amount,
    ): Payout {
        if (
            ! in_array(
                $meeting->status,
                [
                    MeetingStatus::InProgress,
                    MeetingStatus::Completed,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'meeting' => __(
                    'Un versement ne peut être préparé que pour une réunion en cours ou terminée.'
                ),
            ]);
        }

        $drawEntry->loadMissing(
            'draw',
        );

        if (
            !$drawEntry
                ->draw
                ->isConfirmed()
        ) {
            throw ValidationException::withMessages([
                'draw_entry_id' => __(
                    'Le tirage doit être confirmé avant d’effectuer un versement.'
                ),
            ]);
        }

        if (
            $drawEntry->draw->session_id
            !== $meeting->session_id
        ) {
            throw ValidationException::withMessages([
                'draw_entry_id' => __(
                    'Ce bénéficiaire n’appartient pas au tirage de cette session.'
                ),
            ]);
        }

        if (
            Payout::query()
            ->where(
                'draw_entry_id',
                $drawEntry->id,
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'draw_entry_id' => __(
                    'Cette part du tirage possède déjà un versement.'
                ),
            ]);
        }

        $payout =
            new Payout();

        $payout->fill([
            'amount' => $amount,

            'status' =>
            PayoutStatus::Pending,
        ]);

        $payout->meeting()
            ->associate($meeting);

        $payout->drawEntry()
            ->associate($drawEntry);

        $payout->creator()
            ->associate($creator);

        $payout->save();

        return $payout->refresh();
    }
}
