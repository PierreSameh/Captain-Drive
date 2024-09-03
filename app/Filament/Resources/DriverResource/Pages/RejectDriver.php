<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use App\Models\RejectMessage;
use App\Models\Driver;  // Make sure to import your Driver model
use Filament\Notifications\Notification;

class RejectDriver extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = DriverResource::class;

    protected static string $view = 'filament.resources.driver-resource.pages.reject-driver';

    public ?array $data = [];

    public $record;

    public function mount($record): void
    {
        $this->record = Driver::findOrFail($record);  // Fetch the Driver model instance
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('reject_reason')
                    ->label('Rejection Reason')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function reject(): void
    {
        $data = $this->form->getState();

        $this->record->update(['is_approved' => 2]);
        RejectMessage::create([
            'driver_id' => $this->record->id,
            'reasons' => $data['reject_reason'],
        ]);

        Notification::make()
            ->success()
            ->title('Driver rejected successfully')
            ->send();

        $this->redirect(DriverResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reject')
                ->action('reject')
                ->color('danger'),
        ];
    }
}