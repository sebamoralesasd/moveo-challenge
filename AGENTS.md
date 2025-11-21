# AGENTS Guidelines

## Build/Lint/Test
- Setup: `composer install` -> `cp .env.example .env`; `php artisan key:generate`; `npm ci && npm run build`
- Dev: `composer dev` (server, queue, vite) or `php artisan serve` + `php artisan queue:work` + `npm run dev`
- Test: `php artisan test` or `composer test` (clears config first)
- Test Single: `php artisan test --filter TestName` (or `vendor/bin/pest --filter TestName`)
- Lint/Format: `vendor/bin/pint`

## Code Style
- PHP 8.2+, strict PSR-12; always declare return types
- Imports: group by type (std, Illuminate, App), alphabetize within groups
- Types: typed properties, constructor type hints; enums for fixed sets
- Naming: PascalCase for classes/enums; camelCase for methods/vars; snake_case DB columns
- Architecture: slim controllers; domain logic in `app/Services` and `app/Queries`
- DB & Errors: Eloquent models + factories; avoid raw SQL; use `lockForUpdate()` for race conditions; throw descriptive `\Exception` messages
- Auth/Logging: Laravel Passport; `CheckUserRole` middleware; `Log::info()` for traceability
- Testing: Pest; `RefreshDatabase`; mocks via `$this->mock(...)`; factories for data
- Tests: use Passport::actingAs(User::factory()->create(['role' => UserRole::ADMIN->value])) for admin context

## Cursor / Copilot
- Cursor rules: none detected
- Copilot rules: none detected