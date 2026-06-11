<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.settings')]
#[Title('Profile')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $avatar = null;

    public bool $removeAvatar = false;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $this->avatar->store('avatars', 'public');

            // Also save as site logo for guest pages and sidebar
            file_put_contents(storage_path('app/site_logo.txt'), $user->avatar);
        } elseif ($this->removeAvatar && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            @unlink(storage_path('app/site_logo.txt'));
        }

        $user->save();

        $this->avatar = null;
        $this->removeAvatar = false;

        $this->dispatch('profile-saved');
        session()->flash('profile_status', 'Profile updated successfully.');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ]);

        // If nothing was entered, skip silently
        if (blank($this->password) && blank($this->current_password)) {
            return;
        }

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');

            return;
        }

        if (blank($this->password)) {
            $this->addError('password', 'Please enter a new password.');

            return;
        }

        $user->password = $this->password;
        $user->save();

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('password_status', 'Password updated successfully.');
    }

    public function clearAvatar(): void
    {
        $this->avatar = null;
        $this->removeAvatar = true;
    }

    public function render()
    {
        return view('livewire.settings.profile');
    }
}
