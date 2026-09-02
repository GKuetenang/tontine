# Group — Project Context

## 1. Purpose

Group is a Laravel + Inertia + React application for managing groups.

This document captures the current domain model, architectural decisions, implementation conventions, and the current state of development so that a new coding agent can continue the project without re-deriving the same decisions.

The repository itself remains the source of truth. Before changing code, inspect the existing implementation and preserve current names, patterns, namespaces, routes, DTOs, factories, enums, and component structure.

## 2. Technology Stack

### Backend
- PHP 8.4
- Laravel 13
- Laravel Breeze
- Laravel Fortify
- Pest
- Spatie Laravel Permission with Teams
- Spatie Laravel Data
- Spatie TypeScript Transformer
- Spatie Media Library
- Laravel Lang

### Frontend
- React
- Inertia.js
- TypeScript
- Tailwind CSS
- shadcn/ui
- Wayfinder-generated routes
- Tiptap for rich text
- DOMPurify for rich-text rendering
- pnpm

## 3. General Architecture

Typical request flow:

```text
Request
  -> FormRequest
  -> Controller
  -> Policy authorization
  -> Action
  -> Model / database
  -> Data DTO
  -> Inertia
  -> React
```

Responsibilities:

```text
Policy
  -> authorization only

Action
  -> business rules
  -> state transitions
  -> transactional writes

FormRequest
  -> input validation

Data DTO
  -> serialization only
  -> no hidden database queries

Controller
  -> orchestration
  -> authorization
  -> eager loading
  -> call Actions
  -> return Inertia response

Model
  -> relationships
  -> casts
  -> persistence behavior
```

Do not move business state rules into Policies.

## 4. Coding Conventions

### Laravel

Prefer named arguments for non-trivial method calls.

```php
$action->execute(
    payout: $payout,
    user: $request->user(),
);
```

Use translated user-facing messages:

```php
__('Le versement a été créé avec succès.')
```

Flash convention:

```php
return Inertia::flash(
    'success',
    __('Le versement a été créé avec succès.'),
)->back();
```

### Routing

Use slugs for domain resources where already implemented and scoped bindings for nested routes.

```text
/groups/{group:slug}
/sessions/{session:slug}
/meetings/{meeting:slug}
```

### TDD

Use Pest.

Preferred workflow:

```text
inspect -> write focused test -> run -> implement -> rerun -> refactor -> related suite
```

Avoid dynamic Pest properties such as `$this->payout` when possible because Intelephense reports P1014. Prefer local variables or typed setup helpers.

## 5. Permissions and Group Teams

The project uses Spatie Laravel Permission with Teams.

Team key:

```text
group_id
```

Recommended helper pattern:

```php
private function can(
    User $user,
    Group $group,
    GroupPermission $permission,
): bool {
    $previousTeamId = getPermissionsTeamId();

    try {
        setPermissionsTeamId($group->id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->can(
            $permission->value,
        );
    } finally {
        setPermissionsTeamId($previousTeamId);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
    }
}
```

## 6. Main Domain Structure

```text
Group
  -> Memberships
  -> Sessions
      -> SessionParticipants
      -> Meetings
          -> Agenda
          -> Attendance
          -> Contributions
          -> Payouts
          -> Notes
          -> Decisions
      -> Draw
          -> DrawEntries
      -> Transactions
```

## 7. Group

Important concepts:
- name
- slug
- description
- creator / owner
- is_public
- is_active
- is_verified

Slug is unique. Do not assume group name must be globally unique unless the current schema explicitly enforces it.

The creator is automatically represented in membership and normally has the president role.

## 8. Membership

Membership is group-scoped.

Important rules:
- no duplicate membership for the same user in the same group;
- creator membership is created automatically as president;
- last president cannot be deactivated;
- deactivation removes team roles and soft-deletes membership;
- `verified_at` is retained;
- default status is Active;
- member number uses a 12-digit range.

## 9. Session

The actual table name is:

```text
group_sessions
```

Do not use `sessions` for this domain table.

Important field:

```text
beneficiaries_per_meeting
```

Recommended schema:

```php
$table
    ->unsignedSmallInteger('beneficiaries_per_meeting')
    ->default(1);
```

Existing rows must not remain NULL.

Model cast:

```php
'beneficiaries_per_meeting' => 'integer',
```

DTO can defensively use:

```php
$session->beneficiaries_per_meeting ?? 1
```

## 10. Meeting

Meeting has:
- stable `number`;
- `scheduled_at`;
- status.

Statuses:

