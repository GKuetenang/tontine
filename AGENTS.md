# AGENTS.md

## 1. Scope

These instructions apply to the entire Group repository unless a more specific `AGENTS.md` exists deeper in the tree.

Read `PROJECT_CONTEXT.md` before doing domain work.

The repository is the implementation source of truth. `PROJECT_CONTEXT.md` captures architectural and business decisions that guide interpretation of the code.

## 2. Primary Objective

Make small, correct, test-backed changes that preserve existing architecture and naming conventions.

Do not redesign unrelated parts of the application while implementing a requested feature.

Before writing code:
1. inspect relevant existing files;
2. identify current patterns;
3. identify affected tests;
4. state any material mismatch between requested behavior and current implementation.

## 3. Development Philosophy

Use TDD where practical.

Preferred cycle:

```text
Inspect
  -> write/fix focused test
  -> run test
  -> implement smallest correct change
  -> rerun focused test
  -> refactor
  -> run related suite
```

Avoid broad speculative changes.

## 4. Laravel Responsibilities

### Controllers
Controllers should:
- resolve route models;
- authorize;
- call Actions/services;
- eager-load required relations;
- return Inertia responses or redirects.

Controllers should not contain large business workflows.

### Policies
Policies handle authorization only.

Policies may answer:
- does this user belong to the relevant group?
- does this user have the required permission?

Policies should not decide:
- whether a Draw is confirmed;
- whether a Meeting is in the correct state;
- whether a Payout is already paid;
- whether a state transition is valid.

Those rules belong in Actions.

### Actions
Actions own business behavior and state transitions.

Examples:
- generate Draw;
- swap DrawEntries;
- confirm Draw;
- create Payout;
- update Payout;
- pay Payout;
- cancel Payout.

Use transactions and locking for concurrency-sensitive financial or ordering operations.

### FormRequests
FormRequests validate request shape and scalar constraints.

Cross-aggregate business rules remain in Actions.

### Data DTOs
Spatie Data DTOs serialize already-loaded data.

Do not introduce hidden N+1 queries from DTO constructors or `fromModel()` methods.

## 5. Database Rules

Prefer database constraints for invariants.

Examples:
- unique Draw positions;
- unique DrawEntry participant/part combination;
- unique Payout `draw_entry_id`.

Application checks should still provide useful validation errors, but database constraints are the final guard.

For money:
- never use float;
- never use double;
- use DECIMAL;
- preserve decimal values as strings at serialization boundaries when appropriate.

## 6. Financial Safety

Payout and Transaction code is financially sensitive.

When changing payment code:
- use DB transactions;
- use `lockForUpdate()` for payout state mutation;
- prevent duplicate payment;
- assert exactly one financial movement;
- avoid floating-point arithmetic;
- preserve auditability;
- do not hard-delete paid financial records.

Payout and Transaction are distinct:

```text
Payout
  -> business obligation / beneficiary event

Transaction
  -> actual money movement
```

## 7. Authorization and Spatie Teams

Spatie Permission Teams is scoped by `group_id`.

When checking permissions:
- set current permissions team ID;
- clear cached user roles/permissions;
- restore previous team ID afterward;
- clear relations again.

Reuse the repository's existing helper pattern.

## 8. Routing

Preserve nested scoped route model binding.

Prefer existing slug conventions.

Do not replace generated Wayfinder route helpers with manually constructed URLs in React.

## 9. Frontend Rules

Use the existing stack:
- React;
- TypeScript;
- Inertia;
- shadcn/ui;
- Tailwind;
- Wayfinder;
- existing authorization hooks;
- existing app layout.

Prefer small focused components over monolithic pages.

Do not introduce a new global state library for local feature state.

## 10. Draw Rules

Draw position changes use swap semantics.

Dragging A onto B means only A and B exchange positions.

Do not use list reorder semantics.

A confirmed Draw is read-only.

Payout eligibility requires a confirmed Draw.

Expected payout meeting is derived from Draw position and Session configuration, not persisted on DrawEntry.

## 11. Meeting and Draw Calendar Rules

`Meeting.number` is the stable mapping key.

Do not derive expected slots from chronological sorting unless the user explicitly changes this rule.

Formula:

```php
intdiv(
    $position - 1,
    $session->beneficiaries_per_meeting,
) + 1
```

Do not store expected payout dates in `draw_entries`.

Use `Meeting.scheduled_at` as date source of truth.

## 12. Payout Rules

Current business rules:

```text
one Meeting -> many Payouts
one DrawEntry -> maximum one Payout
```

