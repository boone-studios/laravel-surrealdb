# AGENTS.md

## Mission
- Preserve Laravel Query Builder and Eloquent behavior parity while integrating SurrealDB SDK internals.
- Prefer compatibility over convenience; do not ship behavior that diverges from expected Laravel semantics without explicit docs and tests.

## Hard Rules
- Do not change public query/Eloquent behavior without adding/adjusting tests first.
- Every bug fix must include a regression test in `tests/`.
- Keep `DB::table()` behavior aligned with Laravel contracts (insert/update/delete/select/count/exists/first/get).
- Keep Eloquent model contracts aligned for core model lifecycle (`create`, `save`, `update`, `delete`, timestamps, casts).
- Do not merge if any matrix leg fails (PHP, Laravel, or SurrealDB version).
- Do not add undocumented config keys or behavior changes.

## Required Checks Before Completion
- `composer test`
- `composer analyse`
- `composer format-check`

## Compatibility Matrix Policy
- PHP: 8.2, 8.3, 8.4
- Laravel: 10, 11, 12, 13
- SurrealDB: v2, v3

## Test Coverage Expectations
- Connection/auth/context tests for SDK lifecycle.
- Query builder CRUD plus filters/sort/pagination edge cases.
- Eloquent lifecycle and cast behavior.
- Error-path tests for invalid queries and missing namespace/database/table states.

## Review Gates
- If changing `src/Connection.php`, include at least one integration test.
- If changing `src/Query/*`, include query compilation + execution coverage.
- If changing `src/Eloquent/*`, include model-level behavioral tests.