```php
Scheduled = 'scheduled'
InProgress = 'in_progress'
Completed = 'completed'
Cancelled = 'cancelled'
```

`Meeting.number` is currently treated as a stable business sequence key.

Meeting show includes modules/tabs such as:
- overview;
- agenda;
- attendance;
- contributions;
- payouts;
- notes;
- decisions.

## 11. Rich Text / Meeting Notes

Meeting Notes use:
- Tiptap;
- Base64 images;
- image resize;
- DOMPurify.

Note content should use LONGTEXT or equivalent because embedded Base64 content can be large.

## 12. Meeting Decisions

MeetingDecision fields:

```text
meeting_id
meeting_agenda_item_id nullable
title
description nullable
created_by nullable
timestamps
```

A decision can be general or attached to an agenda item.

Lifecycle expectation:
- Scheduled: no editing workflow;
- In Progress: CRUD according to permissions;
- Completed / Cancelled: read-only.

## 13. Draw / Tirage

Current `draw_entries` structure:

```php
Schema::create('draw_entries', function (Blueprint $table) {
    $table->id();

    $table->foreignIdFor(Draw::class)
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignIdFor(SessionParticipant::class)
        ->constrained()
        ->restrictOnDelete();

    $table->unsignedInteger('position');

    $table->unsignedSmallInteger('entry_number')
        ->default(1);

    $table->timestamps();

    $table->unique([
        'draw_id',
        'position',
    ]);

    $table->unique([
        'draw_id',
        'session_participant_id',
        'entry_number',
    ]);
});
```

There is no `session_id` in `draw_entries`.

Session is reached through:

```text
DrawEntry -> Draw -> Session
```

Current permission family includes:
- draws.view
- draws.generate
- draws.update
- draws.reset
- draws.delete
- draws.confirm

Authorization shape:

```text
view(User, Session)
generate(User, Session)

update(User, Draw)
reset(User, Draw)
confirm(User, Draw)
delete(User, Draw)
```

Lifecycle:

```text
No Draw
  -> generate

Generated but not confirmed
  -> swap
  -> reset
  -> delete
  -> confirm

Confirmed
  -> read-only
  -> eligible for payouts
```

## 14. Draw Drag-and-Drop Swap

Dragging position 1 onto position 7 means only 1 and 7 exchange positions.

Do not use reorder semantics.

Frontend:
- DndContext
- useDraggable
- useDroppable
- DragOverlay

Avoid `arrayMove()`.

Request payload:

```json
{
  "source_entry_id": 1,
  "target_entry_id": 7
}
```

Backend must verify both entries belong to the current Draw.

Because `(draw_id, position)` is unique and position is unsigned, use a temporary positive position:

```php
$temporaryPosition = DrawEntry::query()
    ->where('draw_id', $draw->id)
    ->max('position') + 1;
```

Use transaction + `lockForUpdate()`.

Optimistic frontend swap must swap both `position` and `expected_meeting`.

## 15. Draw Calendar

Do not store calculated payout dates in `draw_entries`.

Date source of truth:

```text
Meeting.scheduled_at
```

Session defines `beneficiaries_per_meeting`, default 1.

Mapping formula:

```php
$meetingNumber =
    intdiv(
        $position - 1,
        $session->beneficiaries_per_meeting,
    ) + 1;
```

Example with 2 beneficiaries per meeting:

```text
positions 1,2 -> Meeting #1
positions 3,4 -> Meeting #2
positions 5,6 -> Meeting #3
```

Use `Meeting.number` as the mapping key.

If there is no corresponding meeting:

```text
expected_meeting = null
```

UI should display `Non planifiée`.

DrawCalendar is a read-only resolver/service. DTOs must not query for meetings individually.

## 16. Meeting Date Serialization

A timezone issue was previously observed where expected draw dates were shifted by four hours.

Example:

```text
Meeting page:
15 Sep 2026 00:00

Draw page:
14 Sep 2026 20:00
```

The issue was serialization/browser timezone conversion, not DrawCalendar mapping.

Match the existing date transformer convention across DTOs. Do not introduce inconsistent UTC conversion.

## 17. Payout Domain

Payout is the current module under implementation.

Payout belongs directly to:
- Meeting;
- DrawEntry.

Session is derived from:

```text
Payout -> Meeting -> Session
```

Beneficiary is derived from:

```text
Payout
  -> DrawEntry
      -> SessionParticipant
          -> Membership
              -> User
```

Payout stores the actual selected `draw_entry_id` because the actual beneficiary may differ from the expected beneficiary.

## 18. Payout Schema

Recommended current migration:

