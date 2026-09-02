<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $meeting_id
 * @property int $session_participant_id
 * @property int $amount_due
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \App\Models\SessionParticipant $sessionParticipant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\ContributionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereSessionParticipantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contribution whereUpdatedAt($value)
 */
	class Contribution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $session_id
 * @property int $membership_id
 * @property numeric $amount
 * @property string $reason
 * @property \App\Enums\DonationStatus $status
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Membership|null $membership
 * @property-read \App\Models\Session|null $session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\DonationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereMembershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereUpdatedAt($value)
 */
	class Donation extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperDraw
 * @property int $id
 * @property int $session_id
 * @property int|null $created_by
 * @property int|null $confirmed_by
 * @property \Carbon\CarbonImmutable|null $confirmed_at
 * @property string|null $description
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $confirmer
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DrawEntry> $entries
 * @property-read int|null $entries_count
 * @property-read \App\Models\Session|null $session
 * @method static \Database\Factories\DrawFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draw withoutTrashed()
 */
	class Draw extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperDrawEntry
 * @property int $id
 * @property int $draw_id
 * @property int $session_participant_id
 * @property int $position
 * @property int $entry_number
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Draw|null $draw
 * @property-read \App\Models\Payout|null $payout
 * @property-read \App\Models\SessionParticipant $sessionParticipant
 * @method static \Database\Factories\DrawEntryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereDrawId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereEntryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereSessionParticipantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DrawEntry whereUpdatedAt($value)
 */
	class DrawEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $session_id
 * @property int $membership_id
 * @property numeric $amount
 * @property string|null $description
 * @property \Carbon\CarbonImmutable $occurred_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Membership|null $membership
 * @property-read \App\Models\Session|null $session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\InsuranceContributionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereMembershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InsuranceContribution whereUpdatedAt($value)
 */
	class InsuranceContribution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $session_id
 * @property int $membership_id
 * @property numeric $principal_amount
 * @property numeric $interest_rate
 * @property int $term_months
 * @property numeric $interest_amount
 * @property numeric $total_due
 * @property \Carbon\CarbonImmutable $due_at
 * @property string|null $reason
 * @property \App\Enums\LoanStatus $status
 * @property \Carbon\CarbonImmutable|null $approved_at
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Repayment> $repayments
 * @property-read int|null $repayments_count
 * @property-read \App\Models\Session|null $session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\LoanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereMembershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan wherePrincipalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereTermMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereTotalDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereUpdatedAt($value)
 */
	class Loan extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperMeeting
 * @property int $id
 * @property int $session_id
 * @property int|null $meeting_schedule_id
 * @property int $number
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property \Carbon\CarbonImmutable $scheduled_at
 * @property string|null $location
 * @property int|null $duration_minutes
 * @property \App\Enums\MeetingStatus $status
 * @property \Carbon\CarbonImmutable|null $opened_at
 * @property \Carbon\CarbonImmutable|null $closed_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingAgendaItem> $agendaItems
 * @property-read int|null $agenda_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingAttendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contribution> $contributions
 * @property-read int|null $contributions_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingDecision> $decisions
 * @property-read int|null $decisions_count
 * @property-read \App\Models\MeetingSchedule|null $meetingSchedule
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payout> $payouts
 * @property-read int|null $payouts_count
 * @property-read \App\Models\Session|null $session
 * @method static \Database\Factories\MeetingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereMeetingScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting withoutTrashed()
 */
	class Meeting extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperMeetingAgendaItem
 * @property int $id
 * @property int $meeting_id
 * @property string $title
 * @property string|null $description
 * @property int $position
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Meeting|null $meeting
 * @method static \Database\Factories\MeetingAgendaItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem whereUpdatedAt($value)
 */
	class MeetingAgendaItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperMeetingAttendance
 * @property int $id
 * @property int $meeting_id
 * @property int $session_participant_id
 * @property \App\Enums\AttendanceStatus $status
 * @property \Carbon\CarbonImmutable|null $checked_in_at
 * @property string|null $note
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \App\Models\SessionParticipant $sessionParticipant
 * @method static \Database\Factories\MeetingAttendanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereSessionParticipantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance whereUpdatedAt($value)
 */
	class MeetingAttendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $meeting_id
 * @property int|null $meeting_agenda_item_id
 * @property string $title
 * @property string|null $description
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\MeetingAgendaItem|null $agendaItem
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Meeting|null $meeting
 * @method static \Database\Factories\MeetingDecisionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereMeetingAgendaItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingDecision whereUpdatedAt($value)
 */
	class MeetingDecision extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $meeting_id
 * @property int|null $meeting_agenda_item_id
 * @property string $content
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\MeetingAgendaItem|null $agendaItem
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Meeting|null $meeting
 * @method static \Database\Factories\MeetingNoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereMeetingAgendaItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingNote whereUpdatedAt($value)
 */
	class MeetingNote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $session_id
 * @property string $rrule
 * @property \Carbon\CarbonImmutable $starts_at
 * @property string $timezone
 * @property string $default_title
 * @property string|null $default_location
 * @property int $default_duration_minutes
 * @property \Carbon\CarbonImmutable|null $generated_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $meetings
 * @property-read int|null $meetings_count
 * @property-read \App\Models\Session|null $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereDefaultDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereDefaultLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereDefaultTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereRrule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingSchedule whereUpdatedAt($value)
 */
	class MeetingSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperMembership
 * @property int $id
 * @property int $user_id
 * @property int $tontine_id
 * @property string $member_number
 * @property \App\Enums\MembershipStatus $status
 * @property \Carbon\CarbonImmutable $verified_at
 * @property \Carbon\CarbonImmutable $joined_at
 * @property \Carbon\CarbonImmutable|null $left_at
 * @property int|null $invited_by
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $inviter
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $sessionParticipations
 * @property-read int|null $session_participations_count
 * @property-read \App\Models\Tontine|null $tontine
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership active()
 * @method static \Database\Factories\MembershipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereInvitedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereLeftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereMemberNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereTontineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership withoutTrashed()
 */
	class Membership extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $meeting_id
 * @property int $draw_entry_id
 * @property numeric $amount
 * @property \App\Enums\PayoutStatus $status
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\DrawEntry $drawEntry
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\PayoutFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereDrawEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereUpdatedAt($value)
 */
	class Payout extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tontine_id
 * @property string $code
 * @property string $name
 * @property \App\Enums\PenaltyTrigger $trigger
 * @property \App\Enums\PenaltyCalculationType $calculation_type
 * @property numeric|null $value
 * @property int|null $grace_period
 * @property \App\Enums\PenaltyGraceUnit|null $grace_unit
 * @property bool $is_automatic
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Tontine|null $tontine
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereCalculationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereGracePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereGraceUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereIsAutomatic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereTontineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereTrigger($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenaltyRule whereValue($value)
 */
	class PenaltyRule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $loan_id
 * @property numeric $amount
 * @property numeric $interest_amount
 * @property numeric $principal_amount
 * @property \Carbon\CarbonImmutable $paid_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Loan $loan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereLoanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment wherePrincipalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereUpdatedAt($value)
 */
	class Repayment extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperSession
 * @property int $id
 * @property int $tontine_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $default_contribution_amount
 * @property int $beneficiaries_per_meeting
 * @property \App\Enums\DrawAllocationMode $draw_allocation_mode
 * @property int|null $base_contribution_amount
 * @property \Carbon\CarbonImmutable|null $start_at
 * @property \Carbon\CarbonImmutable|null $end_at
 * @property \App\Enums\SessionStatus $status
 * @property \Carbon\CarbonImmutable|null $activated_at
 * @property \Carbon\CarbonImmutable|null $closed_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $activeParticipants
 * @property-read int|null $active_participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $completedMeetings
 * @property-read int|null $completed_meetings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Donation> $donations
 * @property-read int|null $donations_count
 * @property-read \App\Models\Draw|null $draw
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InsuranceContribution> $insuranceContributions
 * @property-read int|null $insurance_contributions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Loan> $loans
 * @property-read int|null $loans_count
 * @property-read \App\Models\MeetingSchedule|null $meetingSchedule
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $meetings
 * @property-read int|null $meetings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $sessionParticipations
 * @property-read int|null $session_participations_count
 * @property-read \App\Models\Tontine|null $tontine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\SessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereBaseContributionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereBeneficiariesPerMeeting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereDefaultContributionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereDrawAllocationMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereTontineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session withoutTrashed()
 */
	class Session extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperSessionParticipant
 * @property int $id
 * @property int $session_id
 * @property int $membership_id
 * @property int $contribution_amount
 * @property-read int|null $draw_entries_count
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable $joined_at
 * @property \Carbon\CarbonImmutable|null $left_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DrawEntry> $drawEntries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingAttendance> $meetingAttendances
 * @property-read int|null $meeting_attendances_count
 * @property-read \App\Models\Membership|null $membership
 * @property-read \App\Models\Session|null $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant active()
 * @method static \Database\Factories\SessionParticipantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereContributionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereDrawEntriesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereLeftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereMembershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionParticipant whereUpdatedAt($value)
 */
	class SessionParticipant extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperTontine
 * @property int $id
 * @property int $user_id
 * @property int $next_member_number
 * @property string $member_number_prefix
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property bool $is_public
 * @property bool $is_verified
 * @property string $currency
 * @property int|null $default_contribution_amount
 * @property numeric $default_loan_interest_rate
 * @property int $default_loan_term_months
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Session|null $activeSession
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Membership> $memberships
 * @property-read int|null $memberships_count
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PenaltyRule> $penaltyRules
 * @property-read int|null $penalty_rules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Session> $sessions
 * @property-read int|null $sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine accessibleBy(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine active()
 * @method static \Database\Factories\TontineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereDefaultContributionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereDefaultLoanInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereDefaultLoanTermMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereMemberNumberPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereNextMemberNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine withoutTrashed()
 */
	class Tontine extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $session_id
 * @property int|null $membership_id
 * @property string|null $transactionable_type
 * @property int|null $transactionable_id
 * @property \App\Enums\TransactionType $type
 * @property \App\Enums\TransactionDirection $direction
 * @property numeric $amount
 * @property string|null $description
 * @property \Carbon\CarbonImmutable $occurred_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Membership|null $membership
 * @property-read \App\Models\Session|null $session
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $transactionable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction credits()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction debits()
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMembershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string|null $first_name
 * @property string $email
 * @property string $username
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Carbon\CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Membership> $memberships
 * @property-read int|null $memberships_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tontine> $ownedTontines
 * @property-read int|null $owned_tontines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passkeys\Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tontine> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tontine> $tontines
 * @property-read int|null $tontines_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 */
	class User extends \Eloquent implements \Laravel\Fortify\Contracts\PasskeyUser, \Laravel\Passkeys\Contracts\PasskeyUser {}
}

