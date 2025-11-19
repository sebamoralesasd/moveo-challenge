# AGENTS Guidelines

## Build & Test
- **Setup**: `composer setup` (installs, migrates, builds assets)
- **Test**: `php artisan test` (Pest) or `composer test`
- **Single Test**: `php artisan test --filter <TestName>`
- **Format/Lint**: `vendor/bin/pint` and `vendor/bin/php-cs-fixer fix`

## Code Standards
- **PHP**: 8.2+. Use `declare(strict_types=1);` in services/classes.
- **Structure**: Laravel conventions (MVC). Keep logic in Services/Actions.
- **Naming**: `PascalCase` classes, `camelCase` methods/vars.
- **Types**: Strict typing for params, returns, and properties.
- **DB**: Use Factories for tests. No hardcoded IDs.

## Workflow
- **Dependencies**: Check `composer.json` before assuming libs.
- **Safety**: Exceptions over `false`/`null`. Validate inputs.
- **Testing**: Write Feature/Unit tests using Pest.
- **Context**: Search existing patterns (e.g., `grep` services) before coding.
