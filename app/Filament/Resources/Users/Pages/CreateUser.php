<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $assignPanitiaBerkas = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assignPanitiaBerkas = (bool) ($data['assign_panitia_berkas'] ?? false);
        unset($data['assign_panitia_berkas']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncPanitiaBerkasRole();
    }

    private function syncPanitiaBerkasRole(): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User || ! $actor->isAdmin() || $actor->can('master.roles.manage')) {
            return;
        }

        if ($this->assignPanitiaBerkas) {
            $this->getRecord()->assignRole(User::ROLE_PANITIA_BERKAS);

            return;
        }

        if ($this->getRecord()->hasRole(User::ROLE_PANITIA_BERKAS)) {
            $this->getRecord()->removeRole(User::ROLE_PANITIA_BERKAS);
        }
    }
}