Payout fields include:
- meeting;
- draw entry;
- decimal amount;
- status;
- paid_at;
- created_by.

Statuses:
- pending;
- paid;
- cancelled.

Allowed transitions:

```text
pending -> paid
pending -> cancelled
```

Pending payouts may be updated.

Paid and cancelled payouts are read-only unless a future explicit workflow is introduced.

Actual beneficiary is represented by the selected DrawEntry.

Do not assume expected beneficiary must always be actual beneficiary.

## 13. Payout Candidate Rules

For a Meeting:
- `expected` comes from DrawCalendar;
- `available` contains unpaid DrawEntries from confirmed Draw;
- expected candidates can also be available and marked `expected = true`;
- once a DrawEntry has a Payout, remove it from expected and available;
- no confirmed Draw means no payout candidates.

## 14. Tests

Use Pest.

Write focused tests for:
- Actions;
- business invariants;
- database constraints;
- HTTP validation;
- authorization where needed;
- serializers when derived output matters.

For Payout, preserve coverage for:
- confirmed Draw requirement;
- same Session requirement;
- duplicate prevention;
- multiple payouts on one Meeting;
- decimal amount;
- update restrictions;
- cancellation restrictions;
- payment transaction creation;
- double-payment protection;
- payout context behavior.

Avoid dynamic `$this->foo` Pest fixture properties when possible because of Intelephense P1014 warnings.

Prefer typed local setup helpers.

## 15. Factories

Reuse existing factories.

Before creating a factory, search for one.

Prefer factory states such as:

```php
Payout::factory()
    ->paid()
```

instead of repeating status setup.

Keep default factory states valid according to domain invariants.

## 16. TypeScript Types

Spatie Data is the source for generated domain TypeScript types where already used.

After DTO changes:
- run the existing generation command;
- inspect generated diff;
- do not manually edit generated files unless that is already repository convention.

## 17. Date and Time Handling

The project has already encountered timezone drift between Meeting dates and Draw expected dates.

When changing date serialization:
- inspect existing DTO transformer conventions;
- use the same convention everywhere;
- do not independently serialize dates in a way that changes local wall-clock values;
- test midnight and non-midnight values when timezone behavior matters.

## 18. Internationalization

Use the existing translation system.

Backend user-facing messages:

```php
__('...')
```

Do not introduce untranslated server messages.

Follow the existing frontend translation pattern.

## 19. Code Style

Match repository style.

General preferences:
- explicit imports;
- typed signatures;
- named arguments for complex calls;
- small methods;
- descriptive variables;
- avoid clever abstractions unless repeated behavior justifies them;
- avoid premature generic repositories/services.

Do not rename established domain concepts without approval.

## 20. Before Editing an Existing Feature

Search for:
- model;
- migration;
- enum;
- policy;
- FormRequest;
- Action;
- controller;
- route;
- Data DTO;
- React component;
- factory;
- test.

Do not assume a file is missing because it was not mentioned in the prompt.

## 21. Handling Mismatches

If code conflicts with `PROJECT_CONTEXT.md`:

1. inspect surrounding implementation;
2. determine whether code is newer than context;
3. preserve intentional existing implementation;
4. flag material business-rule conflicts;
5. avoid destructive changes until discrepancy is understood.

## 22. Commands

Use repository-installed tooling.

Typical Laravel test commands:

```bash
php artisan test --filter=Payout
```

```bash
php artisan test tests/Feature/Payouts
```

```bash
php artisan test
```

For frontend:
- use pnpm;
- inspect `package.json` before assuming script names.

For TypeScript generation:
- inspect current Artisan commands/project scripts before assuming exact syntax.

## 23. Completion Criteria

A feature is not complete merely because the UI works.

Before considering work complete:
- relevant tests pass;
- database invariants are preserved;
- authorization is enforced;
- business rules live in Actions;
- TypeScript types are synchronized;
- no obvious N+1 query was introduced;
- no unnecessary duplicate domain data was persisted;
- frontend handles empty/pending/read-only states;
- user-facing errors are translated;
- formatting/linting follows repository conventions.

## 24. Current Work Priority

Current module:

```text
Payout
```

Before continuing:
1. read `PROJECT_CONTEXT.md`;
2. inspect current Payout implementation;
3. inspect DrawCalendar;
4. inspect Transaction model;
5. inspect Meeting show page;
6. inspect existing tests;
7. run focused Payout tests;
8. continue from actual failing/missing behavior.

Do not rebuild the Payout module from scratch if partial implementation already exists.