```php
Schema::create('payouts', function (Blueprint $table): void {
    $table->id();

    $table->foreignIdFor(Meeting::class)
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignIdFor(DrawEntry::class)
        ->constrained()
        ->restrictOnDelete();

    $table->decimal(
        'amount',
        15,
        2,
    );

    $table->string('status');

    $table->timestamp('paid_at')
        ->nullable();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamps();

    $table->unique('draw_entry_id');

    $table->index([
        'meeting_id',
        'status',
    ]);
});
```

Integrity rule:

```text
one DrawEntry -> maximum one Payout
one Meeting -> many Payouts
```

Do not use a unique `(meeting_id, draw_entry_id)` in place of the global unique `draw_entry_id`.

## 19. Money Handling

Never use float or double for money.

Use:

```php
$table->decimal('amount', 15, 2);
```

Laravel cast:

```php
'amount' => 'decimal:2',
```

DTO type:

```php
public string $amount
```

Serialized example:

```text
"1250.50"
```

Avoid financial arithmetic using JavaScript floating-point numbers.

Existing formatter should support strings:

```ts
export function formatCurrency(
    amount?: number | string | null,
    currency = 'XAF',
    locale = 'fr-FR',
): string {
    if (amount == null) {
        return '—';
    }

    const value =
        typeof amount === 'string'
            ? Number(amount)
            : amount;

    if (!Number.isFinite(value)) {
        return '—';
    }

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}
```

`Number()` is acceptable for display only, not financial computation.

Future direction: currency should likely belong to Group and be inherited by payouts/transactions.

## 20. Payout Status

Current enum:

```php
enum PayoutStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
```

Lifecycle:

```text
Pending -> Paid
Pending -> Cancelled
```

No hard delete in V1.

Paid payouts should not be directly cancelled or edited.

Cancelled payouts still occupy their unique `draw_entry_id`. If reactivation is later required, prefer reopening the same payout rather than creating another.

## 21. Payout Permissions

Intended permissions:

```text
payouts.view
payouts.create
payouts.update
payouts.pay
payouts.cancel
```

No `payouts.delete` in V1.

## 22. Payout Business Rules

Creation requires:
- Meeting in an allowed state;
- DrawEntry belongs to same Session as Meeting;
- Draw is confirmed;
- no existing Payout for DrawEntry;
- amount is valid and positive.

Initial status: `pending`.

Update:
- only pending payout can be modified.

Cancel:
- pending payout can be cancelled;
- paid payout cannot be directly cancelled.

Pay:
- run inside DB transaction;
- use `lockForUpdate()`;
- payout must be pending;
- create exactly one outgoing Transaction;
- associate Transaction to Payout polymorphically;
- associate beneficiary membership;
- associate Session;
- associate actor;
- set status paid;
- set `paid_at`;
- duplicate execution must not create duplicate financial movements.

## 23. Payout and Transaction Separation

Payout is a business obligation/event.

Transaction is actual money movement.

Expected relation:

```text
Payout -> morphMany Transactions
```

Do not conflate Payout with Transaction.

Potential future partial-payment support can use multiple transactions for one payout, but V1 assumes one standard payment workflow.

## 24. Payout Candidate Context

Meeting payout UI needs:

```text
expected
available
```

`expected` = DrawEntries expected for current Meeting according to DrawCalendar.

`available` = unpaid DrawEntries from confirmed Draw that may be selected.

Recommended lightweight DTO:

```text
PayoutCandidateData
```

Fields:

```text
draw_entry_id
position
entry_number
session_participant_id
member_name
expected
```

Recommended context DTO:

```text
MeetingPayoutContextData
```

with:
- expected
- available

Do not overload MeetingData with payout-specific calculations.

## 25. Payout Context Rules

Only a confirmed Draw should produce candidates.

If Draw is absent or unconfirmed:

```text
expected = []
available = []
```

Example:

```text
beneficiaries_per_meeting = 2
Meeting #2
```

Expected positions:

```text
3
4
```

After creating a payout for position 3:
- position 3 disappears from available;
- position 3 disappears from expected;
- position 4 remains expected.

## 26. Payout UI

Payout belongs inside the Meeting page.

Recommended tab order:

```text
Aperçu
Ordre du jour
Présences
Cotisations
Versements
Notes
Décisions
```

UI should:
- show expected beneficiary/beneficiaries;
- allow creation of a payout;
- preselect first expected available beneficiary;
- allow another available beneficiary;
- display payouts for current meeting;
- pending actions: modify, pay, cancel;
- paid/cancelled are read-only in V1.

