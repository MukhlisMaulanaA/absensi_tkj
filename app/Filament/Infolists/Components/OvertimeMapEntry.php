<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\View\View;

class OvertimeMapEntry extends Entry
{
  protected string $view = 'filament.infolist.components.overtime-map-entry';

  public function render(): View
  {
    return view($this->view, [
      'record' => $this->getRecord(),
      'apiKey' => config('services.google.maps_api_key') ?? env('GOOGLE_MAPS_API_KEY', ''),
    ]);
  }
}
