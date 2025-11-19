# AGENTS Guidelines

## Build & Test
- **Setup**: `composer setup` (installs deps, setup env, migrates).
- **Test Suite**: `php artisan test` (Pest) or `composer test`.
- **Single Test**: `php artisan test --filter <TestName|MethodName>`.
- **Frontend**: `npm install && npm run build`.

## Code Style & Standards
- **PHP**: 8.2+. Use `declare(strict_types=1);`.
- **Format**: Run `vendor/bin/pint` to fix style issues (PSR-12/Laravel).
- **Lint**: `vendor/bin/php-cs-fixer fix` for deeper cleanup if needed.
- **Naming**: `PascalCase` classes, `camelCase` methods/variables.
- **Types**: Explicitly type all parameters, return values, and properties.

## Development Conventions
- **Testing**: Write Pest tests in `tests/Feature` or `tests/Unit`.
- **DB**: Use factories for test data. Avoid hardcoded IDs.
- **Safety**: Prefer exceptions over returning false/null on errors.
- **Context**: Check `composer.json` for deps before assuming availability.
