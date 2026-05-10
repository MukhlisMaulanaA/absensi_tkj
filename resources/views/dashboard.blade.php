<x-app-layout>
  <x-slot name="header">
    <div class="flex justify-between items-center">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
      </h2>
    </div>
  </x-slot>

  <div class="py-8 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen" x-data="attendanceApp()"
    x-init="init()">

    <!-- A. IDENTITY ZONE (HEADER) -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-600">
        <div class="flex items-center justify-between">
          <!-- Profile Section -->
          <div class="flex items-center gap-4">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">
              {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
              <p class="text-sm text-gray-600">{{ __('Greeting') }},</p>
              <h3 class="text-2xl font-bold text-gray-900">{{ explode(' ', $user->name)[0] }}!</h3>
              <p class="text-sm text-gray-500 mt-1">{{ $user->role === 'admin' ? 'Administrator' : 'Employee' }} •
                {{ $user->location?->name ?? 'No Location Assigned' }}</p>
            </div>
          </div>

          <!-- Today's Status Badge -->
          <div class="text-right">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">{{ __('Today Status') }}</p>
            @if ($todayStatus['status'] === 'not_checked_in')
              <span
                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 border border-yellow-300">
                <span class="w-2 h-2 bg-yellow-600 rounded-full mr-2"></span>
                {{ __('Not Checked In') }}
              </span>
            @elseif($todayStatus['status'] === 'checked_in')
              <span
                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-300">
                <span class="w-2 h-2 bg-blue-600 rounded-full mr-2 animate-pulse"></span>
                {{ __('Checked In') }}
              </span>
            @else
              <span
                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-300">
                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                {{ __('Checked Out') }}
              </span>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- B. MAIN ZONE (HERO ACTION) -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
      <div class="bg-white rounded-lg shadow-lg p-8 text-center space-y-6">

        <!-- Digital Clock & Date -->
        <div>
          <div class="text-6xl font-bold text-indigo-600 font-mono tracking-wider mb-2" x-text="currentTime">
            00:00:00
          </div>
          <p class="text-lg text-gray-600" x-text="currentDate">Loading...</p>
        </div>

        <!-- Attendance Trigger Button -->
        <div class="flex justify-center">
          @if ($todayStatus['status'] === 'not_checked_in')
            <button @click="openModal('check-in')"
              class="w-40 h-40 rounded-full bg-gradient-to-br from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white font-bold text-2xl shadow-lg hover:shadow-2xl transform transition hover:scale-105 focus:outline-none flex flex-col items-center justify-center gap-2">
              <span class="text-4xl">📍</span>
              <span>{{ __('Check In') }}</span>
            </button>
          @elseif($todayStatus['status'] === 'checked_in')
            <button @click="openModal('check-out')"
              class="w-40 h-40 rounded-full bg-gradient-to-br from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white font-bold text-2xl shadow-lg hover:shadow-2xl transform transition hover:scale-105 focus:outline-none flex flex-col items-center justify-center gap-2">
              <span class="text-4xl">🚪</span>
              <span>{{ __('Check Out') }}</span>
            </button>
          @else
            <button type="button" disabled
              class="w-40 h-40 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 text-white font-bold text-2xl shadow-lg flex flex-col items-center justify-center gap-2 opacity-50 cursor-not-allowed">
              <span class="text-4xl">✓</span>
              <span>{{ __('Completed') }}</span>
            </button>
          @endif
        </div>

        <!-- Check In / Check Out Time Record -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-gray-600 font-semibold mb-2">{{ __('Check In Time') }}</p>
            <p class="text-2xl font-mono font-bold text-blue-700">
              @if ($todayStatus['data'])
                {{ $todayStatus['data']->check_in_time->format('H:i') }}
              @else
                --:--
              @endif
            </p>
          </div>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-xs uppercase tracking-wide text-gray-600 font-semibold mb-2">{{ __('Check Out Time') }}</p>
            <p class="text-2xl font-mono font-bold text-red-700">
              @if ($todayStatus['data'] && $todayStatus['data']->check_out_time)
                {{ $todayStatus['data']->check_out_time->format('H:i') }}
              @else
                --:--
              @endif
            </p>
          </div>
        </div>

      </div>
    </div>

    <!-- C. SECONDARY ACTION & NAVIGATION ZONE -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-8">

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
          <div class="flex gap-4 flex-wrap">
            <a href="{{ route('overtime.create') }}"
              class="inline-flex items-center px-6 py-3 border-2 border-amber-400 text-amber-600 font-semibold rounded-lg hover:bg-amber-50 transition">
              <span class="mr-2">⏰</span>
              {{ __('Request Overtime') }}
            </a>
            <a href="{{ route('attendance.page') }}"
              class="inline-flex items-center px-6 py-3 border-2 border-indigo-400 text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition">
              <span class="mr-2">📋</span>
              {{ __('View All History') }}
            </a>
          </div>
        </div>

        <!-- Recent Attendance History -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Recent Attendance History') }}</h3>

          @if ($recentAttendances->count() > 0)
            <div class="space-y-3">
              @foreach ($recentAttendances as $attendance)
                <div
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                  <div>
                    <p class="font-semibold text-gray-900">{{ $attendance->check_in_time->format('l, d M Y') }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                      <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium mr-2">
                        In: {{ $attendance->check_in_time->format('H:i') }}
                      </span>
                      @if ($attendance->check_out_time)
                        <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">
                          Out: {{ $attendance->check_out_time->format('H:i') }}
                        </span>
                      @else
                        <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                          Not checked out
                        </span>
                      @endif
                    </p>
                  </div>
                  <div class="text-right">
                    @if ($attendance->is_within_radius)
                      <span
                        class="inline-flex items-center text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded">
                        ✓ {{ __('On Site') }}
                      </span>
                    @else
                      <span
                        class="inline-flex items-center text-xs font-semibold text-red-700 bg-red-100 px-2 py-1 rounded">
                        ✗ {{ __('Off Site') }}
                      </span>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
              <p class="text-gray-600">{{ __('No attendance records yet') }}</p>
            </div>
          @endif

          <div class="mt-4 text-center">
            <a href="{{ route('attendance.page') }}"
              class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">
              {{ __('View Complete History') }} →
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- ===================== MODAL ABSENSI ===================== -->
    <div x-show="modalOpen" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-end"
      @click.self="closeModal()">

      <div class="bg-white w-full max-w-lg mx-auto rounded-t-2xl shadow-2xl z-50 max-h-[90vh] overflow-y-auto">

        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-800">
            <span x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span>
          </h2>
          <button @click="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 space-y-4">

          <!-- Time Display -->
          <div class="text-center bg-gray-50 p-4 rounded-lg">
            <p class="text-3xl font-bold text-gray-800" x-text="currentTime"></p>
            <p class="text-sm text-gray-600" x-text="currentDate"></p>
          </div>

          <!-- Location Info -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-gray-700 font-semibold">Latitude:</span>
              <span class="text-gray-900 font-mono" x-text="latitude ? latitude.toFixed(6) : 'Mengambil...'"></span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-700 font-semibold">Longitude:</span>
              <span class="text-gray-900 font-mono" x-text="longitude ? longitude.toFixed(6) : 'Mengambil...'"></span>
            </div>
            <button @click="refreshLocation()" :disabled="locationLoading"
              class="w-full mt-3 bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded transition">
              <span x-show="!locationLoading">🔄 Refresh Lokasi</span>
              <span x-show="locationLoading">Mengambil lokasi...</span>
            </button>
          </div>

          <!-- Map -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Peta Lokasi</label>
            <div :id="'map-' + modalType" class="w-full h-64 rounded-lg border border-gray-300"></div>
          </div>

          <!-- Camera Section -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Ambil Foto Selfie</label>

            <template x-if="!photoPreview">
              <div class="space-y-3">
                <video x-ref="modalVideo" autoplay playsinline class="w-full rounded-lg bg-black"></video>
                <button @click="capturePhoto()"
                  class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                  📷 Ambil Foto
                </button>
              </div>
            </template>

            <template x-if="photoPreview">
              <div class="space-y-3">
                <img :src="photoPreview" class="w-full rounded-lg" />
                <button @click="retakePhoto()"
                  class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition">
                  🔄 Ambil Ulang
                </button>
              </div>
            </template>
          </div>

          <canvas x-ref="modalCanvas" class="hidden"></canvas>

        </div>

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 space-y-2">
          <button @click="submit()" :disabled="!photo || !latitude || !longitude || submitting"
            class="w-full bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition">
            <span x-show="!submitting">✓ Konfirmasi <span
                x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span></span>
            <span x-show="submitting">Sedang memproses...</span>
          </button>
          <button @click="closeModal()"
            class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded-lg transition">
            Batal
          </button>
        </div>

      </div>
    </div>
    <!-- ==================== END MODAL ========================= -->

  </div>

  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
    function attendanceApp() {
      return {
        modalOpen: false,
        modalType: null,
        latitude: null,
        longitude: null,
        photo: null,
        photoPreview: null,
        currentTime: '00:00:00',
        currentDate: '',
        map: null,
        marker: null,
        submitting: false,
        locationLoading: false,

        init() {
          this.startClock();
          this.getLocation();
        },

        startClock() {
          const update = () => {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', {
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit'
            });
            this.currentDate = now.toLocaleDateString('{{ app()->getLocale() }}', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            });
          };
          update();
          setInterval(update, 1000);
        },

        getLocation() {
          this.locationLoading = true;
          if (!navigator.geolocation) {
            this.showSweetAlert('Error', 'Geolocation tidak didukung browser Anda', 'error');
            this.locationLoading = false;
            return;
          }
          navigator.geolocation.getCurrentPosition(
            position => {
              this.latitude = position.coords.latitude;
              this.longitude = position.coords.longitude;
              this.locationLoading = false;
              if (this.modalOpen && this.map) this.initMapWithCoordinates();
            },
            () => {
              this.showSweetAlert('Error', 'Gagal mengambil lokasi. Pastikan GPS aktif.', 'error');
              this.locationLoading = false;
            }, {
              enableHighAccuracy: true,
              timeout: 10000,
              maximumAge: 0
            }
          );
        },

        refreshLocation() {
          this.getLocation();
        },

        initMapWithCoordinates() {
          if (!this.latitude || !this.longitude) return;
          const mapElement = document.getElementById('map-' + this.modalType);
          if (!mapElement) return;
          const position = {
            lat: this.latitude,
            lng: this.longitude
          };
          if (!this.map) {
            this.map = new google.maps.Map(mapElement, {
              zoom: 18,
              center: position,
              mapTypeControl: false,
              fullscreenControl: false
            });
            this.marker = new google.maps.Marker({
              position,
              map: this.map,
              title: 'Lokasi Anda'
            });
          } else {
            this.map.setCenter(position);
            this.marker.setPosition(position);
          }
        },

        openModal(type) {
          this.modalType = type;
          this.modalOpen = true;
          this.photo = null;
          this.photoPreview = null;
          this.message = '';
          this.$nextTick(() => {
            this.openCamera();
            this.initMapWithCoordinates();
          });
        },

        closeModal() {
          this.modalOpen = false;
          this.stopCamera();
          this.photo = null;
          this.photoPreview = null;
          this.map = null;
        },

        openCamera() {
          navigator.mediaDevices.getUserMedia({
            video: {
              facingMode: 'user',
              width: {
                ideal: 1280
              },
              height: {
                ideal: 720
              }
            },
            audio: false
          }).then(stream => {
            if (this.$refs.modalVideo) {
              this.$refs.modalVideo.srcObject = stream;
              this.$refs.modalVideo.play();
            }
          }).catch(err => {
            this.showSweetAlert('Camera Error', 'Gagal mengakses kamera: ' + err.message, 'error');
          });
        },

        stopCamera() {
          if (this.$refs.modalVideo && this.$refs.modalVideo.srcObject) {
            this.$refs.modalVideo.srcObject.getTracks().forEach(t => t.stop());
          }
        },

        capturePhoto() {
          const video = this.$refs.modalVideo;
          const canvas = this.$refs.modalCanvas;
          if (!canvas || !video) return;
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          const ctx = canvas.getContext('2d');
          ctx.translate(canvas.width, 0);
          ctx.scale(-1, 1);
          ctx.drawImage(video, 0, 0);
          this.photoPreview = canvas.toDataURL('image/jpeg');
          canvas.toBlob(blob => {
            this.photo = new File([blob], 'photo.jpg', {
              type: 'image/jpeg'
            });
          }, 'image/jpeg');
        },

        retakePhoto() {
          this.photo = null;
          this.photoPreview = null;
        },

        async submit() {
          if (!this.photo) {
            this.showSweetAlert('Warning', 'Ambil foto terlebih dahulu', 'warning');
            return;
          }
          if (!this.latitude || !this.longitude) {
            this.showSweetAlert('Error', 'Lokasi belum terdeteksi', 'error');
            return;
          }
          if (this.submitting) return;

          this.submitting = true;
          const formData = new FormData();
          formData.append('latitude', this.latitude);
          formData.append('longitude', this.longitude);
          formData.append('photo', this.photo);

          const url = this.modalType === 'check-in' ? '{{ route('check-in') }}' : '{{ route('check-out') }}';
          const actionType = this.modalType === 'check-in' ? 'Check In' : 'Check Out';

          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: formData
            });
            const result = await response.json();
            if (response.ok) {
              window.Swal.fire({
                title: 'Success!',
                text: result.message,
                icon: 'success',
                confirmButtonText: 'OK',
              }).then(() => {
                location.reload();
              });
            } else {
              this.showSweetAlert('Error', result.message, 'error');
            }
          } catch (err) {
            this.showSweetAlert('Error', 'Terjadi kesalahan: ' + err.message, 'error');
          } finally {
            this.submitting = false;
          }
        },

        showSweetAlert(title = 'Notification', message = '', icon = 'info') {
          if (!window.Swal) {
            console.warn('SweetAlert not loaded');
            alert(message);
            return;
          }
          window.Swal.fire({
            title: title,
            text: message,
            icon: icon,
            confirmButtonText: 'OK',
          });
        }
      }
    }
  </script>
</x-app-layout>
