# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using the official Laravel Livewire Starter Kit. It combines:
- **Laravel Framework** (^12.0) as the backend
- **Livewire Flux** (^2.1.1) for reactive components
- **Livewire Volt** (^1.7.0) for single-file components
- **Laravel Fortify** (^1.30) for authentication features
- **Filament** (^4.0) admin panel at `/admin` path
- **Tailwind CSS** (^4.0.7) with Vite for frontend tooling
- **SQLite** as the default database
- **Spatie Laravel Permission** for role and permission management
- **Spatie Laravel ActivityLog** for activity tracking
- **Matomo Device Detector** (^6.4) for bot detection and device/browser analysis

## Filament Development Guidelines

**CRITICAL: Before writing ANY Filament code, you MUST:**

1. **Check vendor files first** - Read the actual class definitions in `vendor/filament/` to verify:
   - Correct namespaces for components
   - Required parameters for methods
   - Available methods and their signatures

2. **Never assume Filament 3.x syntax works in Filament 4.x** - The architecture changed significantly:
   - **Forms**: Use `Filament\Forms\Components\*` for inputs (TextInput, Select, FileUpload)
   - **Tables**: Use `Filament\Tables\Columns\*` for columns (TextColumn, ImageColumn)
   - **Infolists**: Use `Filament\Infolists\Components\*` for entries (TextEntry, ImageEntry)
   - **Schemas**: Use `Filament\Schemas\Components\*` for containers (Section, Grid, Fieldset)
   - **Actions**: Use `Filament\Actions\*` (not `Filament\Tables\Actions\*`)
   - **Modern syntax**: Use `->recordActions()` and `->toolbarActions()` instead of `->actions()` and `->bulkActions()`

3. **Search for existing examples** - Before creating new Filament resources:
   ```bash
   # Search for existing resources to follow the same pattern
   find app/Filament/Resources -name "*.php" -type f
   ```

4. **Use official commands** - Always generate scaffolding with artisan:
   ```bash
   php artisan make:filament-resource ModelName --generate --view --soft-deletes
   ```

5. **Infolist components must use model attributes only** - Cannot use methods like `initials()` or computed properties. Only direct attributes and relationships are allowed.

6. **Test immediately** - After writing Filament code, verify it works before proceeding.

**DO NOT write Filament code from memory or assumptions. ALWAYS verify with vendor files first.**

## Development Commands

### Starting Development
```bash
composer dev
# Runs: php artisan serve + php artisan queue:listen + npm run dev concurrently
```

### Building Assets
```bash
npm run build    # Build for production
npm run dev      # Development with hot reload
```

### Testing
```bash
composer test           # Run all tests (uses Pest framework)
php artisan test        # Alternative test command
vendor/bin/pest         # Direct Pest execution
```

### Code Quality
```bash
vendor/bin/pint         # Code formatting (Laravel Pint)
```

### Database Operations
```bash
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed      # Fresh migration with seeding
php artisan db:seed                   # Run seeders
```

## Architecture Overview

### Authentication System
- Uses **Laravel Fortify** for authentication features
- Authentication routes defined in `routes/auth.php`
- Volt components in `resources/views/livewire/auth/` handle UI
- Supports two-factor authentication and email verification
- Custom Logout action: `App\Livewire\Actions\Logout`

### Admin Panel (Filament)
- **Filament admin panel** accessible at `/admin` path
- Role-based access control (Super Admin role required)
- Uses Amber color scheme and auto-discovery for Resources/Pages/Widgets
- User model implements Filament contracts (`FilamentUser`, `HasAvatar`, `HasName`)
- Configured in `app/Providers/Filament/AdminPanelProvider.php`

### Role & Permission System
- **Spatie Laravel Permission** package for roles and permissions
- Role-based access to Filament admin panel
- User roles determine admin panel accessibility
- Super Admin role has blanket authorization via Gate (see `AppServiceProvider::boot()`)

### Activity Logging System
- Uses **Spatie Laravel ActivityLog** for comprehensive activity tracking
- **Three types of activity logs** (differentiated by `log_name`):
  1. **`authentication`** - Authentication events (login, logout, register, login_failed)
  2. **`access`** - HTTP access tracking (page visits, API calls)
  3. **`default`** - Model changes (create, update, delete, restore)
