<?php

namespace App\Filament\Resources\AdminNumberResource\Pages;

use App\Filament\Resources\AdminNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminNumbers extends ListRecords
{
    protected static string $resource = AdminNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
