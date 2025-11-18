# AGENTS Guidelines

- Build/Lint/Test
  - Install: `composer install` and `npm install` as needed.
  - Full tests: `composer test` or `php artisan test`.
  - Single test: `composer test -- --filter <TestClass>` or `php artisan test --filter <TestClass>`; for methods use `TestClass::methodName`.
  - Lint/format: `vendor/bin/pint` for formatting; `vendor/bin/php-cs-fixer fix` for targeted fixes.

- Code Style
  - PHP 8.2+, add `declare(strict_types=1);` where practical.
  - PSR-12: use statements sorted, no unused imports, proper namespaces.
  - Types: add parameter and return types; use typed properties where feasible.
  - Naming: classes PascalCase, methods camelCase, variables descriptive.
  - Errors: prefer exceptions; avoid broad catches; log and rethrow as needed.

- Testing
  - Write Pest-style tests; descriptive test names; use factories; keep tests isolated.

- Tooling
  - Pint for formatting; PHP-CS-Fixer for deeper fixes; run via `composer test` for CI parity.

- Cursor/Copilot
  - Cursor rules: none detected; follow project rules if added.
  - Copilot rules: none detected; follow repo conventions if added.

- Validation
  - Run a focused test after changes; summarize outcomes to confirm.