Suggested frontend structure:

```text
pages/
└── meeting-payouts/
    ├── show.tsx
    ├── form.tsx
    ├── payout-item.tsx
    └── placeholder.tsx
```

## 27. Essential Payout Tests

Creation:
- creates payout for confirmed DrawEntry;
- status pending;
- paid_at null;
- decimal amount preserved;
- unconfirmed Draw rejected;
- DrawEntry from another Session rejected;
- duplicate Payout for same DrawEntry rejected;
- multiple Payouts on same Meeting allowed.

Database:
- unique `draw_entry_id` enforced.

Update:
- pending can update;
- paid cannot update;
- cancelled cannot update.

Cancel:
- pending can cancel;
- paid cannot cancel;
- cancelled remains cancelled if action is idempotent.

Pay:
- pending becomes paid;
- paid_at populated;
- exactly one outgoing Transaction created;
- Transaction amount matches;
- correct Session;
- correct beneficiary Membership;
- polymorphic relation points to Payout;
- actor stored;
- double payment rejected;
- cancelled payout cannot be paid;
- duplicate execution never creates duplicate financial movement.

Context:
- multiple beneficiaries per meeting supported;
- all unpaid entries available;
- expected entries marked expected;
- entries with existing payout excluded;
- other meeting beneficiaries may remain selectable if allowed;
- unconfirmed Draw gives no candidates.

Amount validation:
- `1250.50` accepted;
- more than two decimals rejected;
- zero rejected;
- negative rejected.

## 28. DrawCalendar Tests

Minimum coverage:
- 1 beneficiary per meeting;
- 2 beneficiaries per meeting;
- 3 beneficiaries per meeting;
- positions for Meeting #N;
- entries for Meeting #N;
- missing meeting returns no expected meeting;
- rescheduling scheduled_at does not change mapping because mapping uses Meeting.number.

## 29. Factories

Expected useful factories:
- GroupFactory
- MembershipFactory
- SessionFactory
- SessionParticipantFactory
- MeetingFactory
- DrawFactory
- DrawEntryFactory
- PayoutFactory
- TransactionFactory
- UserFactory

PayoutFactory should ideally have `paid()` and `cancelled()` states.

## 30. TypeScript Generation

After modifying Spatie Data DTOs, regenerate TypeScript types using the existing project command.

Inspect the repository before assuming the exact command. Do not introduce a second generation path.

## 31. Inertia Frontend Conventions

Use:
- generated Wayfinder routes;
- Inertia Form;
- Link;
- router where appropriate;
- shadcn/ui;
- existing `useAuthorization()` hook;
- existing layout conventions.

Do not manually duplicate route strings where generated helpers exist.

## 32. Internationalization

All user-facing Laravel messages should use `__()`.

Preserve the existing frontend translation architecture.

## 33. Current Development State

Current focus:

```text
Payout module
```

Already decided:
- Payout belongs to Meeting and DrawEntry;
- amount uses DECIMAL(15,2);
- one DrawEntry may have only one Payout;
- one Meeting may contain several Payouts;
- Draw must be confirmed before payout creation;
- Payout starts pending;
- payout can be paid or cancelled;
- paid payout creates outgoing Transaction;
- expected beneficiaries come from DrawCalendar;
- actual beneficiary may be another eligible DrawEntry;
- payout UI belongs inside Meeting;
- essential Pest coverage is required.

The next coding agent should inspect which files already exist before adding anything:
- migration;
- model;
- enum;
- permissions;
- policy;
- FormRequests;
- Actions;
- controller;
- routes;
- Data DTOs;
- context builder;
- frontend components;
- factories;
- tests.

Do not recreate existing files blindly.

## 34. Immediate Next Step for Codex

Before modifying anything:

1. Read this file.
2. Read `AGENTS.md`.
3. Inspect the current repository.
4. Locate all Payout-related files.
5. Locate DrawCalendar and DrawEntry implementation.
6. Locate Transaction model and enums.
7. Locate Meeting show page and tabs.
8. Locate current Pest patterns and factories.
9. Compare actual implementation against this document.
10. Report:
   - what already exists;
   - what is missing;
   - what conflicts with this context;
   - the smallest coherent implementation plan.

Then continue using TDD.

## 35. Source-of-Truth Rule

When this document and repository differ:
- preserve existing names and organization unless clearly wrong;
- preserve explicit domain rules unless the user changes them;
- do not silently rewrite architecture based on assumptions;
- identify discrepancies before broad/destructive changes.

The goal is continuity, not wholesale refactoring.