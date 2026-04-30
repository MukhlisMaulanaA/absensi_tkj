<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>Absensi</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
</head>

<body class="bg-gray-100">

  <div x-data="attendanceApp()" x-init="init()" class="p-4 max-w-xl mx-auto">

    <h1 class="text-xl font-bold mb-4">Absensi Hari Ini</h1>

    <!-- MAP -->
    <div id="map" class="w-full h-64 rounded-lg mb-4"></div>

    <!-- COORDINATES -->
    <div class="mb-4 text-sm text-gray-700">
      <p>Latitude: <span x-text="latitude"></span></p>
      <p>Longitude: <span x-text="longitude"></span></p>
    </div>

    <!-- CAMERA -->
    <div class="mb-4">
      <video x-ref="video" autoplay class="w-full rounded-lg"></video>
      <canvas x-ref="canvas" class="hidden"></canvas>
    </div>

    <!-- PREVIEW -->
    <template x-if="photoPreview">
      <img :src="photoPreview" class="w-full rounded-lg mb-4" />
    </template>

    <!-- BUTTONS -->
    <div class="flex gap-2">
      <button @click="capturePhoto" class="bg-blue-500 text-white px-4 py-2 rounded">
        Ambil Foto
      </button>

      <button @click="submit('check-in')" class="bg-green-500 text-white px-4 py-2 rounded">
        Check In
      </button>

      <button @click="submit('check-out')" class="bg-red-500 text-white px-4 py-2 rounded">
        Check Out
      </button>
    </div>

    <!-- STATUS -->
    <div class="mt-4 text-sm text-gray-600" x-text="message"></div>

  </div>

  <script>
    function attendanceApp() {
      return {
        latitude: null,
        longitude: null,
        map: null,
        marker: null,
        photo: null,
        photoPreview: null,
        message: '',

        init() {
          this.initCamera();
          this.getLocation();
        },

        initCamera() {
          navigator.mediaDevices.getUserMedia({
              video: true
            })
            .then(stream => {
              this.$refs.video.srcObject = stream;
            });
        },

        getLocation() {
          navigator.geolocation.watchPosition(position => {
            this.latitude = position.coords.latitude;
            this.longitude = position.coords.longitude;

            this.initMap();
          }, error => {
            alert('Gagal mengambil lokasi');
          }, {
            enableHighAccuracy: true
          });
        },

        initMap() {
          const position = {
            lat: this.latitude,
            lng: this.longitude
          };

          if (!this.map) {
            this.map = new google.maps.Map(document.getElementById('map'), {
              zoom: 18,
              center: position
            });

            this.marker = new google.maps.Marker({
              position: position,
              map: this.map
            });
          } else {
            this.map.setCenter(position);
            this.marker.setPosition(position);
          }
        },

        capturePhoto() {
          const canvas = this.$refs.canvas;
          const video = this.$refs.video;

          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;

          const ctx = canvas.getContext('2d');
          ctx.drawImage(video, 0, 0);

          this.photoPreview = canvas.toDataURL('image/jpeg');

          canvas.toBlob(blob => {
            this.photo = new File([blob], 'photo.jpg', {
              type: 'image/jpeg'
            });
          }, 'image/jpeg');
        },

        async submit(type) {
          if (!this.photo) {
            alert('Ambil foto dulu!');
            return;
          }

          const formData = new FormData();
          formData.append('latitude', this.latitude);
          formData.append('longitude', this.longitude);
          formData.append('photo', this.photo);

          const url = type === 'check-in' ? '/check-in' : '/check-out';

          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: formData
            });

            const result = await response.json();
            this.message = result.message;

          } catch (error) {
            this.message = error.message;
          }
        }
      }
    }
  </script>

</body>

</html>
