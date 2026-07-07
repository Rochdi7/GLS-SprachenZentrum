# TODO: CRM/Hikvision sync — batched upsert() conversion

**Status:** Not implemented. Deliberately deferred — see reasoning below.
**Scope:** `app/Console/Commands/MirrorCoreCommand.php`, `app/Console/Commands/HikvisionSyncCommand.php`

## Why this wasn't changed blindly

The production-readiness audit flagged per-row `updateOrCreate()` inside `foreach`
loops as a performance bottleneck (up to ~1,000 queries per 500-row page). That's
accurate, but converting to `DB::table(...)->upsert()` is not a safe drop-in
replacement here — three concrete issues block a mechanical conversion:

### 1. `raw_data` is an Eloquent JSON cast; `upsert()` bypasses casts entirely

Every affected model (`CrmClass`, `CrmStudent`, `CrmRegistration`, `CrmAttendance`,
`HikvisionDevice`, `HikvisionPerson`, `HikvisionAttendance`, `HikvisionAlarm`)
stores the full API payload in a `raw_data` JSON column via Eloquent's array/JSON
cast. `Model::updateOrCreate()` goes through Eloquent, so the cast handles
serialization automatically. `DB::table(...)->upsert()` writes through the query
builder directly — every `raw_data` value would need manual `json_encode()`
before the batch insert, and any code elsewhere reading `raw_data` back via
`json_decode` vs. relying on the Eloquent cast needs to stay consistent. Not
hard, but it's a real behavior change to verify, not a mechanical find-replace.

### 2. `crm_registrations` has two overlapping unique constraints, one nullable

`database/migrations/2026_06_02_110005_create_crm_mirror_tables.php:43,57`:

```php
$table->bigInteger('crm_id')->nullable()->unique();
// ...
$table->unique(['crm_student_id', 'crm_class_id'], 'student_class_unique');
```

`MirrorCoreCommand::syncRegistrations()` (line 245) keys `updateOrCreate` on
`crm_id` alone. But `crm_id` is **nullable**, and MySQL treats multiple `NULL`
values in a unique column as non-conflicting (each NULL is "distinct" for
uniqueness purposes). `upsert($rows, ['crm_id'], [...])` would rely on the
`crm_id` unique index as the conflict target — if `crm_id` is ever null/missing
for a batch row (need to confirm from real API responses whether `item['ID']`
can be absent), the upsert's ON DUPLICATE KEY behavior on a nullable unique
column doesn't guarantee the same outcome as `updateOrCreate`, and could
silently insert duplicate rows that then collide with the *separate*
`student_class_unique` constraint mid-batch, aborting the whole batch insert.
`updateOrCreate` never hits this because it does a SELECT-then-INSERT/UPDATE
per row against whichever row already exists, sidestepping the ambiguity.

**Before converting:** confirm `item['ID']` (registration `crm_id`) is always
present in bulk API responses. If yes, this risk is theoretical and the
conversion is safe. If it can be null, the upsert conflict target needs to be
`['crm_student_id', 'crm_class_id']` instead (matching `student_class_unique`),
which changes what "duplicate" means for a registration record — needs a
product decision, not just an engineering one.

### 3. Hikvision's per-row `catch (Throwable) { $failed++ }` is incompatible with batch upsert

`HikvisionSyncCommand.php:149-168,213-242,287-310` wraps every single
`updateOrCreate()` call in its own `try/catch`, incrementing a `$failed`
counter so one bad record doesn't abort the whole page. `upsert()` sends the
entire batch as one SQL statement — if any row in the batch violates a
constraint, the *entire batch* fails, not just that row. Converting would
either lose per-record failure isolation (a single malformed record blocks 29
good ones in the same page) or require falling back to row-by-row on batch
failure anyway, which erases most of the performance win.

Additionally, `HikvisionAttendance`/`HikvisionAlarm` generate their unique key
via `attendanceExternalId()` (`HikvisionSyncCommand.php:343-346`):

```php
private function attendanceExternalId(array $record): string
{
    return (string) ($record['serialNo'] ?? '') . '-' . (string) ($record['time'] ?? uniqid());
}
```

