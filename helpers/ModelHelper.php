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
 * @property-read \App\Models\Tontine|null $tontine
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership active()
 * @method static \Database\Factories\MembershipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership onlyTrashed()
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
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Membership> $memberships
 * @property-read int|null $memberships_count
 * @property-read \App\Models\User|null $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine active()
 * @method static \Database\Factories\TontineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine orderFromRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tontine whereCurrency($value)
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

