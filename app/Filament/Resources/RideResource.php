<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Filament\Resources\RideResource\RelationManagers;
use App\Models\Ride;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification; // Make sure to use the correct namespace for Notification
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Facades\FilamentIcon;
use Filament\Infolists\Components\Grid;

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('offer.driver.name')
                    ->label('Driver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('offer.request.user.name')
                    ->label('Passenger')
                    ->searchable(),
                Tables\Columns\TextColumn::make('offer.price')
                    ->label('Price'),
                    Tables\Columns\TextColumn::make('rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Section::make('Ride')
            ->schema([
                Grid::make(2)
                ->schema([
            TextEntry::make('offer.driver.name')
            ->label("Driver Name"),
            TextEntry::make('offer.request.user.name')
            ->label('Passenger Name'),
            TextEntry::make('offer.price')
            ->label('Price'),
            TextEntry::make('offer.request.st_location')
            ->label('Start Location'),
            TextEntry::make('offer.request.en_location')
            ->label('End Location'),
            TextEntry::make('created_at')
            ->label('Start Time'),
            TextEntry::make('updated_at')
            ->label('End Time'),
            ]),
            ]),
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
            'index' => Pages\ListRides::route('/'),
            'create' => Pages\CreateRide::route('/create'),
            'view' => Pages\ViewRide::route('/{record}'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', "completed");
    }
}
