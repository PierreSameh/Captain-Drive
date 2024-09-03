<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification; // Make sure to use the correct namespace for Notification
use App\Models\RejectMessage;
use Filament\Forms\Components\Textarea;  // Import Textarea for form
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

        protected function getActions(): array
    {
        return [
            Action::make('approve')
                ->action(function () {
                    $this->record->update(['is_approved' => 1]);

                    Notification::make()
                        ->success()
                        ->title('Driver Approved successfully')
                        ->send();
                    
                    return redirect()->to('admin/drivers/');

                })
                ->requiresConfirmation()
                ->color('success'),

                Action::make('reject')
                ->action(function (array $data) {
                    $this->record->update(['is_approved' => 2]);
                    RejectMessage::create([
                        'driver_id' => $this->record->id,
                        'reasons' => $data['reject_reason'],
                    ]);
                    Notification::make()
                        ->success()
                        ->title('Driver rejected successfully')
                        ->send();
                })
                ->form([
                    Forms\Components\Textarea::make('reject_reason')
                        ->required()
                        ->label('Rejection Reason'),
                ])
                ->requiresConfirmation()
                ->color('danger'),
        ];
    }
}
