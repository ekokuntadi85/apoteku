<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Title('Pengaturan Tampilan')]
class Appearance extends Component
{
    use WithFileUploads;

    public $appName;
    public $appLogo;
    public $address;
    public $phoneNumber;
    public $existingLogo;

    public function mount()
    {
        $this->appName = config('settings.app_name');
        $this->address = config('settings.address');
        $this->phoneNumber = config('settings.phone_number');
        $this->existingLogo = config('settings.app_logo_path');
    }

    public function save()
    {
        $this->validate([
            'appName' => 'required|string|max:255',
            'appLogo' => 'nullable|image|max:1024', // 1MB Max
            'address' => 'nullable|string',
            'phoneNumber' => 'nullable|string|max:20',
        ]);

        Setting::updateOrCreate(['key' => 'app_name'], ['value' => $this->appName]);
        Setting::updateOrCreate(['key' => 'address'], ['value' => $this->address]);
        Setting::updateOrCreate(['key' => 'phone_number'], ['value' => $this->phoneNumber]);

        if ($this->appLogo) {
            $path = $this->appLogo->store('logos', 'public');
            Setting::updateOrCreate(['key' => 'app_logo_path'], ['value' => $path]);
            $this->existingLogo = $path;
        }

        // Clear the appLogo property to prevent it from being re-uploaded
        $this->appLogo = null;

        // Re-read the config to apply changes immediately
        $settings = Setting::all()->keyBy('key')->map(fn ($setting) => $setting->value);
        config(['settings' => $settings]);

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}

