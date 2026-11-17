<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected bool $assignPanitiaBerkas = false;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['assign_panitia_berkas'] = $this->getRecord()->hasRole(User::ROLE_PANITIA_BERKAS);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assignPanitiaBerkas = (bool) ($data['assign_panitia_berkas'] ?? false);
        unset($data['assign_panitia_berkas']);

        return $data;
    }

    protected function afterSave(): void
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
