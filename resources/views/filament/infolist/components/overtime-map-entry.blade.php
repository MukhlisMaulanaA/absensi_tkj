@php
  $hasCoords = $record->latitude && $record->longitude;
  $mapId = 'overtime-map-' . $record->id;
@endphp

<div class="space-y-4">
  @if ($hasCoords)
    <div class="rounded-lg overflow-hidden border border-gray-300 shadow-sm">
      <div id="{{ $mapId }}" style="width: 100%; height: 420px;"></div>
    </div>

    @if ($apiKey)
      <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initOvertimeMap"></script>

      <script>
        window.initOvertimeMap = function() {
          const mapElement = document.getElementById('{{ $mapId }}');
          if (!mapElement) return;

          const latLng = { lat: {{ $record->latitude }}, lng: {{ $record->longitude }} };

          const map = new google.maps.Map(mapElement, {
            zoom: 16,
            center: latLng,
            mapTypeControl: true,
            fullscreenControl: true,
            streetViewControl: false,
            zoomControl: true,
          });

          const marker = new google.maps.Marker({
            position: latLng,
            map: map,
            title: 'Overtime Request Location',
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 8,
              fillColor: '#2563eb',
              fillOpacity: 0.9,
              strokeColor: '#fff',
              strokeWeight: 2,
            }
          });

          const infoWindow = new google.maps.InfoWindow({
            content: `
              <div style="font-family:sans-serif;padding:6px 10px;min-width:160px;">
                <p style="margin:0 0 4px;font-weight:700;color:#1e40af;font-size:13px;">📍 Overtime Location</p>
                <p style="margin:0 0 2px;font-size:12px;color:#374151;">⏰ {{ $record->start_time?->format('d/m/Y H:i') ?? '-' }} — {{ $record->end_time?->format('d/m/Y H:i') ?? '-' }}</p>
                <p style="margin:0;font-size:11px;color:#6b7280;">{{ number_format($record->latitude, 6) }}, {{ number_format($record->longitude, 6) }}</p>
              </div>
            `
          });

          marker.addListener('click', () => infoWindow.open(map, marker));
          infoWindow.open(map, marker);
        };

        if (typeof Livewire !== 'undefined') {
          Livewire.hook('morph.updated', () => {
            if (window.google && window.google.maps) {
              window.initOvertimeMap();
            }
          });
        }
      </script>
    @else
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
        <p class="text-sm font-medium mb-2">⚠️ Google Maps API Key not configured</p>
        <p class="text-xs">Please add <code class="bg-yellow-100 px-1 rounded">GOOGLE_MAPS_API_KEY</code> to your .env file to display the map.</p>
      </div>
    @endif

    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
      <p class="font-semibold text-blue-900 mb-1">Coordinates</p>
      <p class="text-blue-800">{{ number_format($record->latitude, 6) }}, {{ number_format($record->longitude, 6) }}</p>
    </div>
  @else
    <div class="p-6 bg-gray-50 border border-gray-300 rounded-lg text-center">
      <p class="text-gray-600">No location data available for this overtime request.</p>
    </div>
  @endif
</div>
