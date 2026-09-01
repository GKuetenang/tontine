<?php

use App\Actions\Meetings\BuildMeetingOccurrencesAction;
use App\Actions\Meetings\GenerateRecurringMeetingsAction;
use App\Actions\Meetings\UpdateRecurringMeetingsAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingSchedule;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('derives the recurrence preset from the persisted rrule', function (): void {
    expect(MeetingRecurrence::fromRRule('FREQ=MONTHLY;INTERVAL=1;BYDAY=1SU'))
        ->toBe(MeetingRecurrence::Monthly)
        ->and(MeetingRecurrence::fromRRule('FREQ=WEEKLY;INTERVAL=2'))
        ->toBe(MeetingRecurrence::Weekly)
        ->and(MeetingRecurrence::fromRRule('FREQ=WEEKLY;INTERVAL=1'))
        ->toBe(MeetingRecurrence::Weekly);
});

it('preserves the local meeting time across daylight saving changes', function (): void {
    $timezone = 'America/Toronto';
    $occurrences = app(BuildMeetingOccurrencesAction::class)->execute(
        rrule: 'FREQ=WEEKLY;INTERVAL=1',
        startsAt: CarbonImmutable::parse('2027-03-07 10:00:00', $timezone),
        endsAt: CarbonImmutable::parse('2027-03-31 23:59:59', $timezone),
    );

    expect(array_map(
        fn (CarbonImmutable $occurrence): string => $occurrence->format('Y-m-d H:i P'),
        $occurrences,
    ))->toBe([
        '2027-03-07 10:00 -05:00',
        '2027-03-14 10:00 -04:00',
        '2027-03-21 10:00 -04:00',
        '2027-03-28 10:00 -04:00',
    ]);
});

it('generates and persists weekly meetings inside the session dates', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01 00:00:00',
        'end_at' => '2027-01-31 23:59:59',
    ]);
    $creator = User::factory()->create();

    $schedule = app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: $creator,
        recurrence: MeetingRecurrence::Weekly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'America/Toronto',
        defaultTitle: 'Réunion hebdomadaire',
        defaultLocation: 'Siège social',
        defaultDurationMinutes: 120,
    );

    expect($schedule->rrule)->toBe('FREQ=WEEKLY;INTERVAL=1')
        ->and($session->meetings()->count())->toBe(5)
        ->and($session->meetings()->pluck('number')->all())->toBe([1, 2, 3, 4, 5])
        ->and($session->meetings()->pluck('location')->unique()->all())->toBe(['Siège social'])
        ->and($session->meetings()->pluck('duration_minutes')->unique()->all())->toBe([120]);
});

it('generates meetings every three weeks', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01 00:00:00',
        'end_at' => '2027-03-31 23:59:59',
    ]);

    $schedule = app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Weekly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion',
        defaultLocation: null,
        defaultDurationMinutes: 60,
        interval: 3,
    );

    expect($schedule->rrule)->toBe('FREQ=WEEKLY;INTERVAL=3')
        ->and($session->meetings()->orderBy('number')->pluck('scheduled_at')->map->format('Y-m-d')->all())
        ->toBe([
            '2027-01-03',
            '2027-01-24',
            '2027-02-14',
            '2027-03-07',
            '2027-03-28',
        ]);
});

it('generates monthly meetings without overflowing the day of month', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01 00:00:00',
        'end_at' => '2027-04-30 23:59:59',
    ]);

    $schedule = app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-31 18:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion mensuelle',
        defaultLocation: null,
        defaultDurationMinutes: 90,
    );

    expect($schedule->rrule)->toBe('FREQ=MONTHLY;INTERVAL=1;BYMONTHDAY=-1')
        ->and($session->meetings()->orderBy('number')->pluck('scheduled_at')->map->format('Y-m-d')->all())
        ->toBe(['2027-01-31', '2027-02-28', '2027-03-31', '2027-04-30']);
});

it('generates the same ordinal weekday for monthly meetings', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01 00:00:00',
        'end_at' => '2027-04-30 23:59:59',
    ]);

    $schedule = app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Premier dimanche',
        defaultLocation: null,
        defaultDurationMinutes: 90,
        monthlyPattern: MeetingMonthlyPattern::WeekdayOrdinal,
    );

    expect($schedule->rrule)->toBe('FREQ=MONTHLY;INTERVAL=1;BYDAY=1SU')
        ->and($session->meetings()->orderBy('number')->pluck('scheduled_at')->map->format('Y-m-d')->all())
        ->toBe(['2027-01-03', '2027-02-07', '2027-03-07', '2027-04-04']);
});

it('generates meetings every three months', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01 00:00:00',
        'end_at' => '2027-12-31 23:59:59',
    ]);

    $schedule = app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion trimestrielle',
        defaultLocation: null,
        defaultDurationMinutes: 90,
        monthlyPattern: MeetingMonthlyPattern::WeekdayOrdinal,
        interval: 3,
    );

    expect($schedule->rrule)->toBe('FREQ=MONTHLY;INTERVAL=3;BYDAY=1SU')
        ->and($session->meetings()->orderBy('number')->pluck('scheduled_at')->map->format('Y-m-d')->all())
        ->toBe([
            '2027-01-03',
            '2027-04-04',
            '2027-07-04',
            '2027-10-03',
        ]);
});

