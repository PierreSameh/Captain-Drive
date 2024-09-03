<?php

namespace App\Filament\Resources\ProfitResource\Pages;

use App\Filament\Resources\ProfitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProfit extends CreateRecord
{
    protected static string $resource = ProfitResource::class;
    protected static bool $canCreateAnother = false;

    
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            // ->createAnother(false),

        ];
    }


}
