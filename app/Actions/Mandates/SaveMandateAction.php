<?php

namespace App\Actions\Mandates;

use App\Enums\MandateStatus;
use App\Models\Group;
use App\Models\Mandate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaveMandateAction
{
    public function execute(Group $group, User $creator, array $attributes, ?Mandate $mandate = null): Mandate
    {
        return DB::transaction(function () use ($group, $creator, $attributes, $mandate): Mandate {
            if ($mandate && $mandate->status !== MandateStatus::Draft) {
                throw ValidationException::withMessages([
                    'name' => __('Seul un mandat en brouillon peut être modifié.'),
                ]);
            }

            if ($mandate) {
                $startsAt = CarbonImmutable::parse($attributes['starts_at'])->startOfDay();
                $endsAt = CarbonImmutable::parse($attributes['ends_at'])->startOfDay();
                $hasAssignmentsOutsidePeriod = $mandate->assignments()
                    ->where(function ($query) use ($startsAt, $endsAt): void {
                        $query->whereDate('starts_at', '<', $startsAt)
                            ->orWhereDate('ends_at', '>', $endsAt);
                    })
                    ->exists();

                if ($hasAssignmentsOutsidePeriod) {
                    throw ValidationException::withMessages([
                        'starts_at' => __('La nouvelle période doit contenir toutes les affectations déjà enregistrées.'),
                    ]);
                }
            }

            $mandate ??= new Mandate;
            $mandate->group()->associate($group);
            $mandate->creator()->associate($creator);
            $mandate->fill($attributes);
            $mandate->save();

            return $mandate->refresh();
        });
    }
}