When `$record['time']` is missing, this falls back to `uniqid()` — a
non-deterministic value generated fresh on every call. Two rows in the same
page that both lack `time` would get two different fabricated keys under
either write method, but a batch `upsert()` computing keys up-front for
deduplication-within-batch would behave differently from sequential
`updateOrCreate` calls in edge cases where `uniqid()` collisions theoretically
matter. Low real-world risk (uniqid() collisions are rare), but it's another
signal that the "unique key" isn't as clean as `crm_id` on the CRM mirror
tables — page sizes here are also only 30 rows, so the query-count savings
from batching are far smaller than on `MirrorCoreCommand`'s 500-row pages.

## What IS safe to convert, and how

Two of the four `MirrorCoreCommand` sync methods have clean, non-nullable,
single-column unique keys with no JSON-cast complexity beyond `raw_data`
itself:

- **`syncClassesPass()`** (line 70) — unique key `crm_id`, not nullable
  (`$table->bigInteger('crm_id')->unique()`, migration line 27). Safe.
- **`syncStudents()`** (line 183) — unique key `crm_id`, not nullable
  (migration line 14). Safe.

**Recommended conversion pattern** (apply to these two methods first, as a
follow-up PR with its own test pass against a copy of production data):

```php
protected function syncClassesPass(?string $history): void
{
    // ... existing pagination loop unchanged ...

    foreach ($response['data'] as $item) {
        $seen++;
        if (($item['STATUS_NAME'] ?? null) === 'Terminé') {
            $finished++;
        }
        $rows[] = [
            'crm_id'         => $item['ID'],
            'class_id'       => $item['CLASS_ID'] ?? null,
            'name'           => $item['NAME'] ?? $item['REFERENCE'],
            'crm_teacher_id' => $item['EMPLOYEE_TEACHER_ID'] ?? null,
            'level'          => $item['SCHOOL_LEVEL_NAME'] ?? null,
            'site_id'        => $item['STR_STORE_ID'] ?? null,
            'raw_data'       => json_encode($item),
            'last_synced_at' => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    if (!empty($rows)) {
        DB::transaction(function () use ($rows) {
            \App\Models\CrmClass::query()->upsert(
                $rows,
                ['crm_id'],
                ['class_id', 'name', 'crm_teacher_id', 'level', 'site_id', 'raw_data', 'last_synced_at', 'updated_at']
            );
        });
    }

    // ... pagination continues, batching per page (500 rows) ...
}
```

Notes for whoever picks this up:
- `created_at` must be included in the insert row array (upsert doesn't
  auto-populate Eloquent timestamps the way `create()`/`updateOrCreate()` do)
  but should **not** be in the update-columns list, so it isn't overwritten on
  existing rows.
- Batch per page (500 rows), not across the whole sync run — keeps each
  transaction small and matches the existing pagination boundary.
- Wrap each page's upsert in `DB::transaction()` per the audit's finding 4.2
  (no transaction currently wraps these writes at all).
- Test against a copied/staging dataset first: confirm no existing row has a
  `raw_data` value that round-trips differently through manual
  `json_encode()`/`json_decode()` vs. the Eloquent cast (e.g. unicode escaping,
  key ordering — shouldn't matter for JSON semantics, but worth a diff check
  before trusting it blind on 135k+ rows of production CRM data).

## What was NOT touched in this pass

- `syncRegistrations()` — blocked on confirming whether `crm_id` can be null
  in bulk API responses (see #2 above).
- `syncTeachers()` — not a page-walked bulk sync; it's an in-memory
  `CrmClass::all()->filter()->map()->unique()->each()` pipeline with
  find-or-create-by-multiple-fallback-strategies logic (line 126-178) that
  doesn't map cleanly onto upsert() at all. Separately flagged in the main
  audit (finding 4.3) as needing `chunk()` instead of `::all()` — a different
  fix, not an upsert conversion.
- `syncAttendance()` in `MirrorCoreCommand` — dead code, not called from
  `handle()` (see comment at line 48-49: superseded by `crm:sync-attendance`).
  Not worth converting code that isn't running.
- All of `HikvisionSyncCommand.php` — see #3 above (per-row failure isolation
  + small page sizes make the tradeoff not worth it at this scale).

## Immediate low-risk fix that IS worth doing separately

Regardless of the upsert question, none of these sync loops wrap their writes
in `DB::transaction()`. Adding a transaction per page (with the existing
`updateOrCreate()` calls unchanged) is a pure safety improvement with zero
behavioral ambiguity — a network failure mid-page currently leaves partial
writes with no rollback. This is a smaller, safer, immediately-applicable fix
that doesn't require resolving the upsert questions above. Recommended as the
next concrete step before attempting any upsert conversion.