it('does not duplicate meetings when the same schedule generation is retried', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-02-28 23:59:59',
    ]);
    $creator = User::factory()->create();
    $arguments = [
        'session' => $session,
        'creator' => $creator,
        'recurrence' => MeetingRecurrence::Weekly,
        'startsAt' => '2027-01-03 10:00:00',
        'timezone' => 'UTC',
        'defaultTitle' => 'Réunion bimensuelle',
        'defaultLocation' => null,
        'defaultDurationMinutes' => 60,
        'interval' => 2,
    ];

    app(GenerateRecurringMeetingsAction::class)->execute(...$arguments);
    app(GenerateRecurringMeetingsAction::class)->execute(...$arguments);

    expect(MeetingSchedule::query()->count())->toBe(1)
        ->and($session->meetings()->count())->toBe(5);
});

it('updates generated meetings globally while preserving their stable identity', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-03-31 23:59:59',
    ]);
    $creator = User::factory()->create();

    app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: $creator,
        recurrence: MeetingRecurrence::Weekly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion',
        defaultLocation: 'Ancien lieu',
        defaultDurationMinutes: 60,
    );
    $firstMeeting = $session->meetings()->orderBy('number')->firstOrFail();

    $schedule = app(UpdateRecurringMeetingsAction::class)->execute(
        session: $session,
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-10 14:00:00',
        timezone: 'America/Toronto',
        defaultTitle: 'Assemblée',
        defaultLocation: 'Nouveau lieu',
        defaultDurationMinutes: 120,
    );

    expect($schedule->rrule)->toBe('FREQ=MONTHLY;INTERVAL=1')
        ->and($session->meetings()->count())->toBe(3)
        ->and($session->meetings()->orderBy('number')->firstOrFail()->id)->toBe($firstMeeting->id)
        ->and($session->meetings()->pluck('location')->unique()->all())->toBe(['Nouveau lieu'])
        ->and($session->meetings()->pluck('duration_minutes')->unique()->all())->toBe([120]);
});

it('does not remove a generated meeting containing business data during a global update', function (): void {
    $session = Session::factory()->draft()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-01-31 23:59:59',
    ]);

    app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Weekly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion',
        defaultLocation: null,
        defaultDurationMinutes: 60,
    );
    MeetingAgendaItem::factory()->for(
        $session->meetings()->orderByDesc('number')->firstOrFail(),
    )->create();

    expect(fn () => app(UpdateRecurringMeetingsAction::class)->execute(
        session: $session,
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-03 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Assemblée',
        defaultLocation: null,
        defaultDurationMinutes: 90,
    ))->toThrow(ValidationException::class);

    expect($session->meetings()->count())->toBe(5)
        ->and($session->meetingSchedule->rrule)->toBe('FREQ=WEEKLY;INTERVAL=1');
});

it('does not generate a recurring schedule for an active session', function (): void {
    $session = Session::factory()->active()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-03-31',
    ]);

    expect(fn () => app(GenerateRecurringMeetingsAction::class)->execute(
        session: $session,
        creator: User::factory()->create(),
        recurrence: MeetingRecurrence::Monthly,
        startsAt: '2027-01-10 10:00:00',
        timezone: 'UTC',
        defaultTitle: 'Réunion',
        defaultLocation: null,
        defaultDurationMinutes: 60,
    ))->toThrow(ValidationException::class);
});

it('allows an authorized user to configure and generate a meeting calendar', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id, 'slug' => 'tontine-test']);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->draft()->create([
        'slug' => 'session-2027',
        'start_at' => '2027-01-01',
        'end_at' => '2027-03-31 23:59:59',
    ]);

    $this->actingAs($president)
        ->post(route('tontines.sessions.meeting-schedule.store', [$tontine, $session]), [
            'recurrence' => MeetingRecurrence::Monthly->value,
            'interval' => 1,
            'starts_at' => '2027-01-10 10:00:00',
            'timezone' => 'America/Toronto',
            'default_title' => 'Réunion mensuelle',
            'default_location' => 'Montréal',
            'default_duration_minutes' => 90,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($session->meetingSchedule)->not->toBeNull()
        ->and($session->meetings()->count())->toBe(3);

    $this->put(route('tontines.sessions.meeting-schedule.update', [$tontine, $session]), [
        'recurrence' => MeetingRecurrence::Weekly->value,
        'interval' => 2,
        'starts_at' => '2027-01-10 14:00:00',
        'timezone' => 'Africa/Douala',
        'default_title' => 'Réunion générale',
        'default_location' => 'Yaoundé',
        'default_duration_minutes' => 120,
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($session->meetingSchedule()->firstOrFail()->rrule)
        ->toBe('FREQ=WEEKLY;INTERVAL=2')
        ->and($session->meetings()->count())->toBe(6)
        ->and($session->meetings()->pluck('location')->unique()->all())
        ->toBe(['Yaoundé']);
});
