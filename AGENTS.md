# AGENTS Guidelines

## Commands
- **Build**: `composer setup` (installs dependencies, migrates, builds assets)
- **Test**: `php artisan test` (runs all tests via Pest)
- **Single Test**: `php artisan test --filter <TestName>`
- **Lint**: `vendor/bin/pint` (Laravel preset) or `vendor/bin/php-cs-fixer fix`

## Code Style & Standards
- **PHP**: 8.2+. Follow PSR-12 and Laravel conventions.
- **Structure**: Keep Controllers slim. Business logic goes in `app/Services` or `app/Queries`.
- **Naming**: `PascalCase` for classes, `camelCase` for methods/variables.
- **Types**: Strict typing. Use return types (`: void`, `: string`) and property types.
- **Database**: Use Eloquent Models & Factories. Avoid raw SQL. No hardcoded IDs.
- **Safety**: Validate inputs via FormRequests. Throw Exceptions instead of returning false.
- **Formatting**: Use standard Laravel imports order (facades first).

## Workflow
- **Context**: Search (`grep`/`glob`) existing patterns (e.g. `Services/`) before coding.
- **Testing**: Write Feature/Unit tests (Pest) for all new logic. Verify with `php artisan test`.
- **Dependencies**: Check `composer.json` before adding libraries.
