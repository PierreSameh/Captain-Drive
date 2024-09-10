<?php

namespace App\Filament\Resources\ApprovedDriverResource\Pages;

use App\Filament\Resources\ApprovedDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovedDrivers extends ListRecords
{
    protected static string $resource = ApprovedDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
