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
use Illuminate\Support\Facades\DB;
use Filament\Infolists\Components\View; // Add this import
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;



class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Activities';

    protected static ?int $navigationSort = 2;


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
                    Tables\Columns\TextColumn::make('offer.driver')
                    ->label('Driver ID')
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->offer->driver->super_key . $record->offer->driver->unique_id;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('offer.driver', function (Builder $driverQuery) use ($search) {
                            $driverQuery->where(DB::raw("CONCAT(super_key, unique_id)"), 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('offer.request.user.name')
                    ->label('Passenger')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('offer.request.user')
                    ->label('User ID')
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->offer->request->user->super_key . $record->offer->request->user->unique_id;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('offer.request.user', function (Builder $userQuery) use ($search) {
                            $userQuery->where(DB::raw("CONCAT(super_key, unique_id)"), 'like', "%{$search}%");
                        });
                    }),
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
                // Tables\Actions\EditAction::make(),
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
            Section::make('Ride Video')
            ->schema([
                ViewEntry::make('video')
                            ->view('filament.infolists.components.video-player')
                            ->getStateUsing(function (Ride $record) {
                                $now = Carbon::now();
                                $video = $record->video;

                                // $debug = [
                                //     'ride_id' => $record->id,
                                //     'video_relation' => $video,
                                //     'video_path' => $video?->path,
                                //     'video_created_at' => $video?->created_at,
                                //     'now' => $now->toDateTimeString(),
                                //     'app_timezone' => config('app.timezone'),
                                //     'db_timezone' => DB::connection()->getConfig('timezone'),
                                // ];

                                if (!$video) {
                                    return [
                                        'status' => 'not_found',
                                        'message' => 'Video not found',
                                        // 'debug' => $debug
                                    ];
                                }

                                $videoPath = $video->path;
                                $videoCreatedAt = Carbon::parse($video->created_at)->setTimezone(config('app.timezone'));

                                // $debug['video_created_at_tz'] = $videoCreatedAt->toDateTimeString();
                                // $debug['storage_exists'] = Storage::disk('public')->exists($videoPath);

                                $diffInHours = $now->diffInHours($videoCreatedAt, false);
                                // $debug['diff_in_hours'] = $diffInHours;

                                // Check if the file exists in storage
                                if (!Storage::disk('public')->exists($videoPath)) {
                                    return [
                                        'status' => 'not_found',
                                        'message' => 'Video file not found in storage',
                                        // 'debug' => $debug
                                    ];
                                }

                                // Check if the video has expired (72 hours after creation)
                                // We use abs() to ensure we're always dealing with a positive number
                                if (abs($diffInHours) > 72) {
                                    return [
                                        'status' => 'expired',
                                        'message' => 'Video expired',
                                        // 'debug' => $debug
                                    ];
                                }

                                return [
                                    'status' => 'available',
                                    'path' => $videoPath,
                                    // 'debug' => $debug
                                ];
                            })
                            ])
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
            // 'create' => Pages\CreateRide::route('/create'),
            'view' => Pages\ViewRide::route('/{record}'),
            // 'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', "completed");
    }
}
