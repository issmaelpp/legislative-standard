<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información General')
                    ->schema([
                        TextEntry::make('log_name')
                            ->label('Tipo de Log')
                            ->badge(),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),

                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2),

                Section::make('Detalles del Acceso')
                    ->schema([
                        TextEntry::make('properties.visitor_type')
                            ->label('Tipo de Visitante')
                            ->default('-'),

                        TextEntry::make('properties.is_bot')
                            ->label('Es Bot')
                            ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No')
                            ->badge()
                            ->color(fn ($state) => $state ? 'warning' : 'success'),

                        TextEntry::make('properties.url')
                            ->label('URL')
                            ->default('-')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('properties.method')
                            ->label('Método HTTP')
                            ->badge()
                            ->default('-'),

                        TextEntry::make('properties.path')
                            ->label('Ruta')
                            ->default('-'),

                        TextEntry::make('properties.status_code')
                            ->label('Código de Estado')
                            ->badge()
                            ->color(fn ($state) => match(true) {
                                $state >= 200 && $state < 300 => 'success',
                                $state >= 300 && $state < 400 => 'info',
                                $state >= 400 && $state < 500 => 'warning',
                                $state >= 500 => 'danger',
                                default => 'gray',
                            })
                            ->default('-'),

                        TextEntry::make('properties.referrer')
                            ->label('Referencia')
                            ->default('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Dispositivo y Navegador')
                    ->schema([
                        TextEntry::make('properties.device.ip')
                            ->label('IP')
                            ->default('-')
                            ->copyable(),

                        TextEntry::make('properties.device.device_name')
                            ->label('Dispositivo')
                            ->default('-')
                            ->badge(),

                        TextEntry::make('properties.device.os.name')
                            ->label('Sistema Operativo')
                            ->formatStateUsing(function ($state, $record) {
                                $version = data_get($record->properties, 'device.os.version');
                                return $state . ($version ? " {$version}" : '');
                            })
                            ->default('-'),

                        TextEntry::make('properties.device.client.name')
                            ->label('Navegador')
                            ->formatStateUsing(function ($state, $record) {
                                $version = data_get($record->properties, 'device.client.version');
                                return $state . ($version ? " {$version}" : '');
                            })
                            ->default('-'),

                        TextEntry::make('properties.device.brand')
                            ->label('Marca')
                            ->default('-')
                            ->visible(fn ($record) => !empty(data_get($record->properties, 'device.brand'))),

                        TextEntry::make('properties.device.model')
                            ->label('Modelo')
                            ->default('-')
                            ->visible(fn ($record) => !empty(data_get($record->properties, 'device.model'))),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Usuario')
                    ->schema([
                        TextEntry::make('causer.name')
                            ->label('Nombre')
                            ->default('Sistema'),

                        TextEntry::make('causer.email')
                            ->label('Email')
                            ->default('-'),

                        TextEntry::make('causer_id')
                            ->label('ID Usuario')
                            ->default('-'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Modelo Afectado')
                    ->schema([
                        TextEntry::make('subject_type')
                            ->label('Tipo de Modelo')
                            ->formatStateUsing(fn ($state) => $state ? $state : '-'),

                        TextEntry::make('subject_id')
                            ->label('ID del Modelo')
                            ->default('-'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Información Técnica')
                    ->schema([
                        TextEntry::make('batch_uuid')
                            ->label('Batch UUID')
                            ->default('-')
                            ->copyable(),

                        TextEntry::make('id')
                            ->label('ID de Registro'),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i:s')
                            ->default('-'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}