<?php

namespace App\Filament\Resources\ProfitResource\Pages;

use App\Filament\Resources\ProfitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Profit;
class ListProfits extends ListRecords
{
    protected static string $resource = ProfitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->visible(fn () => Profit::count() === 0)
            ->createAnother(false),

        ];
    }
}
