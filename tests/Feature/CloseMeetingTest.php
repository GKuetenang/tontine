<?php

use App\Actions\Meetings\CloseMeetingAction;
use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use Illuminate\Validation\ValidationException;

test('an in progress meeting can be closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $meeting = app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($meeting)
        ->status->toBe(MeetingStatus::Completed)
        ->closed_at->not->toBeNull();
});

test('pending attendances become absent when meeting is closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $pending = MeetingAttendance::factory()
        ->for($meeting)
        ->create([
            'status' => AttendanceStatus::Pending,
        ]);

    app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($pending->refresh())
        ->status->toBe(AttendanceStatus::Absent)
        ->checked_in_at->toBeNull();
});

test('existing attendance statuses are preserved when meeting is closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $present = MeetingAttendance::factory()
        ->for($meeting)
        ->present()
        ->create();

    $late = MeetingAttendance::factory()
        ->for($meeting)
        ->late()
        ->create();

    $excused = MeetingAttendance::factory()
        ->for($meeting)
        ->excused()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($present->refresh()->status)
        ->toBe(AttendanceStatus::Present)
        ->and($late->refresh()->status)
        ->toBe(AttendanceStatus::Late)
        ->and($excused->refresh()->status)
        ->toBe(AttendanceStatus::Excused);
});

test('a scheduled meeting cannot be closed', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a completed meeting cannot be closed again', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a cancelled meeting cannot be closed', function (): void {
    $meeting = Meeting::factory()
        ->cancelled()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);
