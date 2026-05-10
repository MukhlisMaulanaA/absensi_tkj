@php
  $hasCheckInCoords = $record->check_in_latitude && $record->check_in_longitude;
  $hasCheckOutCoords = $record->check_out_latitude && $record->check_out_longitude;
  $mapId = 'attendance-map-' . $record->id;
@endphp

<div class="space-y-4">
  @if ($hasCheckInCoords || $hasCheckOutCoords)
    <div class="rounded-lg overflow-hidden border border-gray-300 shadow-sm">
      <div id="{{ $mapId }}" style="width: 100%; height: 500px;"></div>
    </div>

    @if ($apiKey)
      <!-- 1. Tambahkan &callback=initAttendanceMap di akhir URL -->
      <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initAttendanceMap">
      </script>

      <script>
        window.initAttendanceMap = function() {
          const mapElement = document.getElementById('{{ $mapId }}');
          if (!mapElement) return;

          @if ($hasCheckInCoords)
            const checkInLatLng = {
              lat: {{ $record->check_in_latitude }},
              lng: {{ $record->check_in_longitude }}
            };
          @endif

          @if ($hasCheckOutCoords)
            const checkOutLatLng = {
              lat: {{ $record->check_out_latitude }},
              lng: {{ $record->check_out_longitude }}
            };
          @endif

          const centerLatLng =
            @if ($hasCheckInCoords)
              checkInLatLng
            @else
              checkOutLatLng
            @endif ;

          const map = new google.maps.Map(mapElement, {
            zoom: 16,
            center: centerLatLng,
            mapTypeControl: true,
            fullscreenControl: true,
            streetViewControl: false,
            zoomControl: true,
          });

          @if ($hasCheckInCoords)
            const checkInMarker = new google.maps.Marker({
              position: checkInLatLng,
              map: map,
              title: 'Check-In',
              icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: '#10b981',
                fillOpacity: 0.8,
                strokeColor: '#fff',
                strokeWeight: 2,
              }
            });

            const checkInInfoWindow = new google.maps.InfoWindow({
              content: `
                <div style="font-family: sans-serif; padding: 5px 10px; min-width: 160px;">
                  <p style="margin: 0 0 4px; font-weight: 700; color: #065f46; font-size: 13px;">✅ Check-In</p>
                  <p style="margin: 0 0 2px; font-size: 12px; color: #374151;">🕐 {{ $record->check_in_time->format('d/m/Y H:i:s') }}</p>
                  <p style="margin: 0; font-size: 11px; color: #6b7280;">📍 {{ number_format($record->check_in_latitude, 6) }}, {{ number_format($record->check_in_longitude, 6) }}</p>
                </div>
              `
            });

            checkInMarker.addListener('click', () => checkInInfoWindow.open(map, checkInMarker));
            checkInInfoWindow.open(map, checkInMarker);
          @endif

          @if ($hasCheckOutCoords)
            const checkOutMarker = new google.maps.Marker({
              position: checkOutLatLng,
              map: map,
              title: 'Check-Out',
              icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: '#ef4444',
                fillOpacity: 0.8,
                strokeColor: '#fff',
                strokeWeight: 2,
              }
            });

            const checkOutInfoWindow = new google.maps.InfoWindow({
              content: `
                <div style="font-family: sans-serif; padding: 5px 10px; min-width: 160px;">
                  <p style="margin: 0 0 4px; font-weight: 700; color: #991b1b; font-size: 13px;">🔴 Check-Out</p>
                  <p style="margin: 0 0 2px; font-size: 12px; color: #374151;">🕐 {{ $record->check_out_time?->format('d/m/Y H:i:s') ?? 'N/A' }}</p>
                  <p style="margin: 0; font-size: 11px; color: #6b7280;">📍 {{ number_format($record->check_out_latitude, 6) }}, {{ number_format($record->check_out_longitude, 6) }}</p>
                </div>
              `
            });

            checkOutMarker.addListener('click', () => checkOutInfoWindow.open(map, checkOutMarker));
          @endif

          @if ($hasCheckInCoords && $hasCheckOutCoords)
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(checkInLatLng);
            bounds.extend(checkOutLatLng);
            map.fitBounds(bounds);

            new google.maps.Polyline({
              path: [checkInLatLng, checkOutLatLng],
              geodesic: true,
              strokeColor: '#6366f1',
              strokeOpacity: 0.7,
              strokeWeight: 3,
              map: map
            });
          @endif
        };

        if (typeof Livewire !== 'undefined') {
          Livewire.hook('morph.updated', () => {
            if (window.google && window.google.maps) {
              window.initAttendanceMap();
            }
          });
        }
      </script>
    @else
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
        <p class="text-sm font-medium mb-2">⚠️ Google Maps API Key not configured</p>
        <p class="text-xs">
          Please add <code class="bg-yellow-100 px-1 rounded">GOOGLE_MAPS_API_KEY</code> to your .env file to display the
          map.
        </p>
      </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      @if ($hasCheckInCoords)
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="font-semibold text-green-900 mb-2">✓ Check-In Location</p>
          <p class="text-green-700 text-xs">
            <strong>Time:</strong> {{ $record->check_in_time->format('d/m/Y H:i:s') }}
          </p>
          <p class="text-green-700 text-xs mt-1">
            <strong>Coordinates:</strong><br>
            {{ number_format($record->check_in_latitude, 6) }}, {{ number_format($record->check_in_longitude, 6) }}
          </p>
        </div>
      @endif

      @if ($hasCheckOutCoords)
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="font-semibold text-red-900 mb-2">✓ Check-Out Location</p>
          <p class="text-red-700 text-xs">
            <strong>Time:</strong> {{ $record->check_out_time?->format('d/m/Y H:i:s') ?? 'N/A' }}
          </p>
          <p class="text-red-700 text-xs mt-1">
            <strong>Coordinates:</strong><br>
            {{ number_format($record->check_out_latitude, 6) }}, {{ number_format($record->check_out_longitude, 6) }}
          </p>
        </div>
      @endif
    </div>
  @else
    <div class="p-6 bg-gray-50 border border-gray-300 rounded-lg text-center">
      <p class="text-gray-600">📍 No location data available for this attendance record.</p>
    </div>
  @endif
</div>
