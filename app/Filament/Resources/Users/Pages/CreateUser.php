<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usuario creado exitosamente';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Eliminar password_confirmation antes de crear
        unset($data['password_confirmation']);

        return $data;
    }
}
