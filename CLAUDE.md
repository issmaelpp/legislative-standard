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
- Activity logging via **Spatie Laravel ActivityLog**

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
- `app/Livewire/Actions/` - Livewire action classes
- `app/Models/` - Eloquent models (User model included)
- `app/Providers/Filament/` - Filament panel providers
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
- **Laravel Sail** available for Docker development
- **EditorConfig** for consistent code formatting
- Concurrent development setup via `composer dev` command