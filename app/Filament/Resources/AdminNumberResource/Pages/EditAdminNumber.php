<?php

namespace App\Filament\Resources\AdminNumberResource\Pages;

use App\Filament\Resources\AdminNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdminNumber extends EditRecord
{
    protected static string $resource = AdminNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
