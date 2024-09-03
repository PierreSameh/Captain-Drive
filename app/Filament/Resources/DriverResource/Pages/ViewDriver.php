<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->action(function () {
                    $this->record->update(['is_approved' => 1]);
                    
                    Notification::make()
                        ->success()
                        ->title('Driver Approved successfully')
                        ->send();
                    
                    return $this->redirect(DriverResource::getUrl('index'));
                })
                ->requiresConfirmation()
                ->color('success'),

                Action::make('reject')
                ->url(fn ($record) => route('filament.resources.drivers.reject', ['record' => $record->id]))
                ->color('danger'),
        ];
    }
}