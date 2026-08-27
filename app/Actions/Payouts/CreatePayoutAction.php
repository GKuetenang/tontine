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
        int $amount,
    ): Payout {
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
                    'Ce bénéficiaire a déjà reçu le versement correspondant à cette position.'
                ),
            ]);
        }

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
                    'Le payout ne peut être créé que pour une réunion en cours ou terminée.'
                ),
            ]);
        }

        if (
            $drawEntry
            ->draw
            ->session_id
            !== $meeting->session_id
        ) {
            throw ValidationException::withMessages([
                'draw_entry_id' => __(
                    'Cette entrée du tirage n’appartient pas à la session de cette réunion.'
                ),
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __(
                    'Le montant doit être supérieur à zéro.'
                ),
            ]);
        }

        if (
            Payout::query()
            ->where(
                'meeting_id',
                $meeting->id,
            )
            ->where(
                'draw_entry_id',
                $drawEntry->id,
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'draw_entry_id' => __(
                    'Un payout existe déjà pour ce bénéficiaire lors de cette réunion.'
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
