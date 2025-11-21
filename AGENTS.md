# AGENTS Guidelines

## Build/Lint/Test
- Setup: `composer setup` (or manually: `composer install && cp .env.example .env && php artisan key:generate && npm ci && npm run build`)
- Dev: `composer dev` (runs server, queue, logs, vite concurrently) or individually: `php artisan serve` + `php artisan queue:listen`
- Test: `php artisan test` or `composer test` (clears config first); uses SQLite in-memory
- Test Single: `php artisan test --filter=TestName` or `vendor/bin/pest --filter=TestName`
- Lint/Format: `vendor/bin/pint`

## Code Style
- PHP 8.2+, Laravel 12, strict PSR-12; always declare return types
- Imports: group by type (PHP std, Illuminate, App), alphabetize within groups
- Types: typed properties, constructor promotion with `protected`; enums for fixed sets (see `app/Enums/`)
- Naming: PascalCase classes/enums; camelCase methods/vars; snake_case DB columns
- Architecture: slim controllers; domain logic in `app/Services/` and `app/Queries/`
- DB & Errors: Eloquent models + factories; use `lockForUpdate()` for race conditions; throw `\Exception` with descriptive messages
- Auth/Logging: Laravel Passport; `CheckUserRole` middleware; `Log::info()` for traceability
- Testing: Pest with `uses(RefreshDatabase::class)`; mocks via `$this->mock(Service::class, fn(MockInterface $mock) => ...)`
- Auth in tests: `Passport::actingAs(User::factory()->create(['role' => UserRole::ADMIN->value]))`

## Cursor / Copilot
- Cursor rules: none detected
- Copilot rules: none detected