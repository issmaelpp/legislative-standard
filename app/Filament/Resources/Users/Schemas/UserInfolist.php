<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        ImageEntry::make('avatar_url')
                            ->label('Avatar')
                            ->circular()
                            ->defaultImageUrl(fn (User $record): string =>
                                'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'
                            ),

                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('email')
                            ->label('Correo Electrónico')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('Roles y Permisos')
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label('Roles')
                            ->badge(),
                    ]),

                Section::make('Seguridad')
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Correo Verificado')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('No verificado'),
                    ]),

                Section::make('Fechas')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i:s'),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i:s')
                            ->since(),

                        TextEntry::make('deleted_at')
                            ->label('Fecha de Eliminación')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('No eliminado'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
