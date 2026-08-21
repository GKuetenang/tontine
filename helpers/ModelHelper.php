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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDraw {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $draw_id
 * @property int $session_participant_id
 * @property int $position
 * @property int $entry_number
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Draw|null $draw
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDrawEntry {}
}

namespace App\Models{
/**
 * @property \App\Enums\MeetingStatus $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingAgendaItem> $agendaItems
 * @property-read int|null $agenda_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeetingAttendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Session|null $session
 * @method static \Database\Factories\MeetingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMeeting {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Meeting|null $meeting
 * @method static \Database\Factories\MeetingAgendaItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAgendaItem query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMeetingAgendaItem {}
}

namespace App\Models{
/**
 * @property \App\Enums\AttendanceStatus $status
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \App\Models\SessionParticipant|null $sessionParticipant
 * @method static \Database\Factories\MeetingAttendanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingAttendance query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMeetingAttendance {}
}

namespace App\Models{
/**
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMembership {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tontine_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $default_contribution_amount
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
 * @property-read \App\Models\Draw|null $draw
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $meetings
 * @property-read int|null $meetings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionParticipant> $sessionParticipations
 * @property-read int|null $session_participations_count
 * @property-read \App\Models\Tontine|null $tontine
 * @method static \Database\Factories\SessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereBaseContributionAmount($value)
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSession {}
}

namespace App\Models{
/**
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSessionParticipant {}
}

namespace App\Models{
/**
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTontine {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

