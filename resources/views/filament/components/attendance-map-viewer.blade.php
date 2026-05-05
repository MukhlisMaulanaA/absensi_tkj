@php
  $hasCheckInCoords = $record->check_in_latitude && $record->check_in_longitude;
  $hasCheckOutCoords = $record->check_out_latitude && $record->check_out_longitude;
  $mapId = 'attendance-map-' . $record->id;
@endphp

<div class="space-y-4">
  @if ($hasCheckInCoords || $hasCheckOutCoords)
    <div class="rounded-lg overflow-hidden border border-gray-300 shadow-sm">
      <div id="{{ $mapId }}" style="width: 100%; height: 400px;"></div>
    </div>

    @if ($apiKey)
      <script>
        (function() {
          async function initAttendanceMap() {
            // Pastikan objek google sudah ada di window sebelum eksekusi lebih jauh
            if (typeof google === 'undefined') {
              console.warn("Google Maps API belum siap. Menunggu...");
              return;
            }

            const mapElement = document.getElementById('{{ $mapId }}');
            if (!mapElement) return;

            // Import library yang dibutuhkan secara asinkron
            const {
              Map
            } = await google.maps.importLibrary("maps");
            const {
              Marker
            } = await google.maps.importLibrary("marker");

            // Persiapkan koordinat dari database (Blade)
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

            // Inisialisasi Map
            const map = new Map(mapElement, {
              zoom: 16,
              center: centerLatLng,
              mapId: "DEMO_MAP_ID", // Tambahkan Map ID jika ingin menggunakan Advanced Markers
              mapTypeControl: true,
              fullscreenControl: true,
              streetViewControl: false
            });

            // Tambahkan Marker Check-In
            @if ($hasCheckInCoords)
              new Marker({
                position: checkInLatLng,
                map: map,
                title: 'Check-In: {{ $record->check_in_time->format('d/m/Y H:i') }}',
                // Gunakan properti 'icon' jika masih menggunakan marker biasa
                icon: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
              });
            @endif

            // Tambahkan Marker Check-Out
            @if ($hasCheckOutCoords)
              new Marker({
                position: checkOutLatLng,
                map: map,
                title: 'Check-Out: {{ $record->check_out_time?->format('d/m/Y H:i') ?? 'N/A' }}',
                icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
              });
            @endif

            // Gambar Garis (Polyline)
            @if ($hasCheckInCoords && $hasCheckOutCoords)
              new google.maps.Polyline({
                path: [checkInLatLng, checkOutLatLng],
                geodesic: true,
                strokeColor: '#4F46E5',
                strokeOpacity: 0.7,
                strokeWeight: 3,
                map: map
              });
            @endif
          }

          // Fungsi Loader
          function loadGoogleMapsAndInit() {
            if (window.google && window.google.maps && window.google.maps.importLibrary) {
              initAttendanceMap();
              return;
            }

            if (document.querySelector('script[data-gmaps-bootstrap]')) {
              window.addEventListener('google-maps-ready', initAttendanceMap, {
                once: true
              });
              return;
            }

            window.__googleMapsBootstrap = function() {
              window.dispatchEvent(new Event('google-maps-ready'));
              initAttendanceMap();
            };

            const script = document.createElement('script');
            script.setAttribute('data-gmaps-bootstrap', '1');
            // Pastikan URL menggunakan v=beta atau versi terbaru untuk mendukung importLibrary
            script.src =
              `https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=__googleMapsBootstrap&libraries=geometry&loading=async`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
          }

          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadGoogleMapsAndInit);
          } else {
            loadGoogleMapsAndInit();
          }
        })();
      </script>
    @else
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
        <p class="text-sm">
          <strong>Google Maps API Key not configured.</strong> Please add GOOGLE_MAPS_API_KEY to your .env file to
          display the map.
        </p>
      </div>
    @endif

    <div class="grid grid-cols-2 gap-4 text-sm">
      @if ($hasCheckInCoords)
        <div class="p-3 bg-green-50 border border-green-200 rounded">
          <p class="font-semibold text-green-900">Check-In Location</p>
          <p class="text-green-700 mt-1">
            📍 {{ number_format($record->check_in_latitude, 6) }}, {{ number_format($record->check_in_longitude, 6) }}
          </p>
        </div>
      @endif

      @if ($hasCheckOutCoords)
        <div class="p-3 bg-red-50 border border-red-200 rounded">
          <p class="font-semibold text-red-900">Check-Out Location</p>
          <p class="text-red-700 mt-1">
            📍 {{ number_format($record->check_out_latitude, 6) }},
            {{ number_format($record->check_out_longitude, 6) }}
          </p>
        </div>
      @endif
    </div>
  @else
    <div class="p-4 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 text-center">
      <p>No location data available for this attendance record.</p>
    </div>
  @endif
</div>
