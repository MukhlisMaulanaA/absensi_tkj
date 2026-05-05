<?php

namespace App\Filament\Components;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceMapViewer extends Component
{
    public $record;
    public $apiKey;

    public function mount($record)
    {
        $this->record = $record;
        $this->apiKey = config('services.google.maps_api_key') ?? env('GOOGLE_MAPS_API_KEY', '');
    }

    public function render(): View
    {
        return view('filament.components.attendance-map-viewer');
    }
}
