<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\RideRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\DB;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;




class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('gender')
                    ->maxLength(255)
                    ->default(null),
                FileUpload::make('picture'),
                Forms\Components\Toggle::make('is_email_verified')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                    TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(function ($record) {
                        return $record->super_key . $record->unique_id;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(DB::raw("CONCAT(super_key, unique_id)"), 'like', "%{$search}%");
                    }),
                Tables\Columns\IconColumn::make('is_email_verified')
                    ->boolean(),
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
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('phone'),
                TextEntry::make('gender'),
                ImageEntry::make('picture'),
                TextEntry::make('id')
                    ->label('ID')
                    ->state(fn ($record) => $record->super_key . $record->unique_id),
                    TextEntry::make('completedRidesCount')
                    ->label('Number of Completed Rides')
                    ->state(function ($record) {
                        return $record->load('riderequest.offers.rides')
                            ->riderequest()
                            ->whereHas('offers.rides', function ($query) {
                                $query->where('status', 'completed');
                            })
                            ->count();
                    }),
            RepeatableEntry::make('riderequest')
                ->label('Completed Rides')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('offers.rides.id')
                            ->label('Ride ID'),
                        TextEntry::make('offers.driver.name')
                            ->label('Driver'),
                        TextEntry::make('offers.driver_id')
                            ->label('Driver ID'),
                        TextEntry::make('st_location')
                            ->label('From'),
                        TextEntry::make('en_location')
                            ->label('To'),
                        TextEntry::make('offers.price')
                            ->label('Price'),
                        TextEntry::make('created_at')
                            ->label('Date')
                            ->date(),
                        TextEntry::make('offers.rides.rate')
                        ->label('Rate'),
                        TextEntry::make('offers.rides.review')
                        ->label('Review'),
                        TextEntry::make('offers.rides.status')
                            ->label('Status'),
                    ])
                ])->columnSpanFull()
                ->visible(fn ($record) => $record->riderequest()
                    ->whereHas('offers.rides', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->exists()
                ),
            ]);
    }
    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with('completedRides.offer.request');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
