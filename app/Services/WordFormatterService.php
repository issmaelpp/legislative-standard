<?php
namespace App\Services;

use App\Enums\GenderEnum;
use App\Models\User;

class WordFormatterService
{
    public function buildMessageWithGender(string $action, User $user): string
    {
        $genderConfig = $this->getGenderConfiguration($user->person->gender);
        $predicado_verbal = $this->getVerbPredicate($action, $genderConfig['suffix']);

        return $genderConfig['article'] . $user->name . $predicado_verbal;
    }

    private function getGenderConfiguration(GenderEnum $gender): array
    {
        return match ($gender) {
            GenderEnum::masculino => [
                'article' => 'El usuario ',
                'suffix' => 'o',
            ],
            GenderEnum::femenino => [
                'article' => 'La usuaria ',
                'suffix' => 'a',
            ],
            default => [
                'article' => 'Le usuarie ',
                'suffix' => 'e',
            ],
        };
    }

    private function getVerbPredicate(string $action, string $suffix): string
    {
        return match ($action) {
            'created' => " fue cread{$suffix}",
            'updated' => " fue actualizad{$suffix}",
            'deleted' => " fue eliminad{$suffix}",
            'restored' => " fue restaurad{$suffix}",
            'force_deleted' => " fue eliminad{$suffix} permanentemente",
            default => $action,
        };
    }
}