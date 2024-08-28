<?php

namespace App\Filament\Resources\ProfitResource\Pages;

use App\Filament\Resources\ProfitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfit extends EditRecord
{
    protected static string $resource = ProfitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
