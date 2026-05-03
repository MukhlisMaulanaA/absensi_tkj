<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>Absensi</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100">

  <div x-data="attendanceApp()" x-init="init()" class="p-4 max-w-xl mx-auto">

    <!-- MAIN PAGE -->
    <div class="space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-800">Absensi</h1>
        <p class="text-gray-500 mt-2">Sistem Presensi Karyawan</p>
      </div>

      <!-- STATUS CARD -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div id="statusContent" class="text-center">
          <p class="text-gray-600 text-sm mb-4">Silakan lakukan check-in untuk memulai</p>
          <p class="text-4xl font-bold text-blue-600" x-text="currentTime"></p>
        </div>
      </div>

      <!-- ACTION BUTTONS -->
      <div class="flex gap-3">
        <button x-show="attendanceStatus === 'not_checked_in' || attendanceStatus === 'checked_out'"
                @click="openModal('check-in')" 
                class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition">
          Check In
        </button>
        <button x-show="attendanceStatus === 'checked_in'"
                @click="openModal('check-out')" 
                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition">
          Check Out
        </button>
      </div>

      <!-- MESSAGE -->
      <template x-if="message">
        <div :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="p-4 rounded-lg">
          <p x-text="message"></p>
        </div>
      </template>
    </div>

    <!-- MODAL OVERLAY -->
    <div x-show="modalOpen" 
         x-transition 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-end"
         @click.self="closeModal()">
      
      <!-- MODAL -->
      <div class="bg-white w-full max-w-lg rounded-t-2xl shadow-2xl z-50 max-h-[90vh] overflow-y-auto">
        
        <!-- MODAL HEADER -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-800">
            <span x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span>
          </h2>
          <button @click="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
            &times;
          </button>
        </div>

        <!-- MODAL CONTENT -->
        <div class="p-6 space-y-4">

          <!-- TIME DISPLAY -->
          <div class="text-center bg-gray-50 p-4 rounded-lg">
            <p class="text-3xl font-bold text-gray-800" x-text="currentTime"></p>
            <p class="text-sm text-gray-600" x-text="currentDate"></p>
          </div>

          <!-- LOCATION INFO -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-gray-700 font-semibold">Latitude:</span>
              <span class="text-gray-900" x-text="latitude ? latitude.toFixed(6) : 'Mengambil...'" class="font-mono"></span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-700 font-semibold">Longitude:</span>
              <span class="text-gray-900" x-text="longitude ? longitude.toFixed(6) : 'Mengambil...'" class="font-mono"></span>
            </div>
            <button @click="refreshLocation()" 
                    :disabled="locationLoading"
                    class="w-full mt-3 bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded transition">
              <span x-show="!locationLoading">🔄 Refresh Lokasi</span>
              <span x-show="locationLoading">Mengambil lokasi...</span>
            </button>
          </div>

          <!-- MAP -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Peta Lokasi</label>
            <div :id="'map-' + modalType" class="w-full h-64 rounded-lg border border-gray-300"></div>
          </div>

          <!-- CAMERA SECTION -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Ambil Foto Selfie</label>
            
            <template x-if="!photoPreview">
              <div class="space-y-3">
                <video x-ref="modalVideo" 
                       autoplay 
                       playsinline 
                       class="w-full rounded-lg bg-black"></video>
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

        <!-- MODAL FOOTER -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 space-y-2">
          <button @click="submit()" 
                  :disabled="!photo || !latitude || !longitude || submitting"
                  class="w-full bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition">
            <span x-show="!submitting">✓ Konfirmasi <span x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span></span>
            <span x-show="submitting">Sedang memproses...</span>
          </button>
          <button @click="closeModal()" 
                  class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded-lg transition">
            Batal
          </button>
        </div>

      </div>

    </div>

  </div>

  <script>
    function attendanceApp() {
      return {
        // State
        modalOpen: false,
        modalType: null,
        latitude: null,
        longitude: null,
        photo: null,
        photoPreview: null,
        message: '',
        messageType: 'success',
        currentTime: '00:00:00',
        currentDate: '',
        map: null,
        marker: null,
        submitting: false,
        locationLoading: false,
        attendanceStatus: 'not_checked_in', // 'not_checked_in', 'checked_in', 'checked_out'

        init() {
          this.checkAttendanceStatus();
          this.initMap();
          this.getLocation();
          this.startClock();
          this.initCamera();
        },

        async checkAttendanceStatus() {
          try {
            const response = await fetch('/attendance/status', {
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              }
            });
            
            const result = await response.json();
            
            if (response.ok) {
              this.attendanceStatus = result.status;
              
              // Update status display
              if (result.status === 'checked_in') {
                document.getElementById('statusContent').innerHTML = `
                  <p class="text-green-600 text-sm mb-4">✓ Anda sudah check-in hari ini</p>
                  <p class="text-4xl font-bold text-green-600">${this.currentTime}</p>
                `;
              } else if (result.status === 'checked_out') {
                document.getElementById('statusContent').innerHTML = `
                  <p class="text-blue-600 text-sm mb-4">✓ Anda sudah check-out hari ini</p>
                  <p class="text-4xl font-bold text-blue-600">${this.currentTime}</p>
                `;
              }
            }
          } catch (error) {
            console.error('Error checking attendance status:', error);
          }
        },

        startClock() {
          const updateClock = () => {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { 
              hour: '2-digit', 
              minute: '2-digit', 
              second: '2-digit' 
            });
            this.currentDate = now.toLocaleDateString('id-ID', { 
              weekday: 'long', 
              year: 'numeric', 
              month: 'long', 
              day: 'numeric' 
            });
          };
          updateClock();
          setInterval(updateClock, 1000);
        },

        initCamera() {
          if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.error('Camera API not available');
            return;
          }
        },

        openCamera() {
          navigator.mediaDevices.getUserMedia({
              video: { 
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
              },
              audio: false
            })
            .then(stream => {
              if (this.$refs.modalVideo) {
                this.$refs.modalVideo.srcObject = stream;
                this.$refs.modalVideo.play();
              }
            })
            .catch(error => {
              this.showMessage('Gagal mengakses kamera: ' + error.message, 'error');
            });
        },

        stopCamera() {
          if (this.$refs.modalVideo && this.$refs.modalVideo.srcObject) {
            this.$refs.modalVideo.srcObject.getTracks().forEach(track => track.stop());
          }
        },

        getLocation() {
          this.locationLoading = true;
          if (!navigator.geolocation) {
            this.showMessage('Geolocation tidak didukung browser Anda', 'error');
            this.locationLoading = false;
            return;
          }

          navigator.geolocation.getCurrentPosition(
            position => {
              this.latitude = position.coords.latitude;
              this.longitude = position.coords.longitude;
              this.locationLoading = false;
              if (this.modalOpen && this.map) {
                this.initMapWithCoordinates();
              }
            },
            error => {
              this.showMessage('Gagal mengambil lokasi. Pastikan GPS aktif.', 'error');
              this.locationLoading = false;
            },
            {
              enableHighAccuracy: true,
              timeout: 10000,
              maximumAge: 0
            }
          );
        },

        refreshLocation() {
          this.getLocation();
        },

        initMap() {
          // Initialize empty maps for both modal types
        },

        initMapWithCoordinates() {
          if (!this.latitude || !this.longitude) return;

          const mapId = 'map-' + this.modalType;
          const mapElement = document.getElementById(mapId);
          
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
              position: position,
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
          
          // Wait for DOM to update then initialize camera and map
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
          // Refresh attendance status when closing modal
          this.checkAttendanceStatus();
        },

        capturePhoto() {
          const video = this.$refs.modalVideo;
          const canvas = this.$refs.modalCanvas;

          if (!canvas || !video) return;

          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;

          const ctx = canvas.getContext('2d');
          // Flip horizontally for selfie
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
            this.showMessage('Ambil foto terlebih dahulu', 'error');
            return;
          }

          if (!this.latitude || !this.longitude) {
            this.showMessage('Lokasi belum terdeteksi', 'error');
            return;
          }

          // Prevent double submission
          if (this.submitting) {
            return;
          }

          this.submitting = true;

          const formData = new FormData();
          formData.append('latitude', this.latitude);
          formData.append('longitude', this.longitude);
          formData.append('photo', this.photo);

          const url = this.modalType === 'check-in' ? '/check-in' : '/check-out';

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
              this.showMessage(result.message, 'success');
              
              // Update status and close modal after a brief delay
              await this.checkAttendanceStatus();
              
              setTimeout(() => {
                this.closeModal();
              }, 500);
            } else {
              this.showMessage(result.message, 'error');
            }
          } catch (error) {
            this.showMessage('Terjadi kesalahan: ' + error.message, 'error');
          } finally {
            this.submitting = false;
          }
        },

        showMessage(msg, type = 'success') {
          this.message = msg;
          this.messageType = type;
          setTimeout(() => {
            this.message = '';
          }, 5000);
        }
      }
    }
  </script>

</body>

</html>
