<?php

namespace App\Filament\Resources\ApprovedDriverResource\Pages;

use App\Filament\Resources\ApprovedDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovedDriver extends EditRecord
{
    protected static string $resource = ApprovedDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }
}