- **Event Listeners** in `app/Listeners/` automatically log authentication events via Laravel's auto-discovery:
  - `LogSuccessfulLogin` - Logs successful logins
  - `LogSuccessfulLogout` - Logs logouts
  - `LogFailedLogin` - Logs failed login attempts
  - `LogRegisteredUser` - Logs new user registrations
  - **Note**: Listeners are auto-discovered, NOT manually registered in service providers
- **Middleware** `TrackAccessActivity` logs all HTTP requests (registered in `bootstrap/app.php`)
- **Model Observers** track changes to Eloquent models (e.g., `UserObserver`)
- All logs include device details via Matomo Device Detector (IP, user agent, OS, browser, bot detection)

### Livewire Integration
- **Volt-based architecture**: Single-file components mixing PHP logic and Blade templates
- Auth components: `resources/views/livewire/auth/` (login, register, password reset, etc.)
- Settings components: `resources/views/livewire/settings/` (profile, password, appearance, 2FA)
- Components use Volt routing in `routes/web.php`

### Frontend Architecture
- **Vite** build system with Laravel plugin
- **Tailwind CSS 4.x** with Vite plugin integration
- Assets in `resources/css/app.css` and `resources/js/app.js`
- Blade components in `resources/views/components/`
- Layout system: `app.blade.php` and `auth.blade.php` layouts

### Database Configuration
- Default: **SQLite** (`database/database.sqlite`)
- Migrations in `database/migrations/`
- Uses database sessions, cache, and queue by default
- Can be switched to MySQL/PostgreSQL via environment variables

### Directory Structure
- `app/Http/` - Controllers and middleware
- `app/Listeners/` - Event listeners (authentication event logging)
- `app/Livewire/Actions/` - Livewire action classes
- `app/Models/` - Eloquent models (User model with SoftDeletes and HasRoles traits)
- `app/Observers/` - Model observers (UserObserver for activity tracking)
- `app/Providers/` - Service providers (App, Fortify, Volt)
- `app/Providers/Filament/` - Filament panel providers
- `app/Services/` - Application services (ActivityLoggerService, WordFormatterService)
- `database/seeders/` - Database seeders (RolePermissionSeeder, UserSeeder)
- `resources/views/livewire/` - Volt components
- `resources/views/components/` - Blade components
- `resources/views/flux/` - Flux UI customizations
- `routes/web.php` - Web routes (uses Volt routing)
- `routes/auth.php` - Authentication routes

### Key Features
- **Dashboard** with authentication middleware
- **Settings pages**: Profile, Password, Appearance, Two-Factor Auth
- **Responsive design** with Tailwind CSS
- **Queue system** enabled (database driver)
- **Email verification** and password reset flows

## Testing Framework
- Uses **Pest PHP** (^4.1) instead of PHPUnit
- Test environment uses in-memory SQLite
- Separate Unit and Feature test suites
- Laravel-specific Pest plugin included

## CI/CD Configuration
- **GitHub Actions** workflows for linting and testing
- Linting workflow runs Laravel Pint on push/PR to main and develop branches
- Testing workflow uses Node 22 and PHP 8.4
- Requires Flux license credentials for CI environment

## Development Environment
- **PHP 8.2+** required (CI uses PHP 8.4)
- **Node 22** used in CI
- **Laravel Sail** available for Docker development
- **EditorConfig** for consistent code formatting
- Concurrent development setup via `composer dev` command

## Services Architecture
- **ActivityLoggerService**: Logs model events with device/browser details (IP, user agent, OS, browser)
  - **Performance optimizations** (added 2025-09-30):
    - Device detection cached for 24 hours by User-Agent hash
    - Rate limiting: authenticated users logged once per 5 minutes
    - Bot detection skipped for authenticated users (always `is_bot = false`)
    - Cache keys: `device_details:{hash}:{auth|anon}` and `access_log_throttle:{user_id}`
    - Reduces ~90% of DeviceDetector calls and ~80% of database writes
- **WordFormatterService**: Handles grammatical formatting for activity log messages (gender-aware)
- Device detection uses Matomo Device Detector library
- Activity logs stored via Spatie ActivityLog with properties for attributes, old values, and device info

## Performance Configuration
- **Cache Driver**: Recommended to use `file` instead of `database` for better performance
  - Set `CACHE_STORE=file` in `.env` for optimal ActivityLoggerService caching
  - Alternative: `redis` for production multi-server environments
  - Default `database` driver adds latency to cache operations
- Activity logging middleware optimized to avoid blocking HTTP responses