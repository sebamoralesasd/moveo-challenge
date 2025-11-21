# AGENTS Guidelines

## Build/Lint/Test
- Setup: `composer setup` (includes migrations, Passport keys, npm)
- Dev: `composer dev` (server, queue, logs, vite) or `php artisan serve` + `php artisan queue:listen`
- Test all: `php artisan test` or `composer test`; uses SQLite in-memory
- Test single: `php artisan test --filter=TestName` or `vendor/bin/pest --filter=TestName`
- Lint/Format: `vendor/bin/pint`

## Code Style
- PHP 8.2+, Laravel 12, PSR-12; always declare return types
- Imports: group by PHP std → Illuminate → App; alphabetize within groups
- Types: typed properties; constructor promotion with `protected`; backed enums for fixed sets (`app/Enums/`)
- Naming: PascalCase classes/enums; camelCase methods/vars; snake_case DB columns
- Architecture: slim controllers; business logic in `app/Services/`; query builders in `app/Queries/`
- DB: Eloquent + factories; `lockForUpdate()` for race conditions; wrap multi-step ops in `DB::transaction()`
- Errors: throw `\Exception` with descriptive messages; catch in controllers, return JSON `['error' => $e->getMessage()]`
- Auth: Laravel Passport; `CheckUserRole` middleware; `Log::info()` for traceability
- Testing: Pest with `uses(RefreshDatabase::class)`; mock via `$this->mock(Service::class, fn(MockInterface $mock) => ...)`
- Auth in tests: `Passport::actingAs(User::factory()->create(['role' => UserRole::ADMIN->value]))`

## Cursor / Copilot
- Cursor rules: none
- Copilot rules: none