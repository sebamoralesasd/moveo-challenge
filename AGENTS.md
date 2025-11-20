# AGENTS Guidelines

## Commands
- **Build**: `composer setup` (installs dependencies, migrations, assets)
- **Test**: `php artisan test` (runs Pest tests) or `composer test`
- **Test Single**: `php artisan test --filter <TestName>`
- **Lint**: `vendor/bin/pint` (Laravel preset)

## Code Style & Standards
- **PHP**: 8.2+. Follow PSR-12. Use strict typing and return types.
- **Structure**: Slim Controllers. Logic in `app/Services` or `app/Queries`.
- **Naming**: `PascalCase` for classes, `camelCase` for methods.
- **Database**: Use Eloquent Models & Factories. Avoid raw SQL.
- **Validation**: Use FormRequests. Throw Exceptions on failure.
- **Security**: No hardcoded secrets. Use policies/gates for auth.

## Workflow
- **Context**: Search (`grep`/`glob`) existing patterns/services first.
- **Testing**: Write Feature/Unit tests (Pest) for all new logic.
- **Dependencies**: Verify `composer.json` before adding new ones.
