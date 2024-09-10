<?php

namespace App\Filament\Resources\ApprovedDriverResource\Pages;

use App\Filament\Resources\ApprovedDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApprovedDriver extends ViewRecord
{
    protected static string $resource = ApprovedDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
