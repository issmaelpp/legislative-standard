<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;

class Admin extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    public static function getNavigationLabel(): string
    {
        return 'Panel';
    }

    public function getTitle(): string
    {
        return 'Panel';
    }

    public function getWidgets(): array
    {
        return Filament::getWidgets();
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}