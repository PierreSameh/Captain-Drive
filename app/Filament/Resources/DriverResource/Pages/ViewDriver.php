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

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    // protected function getInfolists(): array
    // {
    //     dd($this->record); // This will dump the driver record and exit

    //     return [
    //         Infolist::make([
    //             Section::make('Driver Details')
    //                 ->schema([
    //                     TextEntry::make('name')
    //                         ->label('Name'),
    //                     TextEntry::make('email')
    //                         ->label('Email'),
    //                     TextEntry::make('phone')
    //                         ->label('Phone'),
    //                     TextEntry::make('add_phone')
    //                         ->label('Additional Phone'),
    //                     TextEntry::make('national_id')
    //                         ->label('National ID'),
    //                     TextEntry::make('social_status')
    //                         ->label('Social Status'),
    //                     TextEntry::make('gender')
    //                         ->label('Gender'),
    //                     TextEntry::make('status')
    //                         ->label('Status'),
    //                     TextEntry::make('id')
    //                         ->label('Driver ID')
    //                         ->formatStateUsing(fn ($state) => $this->record->super_key . $this->record->unique_id),
    //                 ])
    //                 ->columns(2),

    //             Section::make('Driver Picture')
    //                 ->schema([
    //                     ImageEntry::make('picture')
    //                         ->label('Driver Picture')
    //                         ->visible(fn ($record) => $record->picture)
    //                         ->path('storage/' . $this->record->picture),
    //                 ]),

    //             Section::make('Driver Documents')
    //                 ->schema([
    //                     Grid::make(3)->schema([
    //                         ImageEntry::make('driverdocs.national_front')
    //                             ->label('National ID Front')
    //                             ->visible(fn ($record) => $record->driverdocs?->national_front)
    //                             ->path('storage/' . $this->record->driverdocs->national_front),
    //                     ]),
    //                 ])
    //                 ->visible(fn ($record) => $record->driverdocs()->exists()),

    //             Section::make('Vehicle Information')
    //                 ->schema([
    //                     TextEntry::make('vehicle.model')
    //                         ->label('Model')
    //                         ->visible(fn ($record) => $record->vehicle),
    //                     TextEntry::make('vehicle.plate_number')
    //                         ->label('Plate Number')
    //                         ->visible(fn ($record) => $record->vehicle),
    //                     ImageEntry::make('vehicle.image')
    //                         ->label('Vehicle Image')
    //                         ->visible(fn ($record) => $record->vehicle?->vehicle_image)
    //                         ->path('storage/' . $this->record->vehicle->vehicle_image),
    //                 ])
    //                 ->visible(fn ($record) => $record->vehicle),
    //         ]),
    //     ];
    // }

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
                    \Filament\Forms\Components\Textarea::make('reject_reason')
                        ->required()
                        ->label('Rejection Reason'),
                ])
                ->requiresConfirmation()
                ->color('danger'),
        ];
    }
}
