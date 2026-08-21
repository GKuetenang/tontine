<?php

use App\Actions\MeetingAttendances\UpdateMeetingAttendanceAction;
use App\Enums\AttendanceStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use Illuminate\Validation\ValidationException;

test('an attendance can be marked present during a meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $attendance = MeetingAttendance::factory()
        ->for($meeting)
        ->create([
            'status' => AttendanceStatus::Pending,
        ]);

    $attendance = app(UpdateMeetingAttendanceAction::class)
        ->execute(
            attendance: $attendance,
            status: AttendanceStatus::Present,
        );

    expect($attendance)
        ->status->toBe(AttendanceStatus::Present)
        ->checked_in_at->not->toBeNull();
});

test('an attendance can be marked late', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $attendance = MeetingAttendance::factory()
        ->for($meeting)
        ->create();

    $attendance = app(UpdateMeetingAttendanceAction::class)
        ->execute(
            attendance: $attendance,
            status: AttendanceStatus::Late,
        );

    expect($attendance)
        ->status->toBe(AttendanceStatus::Late)
        ->checked_in_at->not->toBeNull();
});

test('an absent attendance does not have a check in time', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $attendance = MeetingAttendance::factory()
        ->for($meeting)
        ->present()
        ->create();

    $attendance = app(UpdateMeetingAttendanceAction::class)
        ->execute(
            attendance: $attendance,
            status: AttendanceStatus::Absent,
        );

    expect($attendance)
        ->status->toBe(AttendanceStatus::Absent)
        ->checked_in_at->toBeNull();
});

test('attendance cannot be changed before the meeting starts', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $attendance = MeetingAttendance::factory()
        ->for($meeting)
        ->create();

    app(UpdateMeetingAttendanceAction::class)
        ->execute(
            attendance: $attendance,
            status: AttendanceStatus::Present,
        );
})->throws(ValidationException::class);
