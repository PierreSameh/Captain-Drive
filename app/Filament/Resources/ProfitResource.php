<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfitResource\Pages;
use App\Filament\Resources\ProfitResource\RelationManagers;
use App\Models\Profit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Illuminate\Validation\Rule;


class ProfitResource extends Resource
{
    protected static ?string $model = Profit::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?string $navigationGroup = 'Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('vehicle_type')
                ->required()
                ->options([
                    '1' => 'Car',
                    '2' => 'Condetioned Car',
                    '3' => 'Motorcycle',
                    '4' => 'Taxi',
                    '5' => 'Bus',
                ])
                ->unique(column: 'vehicle_type'),
                Forms\Components\TextInput::make('per_kilo')
                    ->required()
                    ->label("Cost Per Kilometre")
                    ->numeric(),
                Forms\Components\TextInput::make('percentage')
                    ->required()
                    ->label("Company's Share(%)")
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("vehicle_type")
                ->label('Vehicle Type')
                ->formatStateUsing(function ($state) {
                    $vehicleTypes = [
                        '1' => 'Car',
                        '2' => 'Conditioned Car',
                        '3' => 'Motorcycle',
                        '4' => 'Taxi',
                        '5' => 'Bus',
                    ];
            
                    return $vehicleTypes[$state] ?? 'Unknown'; // Return the mapped value or 'Unknown' if not found
                }),
                Tables\Columns\TextColumn::make('per_kilo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProfits::route('/'),
            'create' => Pages\CreateProfit::route('/create'),
            'edit' => Pages\EditProfit::route('/{record}/edit'),
        ];
    }
}
