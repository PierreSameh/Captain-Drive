<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;  // Import TextColumn
use Filament\Tables\Columns\BooleanColumn;  // Import BooleanColumn
use Filament\Forms\Components\Textarea;  // Import Textarea for form
use Filament\Forms\Components\TextInput;  // Import Textarea for form
use Filament\Tables\Actions\Action; // Import Action for custom actions
use Filament\Forms\Components\Card;
use App\Models\RejectMessage;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification; // Make sure to use the correct namespace for Notification
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ImageEntry;



class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               TextEntry::make('name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('phone'),
            // Add other columns as needed
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\Action::make('approve')
                ->action(fn (Driver $record) => $record->update(['is_approved' => 1]))
                ->requiresConfirmation()
                ->color('success'),
                Tables\Actions\Action::make('reject')
                ->action(function (Driver $record, array $data) {
                    $record->update(['is_approved' => 2]);
                    RejectMessage::create([
                        'driver_id' => $record->id,
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
            Tables\Actions\ViewAction::make(),
        ])
        ->recordUrl(null); // Disable the row click behavior
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Section::make('Driver')
            ->schema([
            ImageEntry::make('picture')
            ->path('storage/app/public'),
            TextEntry::make('name'),
            TextEntry::make('email'),
            TextEntry::make('phone'),
            TextEntry::make('add_phone'),
            TextEntry::make('national_id'),
            TextEntry::make('social_status'),
            TextEntry::make('gender'),
            ])
        ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            // 'create' => Pages\CreateDriver::route('/create'),
            // 'edit' => Pages\EditDriver::route('/{record}/edit'),
            'view' => Pages\ViewDriver::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_approved', 0);
    }
}
