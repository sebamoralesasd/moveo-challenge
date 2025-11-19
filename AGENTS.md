# AGENTS Guidelines

## Build & Test
- **Setup**: `composer setup` (installs, migrates, builds assets)
- **Test**: `php artisan test` (Pest)
- **Single Test**: `php artisan test --filter <TestName>`
- **Lint/Fix**: `vendor/bin/pint` or `vendor/bin/php-cs-fixer fix`

## Code Standards
- **PHP**: 8.2+. Adhere to Laravel conventions (MVC).
- **Organization**: Logic in Services/Actions, Controllers slim.
- **Naming**: `PascalCase` classes, `camelCase` methods/variables.
- **Database**: Use Eloquent Models & Factories. No hardcoded IDs.
- **Types**: Use proper type hinting for params/returns where possible.

## Workflow
- **Dependencies**: Verify in `composer.json` before using libraries.
- **Safety**: Validate all inputs. Prefer Exceptions over null/false.
- **Testing**: Create Feature/Unit tests using Pest.
- **Context**: Search codebase patterns before implementing new features.
