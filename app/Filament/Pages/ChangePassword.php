<?php
namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Session;


class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static string $view = 'filament.pages.change-password';
    protected static ?string $title = 'Change Password';
    protected static ?string $navigationGroup = 'Account Settings';
protected static ?string $navigationLabel = 'Change Password';

    
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('current_password')
                ->label('Current Password')
                ->password()
                ->required(),

            Forms\Components\TextInput::make('new_password')
                ->label('New Password')
                ->password()
                ->required()
                ->minLength(8),

            Forms\Components\TextInput::make('new_password_confirmation')
                ->label('Confirm New Password')
                ->password()
                ->required(),
            ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Validate current password
        if (! Hash::check($data['current_password'], Auth::user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Check if new passwords match
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            throw ValidationException::withMessages([
                'new_password_confirmation' => 'The new password confirmation does not match.',
            ]);
        }

        // Update the password
        Auth::user()->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Success message
        session()->flash('success', 'Password successfully changed!');

    }

    public function submit(): void
    {
        $this->save();

        Auth::logout();

        // Clear and invalidate the session
        Session::flush();

        // Regenerate the session ID
        Session::regenerate();

        // Set a flash message
        Session::flash('status', 'Your password has been changed. Please log in again.');

        // Redirect to the login page
        $this->redirect('/admin/login');
    }
}
