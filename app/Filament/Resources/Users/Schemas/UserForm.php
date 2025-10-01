<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Seguridad')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn (?string $state): ?string =>
                                filled($state) ? Hash::make($state) : null
                            )
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->revealable()
                            ->helperText('Mínimo 8 caracteres. Dejar en blanco para mantener la contraseña actual al editar.'),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->revealable(),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'create' || $operation === 'edit'),

                Section::make('Roles y Permisos')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información Adicional')
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Correo Verificado')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i:s')
                            ->seconds(false),

                        DateTimePicker::make('created_at')
                            ->label('Fecha de Creación')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i:s')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('updated_at')
                            ->label('Última Actualización')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i:s')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->visible(fn (string $operation): bool => $operation === 'edit' || $operation === 'view')
                    ->collapsible(),
            ]);
    }
}
