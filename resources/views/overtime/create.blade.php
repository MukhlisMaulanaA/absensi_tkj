<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Request Lembur') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
        <div class="p-6 text-gray-900">

          @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
          @endif

          @if ($errors->any())
            <div class="mb-4">
              <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @php
            $today = now()->toDateString(); // "2025-05-07"
            $tomorrow = now()->addDay()->toDateString(); // "2025-05-08"
            $dayName = now()->translatedFormat('l, d F Y');
          @endphp

          <h3 class="text-base font-semibold text-gray-800 mb-1">Request Lembur</h3>

          <div
            class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg px-3 py-1.5 text-xs font-medium mb-6">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <path d="M16 2v4M8 2v4M3 10h18" />
            </svg>
            {{ $dayName }}
          </div>

          <form method="POST" action="{{ route('overtime.store') }}" id="overtimeForm">
            @csrf
            <input type="hidden" name="start_time" id="start_time_input">
            <input type="hidden" name="end_time" id="end_time_input">
            <input type="hidden" name="latitude" id="latitude_input">
            <input type="hidden" name="longitude" id="longitude_input">

            <div class="grid grid-cols-1 gap-6">

              {{-- Preset Cepat --}}
              <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Preset Cepat</p>
                <div class="flex flex-wrap gap-2">
                  @foreach ([['16:00', '18:00', false], ['16:00', '19:00', false], ['16:00', '20:00', false], ['16:00', '21:00', false], ['16:00', '22:00', false], ['16:00', '23:00', false], ['16:00', '23:59', false], ['16:00', '02:00', true], ['16:00', '04:00', true], ['16:00', '06:00', true]] as [$s, $e, $nextDay])
                    <button type="button"
                      onclick="setPreset('{{ $s }}','{{ $e }}',{{ $nextDay ? 'true' : 'false' }})"
                      class="preset-btn px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-xs font-mono font-medium text-gray-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-colors">
                      {{ $s }}–{{ $e }}{{ $nextDay ? ' +1' : '' }}
                    </button>
                  @endforeach
                </div>
              </div>

              {{-- Time Pickers --}}
              <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Jam Lembur</p>
                <div class="grid grid-cols-2 gap-4">

                  {{-- START --}}
                  <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Mulai</label>
                    <div
                      class="bg-gray-50 border-2 border-gray-200 rounded-xl p-3 focus-within:border-blue-500 focus-within:bg-blue-50 transition-colors">
                      <div
                        class="font-mono text-3xl font-semibold text-gray-800 text-center tracking-wider mb-2 leading-none">
                        <span id="startH">16</span><span class="text-gray-300">:</span><span id="startM">00</span>
                      </div>
                      <div class="flex items-end gap-1.5">
                        <div class="flex flex-col items-center gap-1 flex-1">
                          <button type="button" onclick="adj('startH', 1)" class="spin-btn">▲</button>
                          <span class="text-[10px] text-gray-400 font-medium">JAM</span>
                          <button type="button" onclick="adj('startH', -1)" class="spin-btn">▼</button>
                        </div>
                        <div class="text-gray-300 font-bold pb-5">:</div>
                        <div class="flex flex-col items-center gap-1 flex-1">
                          <button type="button" onclick="adjStep('startM', 1)" class="spin-btn">▲</button>
                          <span class="text-[10px] text-gray-400 font-medium">MENIT</span>
                          <button type="button" onclick="adjStep('startM', -1)" class="spin-btn">▼</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- END --}}
                  <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">
                      Selesai
                      <span id="nextDayBadge"
                        class="hidden ml-1 text-[10px] font-semibold bg-amber-100 text-amber-600 border border-amber-200 rounded px-1.5 py-0.5">+</span>
                    </label>
                    <div
                      class="bg-gray-50 border-2 border-gray-200 rounded-xl p-3 focus-within:border-blue-500 focus-within:bg-blue-50 transition-colors">
                      <div
                        class="font-mono text-3xl font-semibold text-gray-800 text-center tracking-wider mb-2 leading-none">
                        <span id="endH">20</span><span class="text-gray-300">:</span><span id="endM">00</span>
                      </div>
                      <div class="flex items-end gap-1.5">
                        <div class="flex flex-col items-center gap-1 flex-1">
                          <button type="button" onclick="adj('endH', 1)" class="spin-btn">▲</button>
                          <span class="text-[10px] text-gray-400 font-medium">JAM</span>
                          <button type="button" onclick="adj('endH', -1)" class="spin-btn">▼</button>
                        </div>
                        <div class="text-gray-300 font-bold pb-5">:</div>
                        <div class="flex flex-col items-center gap-1 flex-1">
                          <button type="button" onclick="adjStep('endM', 1)" class="spin-btn">▲</button>
                          <span class="text-[10px] text-gray-400 font-medium">MENIT</span>
                          <button type="button" onclick="adjStep('endM', -1)" class="spin-btn">▼</button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              {{-- Duration --}}
              <div class="bg-gray-50 rounded-xl px-4 py-3 flex justify-between items-center text-sm">
                <span class="text-gray-500 font-medium">⏱ Durasi lembur</span>
                <span id="durationDisplay" class="font-mono font-semibold text-gray-800">—</span>
              </div>

              {{-- Overtime Calculation Result --}}
              <div class="bg-white border border-blue-200 rounded-xl overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-blue-200">
                  <h4 class="text-sm font-semibold text-blue-900">📋 Rincian Perhitungan Lembur</h4>
                </div>
                <div class="p-4">
                  <table class="w-full text-sm">
                    <tbody>
                      <tr class="border-b border-gray-200 last:border-0">
                        <td class="text-gray-600 py-2">Jam Mulai</td>
                        <td class="text-right font-mono font-semibold text-gray-800">
                          <span id="calcStartDisplay">16:00</span>
                        </td>
                      </tr>
                      <tr class="border-b border-gray-200 last:border-0">
                        <td class="text-gray-600 py-2">Jam Selesai</td>
                        <td class="text-right font-mono font-semibold text-gray-800">
                          <span id="calcEndDisplay">20:00</span> <span id="calcNextDayTag"
                            class="hidden text-xs bg-amber-100 text-amber-600 rounded px-2 py-0.5">+</span>
                        </td>
                      </tr>
                      <tr class="border-b border-gray-200 last:border-0">
                        <td class="text-gray-600 py-2">Durasi</td>
                        <td class="text-right font-mono font-semibold text-gray-800">
                          <span id="calcDurationDisplay">4 jam</span>
                        </td>
                      </tr>
                      <tr class="bg-blue-50 border-t border-blue-200">
                        <td class="text-blue-900 font-semibold py-2">Hari Lembur</td>
                        <td class="text-right">
                          <span id="calcOvertimeDays"
                            class="inline-flex items-center gap-1 bg-blue-600 text-white font-bold px-3 py-1 rounded-lg text-sm">
                            <span class="text-lg">→</span> 1 Hari
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-xs text-amber-800 font-medium">
                      <span class="font-semibold">⚠️ Catatan Perhitungan:</span><br>
                      • Mulai dari 16:00 = 1 hari<br>
                      • Sampai 02:00 dini hari = 2 hari<br>
                      • Sampai 06:00 pagi = 3 hari
                    </p>
                  </div>
                </div>
              </div>

              {{-- Description --}}
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Keterangan<span class="text-red-400 font-normal"> (wajib diisi)</span>
                </label>
                <textarea name="description" rows="3"
                  class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  placeholder="..." required>{{ old('description') }}</textarea>
              </div>

              {{-- Image Upload (Multiple) --}}
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Lampirkan Gambar<span class="text-red-400 font-normal"> (wajib dilampirkan, maks 5 gambar)</span>
                </label>
                <div class="relative">
                  <!-- 1. Menambahkan 'multiple' dan mengubah name menjadi format array [] -->
                  <input type="file" name="images[]" id="imageInput" accept="image/*" class="hidden" multiple
                    required />

                  <label for="imageInput"
                    class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-colors">
                    <div class="text-center">
                      <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      <p id="imageLabel" class="text-sm text-gray-600">
                        <span class="font-medium text-blue-600">Klik untuk memilih</span> atau seret hingga 5 gambar di
                        sini
                      </p>
                      <!-- Tempat menampilkan jumlah file terpilih -->
                      <p id="imageCount" class="text-xs text-gray-400 mt-1"></p>
                    </div>
                  </label>

                  <!-- 2. Mengubah single img menjadi Grid Container untuk banyak preview gambar -->
                  <div id="imagePreviewContainer"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mt-3 hidden">
                    <!-- Preview gambar akan di-inject lewat JavaScript di sini -->
                  </div>
                </div>
              </div>

              {{-- Location Map --}}
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Lokasi Permintaan</label>
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                  <div id="overtime-map" style="width:100%;height:320px;"></div>
                  <div class="p-3 flex items-center justify-between">
                    <div class="text-sm text-gray-600">Koordinat: <span id="coordsDisplay">—</span></div>
                    <div class="flex items-center gap-3">
                      <button type="button" id="useLocationBtn"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs">Gunakan
                        Lokasi Saya</button>
                      <button type="button" id="clearLocationBtn"
                        class="text-sm text-red-500 underline">Hapus</button>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Actions --}}
              <div class="flex items-center gap-3">
                <button type="submit" id="submitBtn"
                  class="inline-flex items-center px-5 py-2.5 bg-blue-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition-colors">
                  Kirim Permintaan
                </button>
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-400 hover:text-gray-700">Batal</a>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <style>
    .spin-btn {
      @apply w-full bg-white border border-gray-200 rounded-md py-1 text-[10px] text-gray-500 cursor-pointer transition-colors flex items-center justify-center hover:bg-blue-600 hover:text-white hover:border-blue-600;
    }
  </style>

  <script>
    const TODAY = "{{ $today }}"; // e.g. "2025-05-07"
    const TOMORROW = "{{ $tomorrow }}"; // e.g. "2025-05-08"
    const STEPS = [0, 15, 30, 45];

    // endH stored as 0-47 internally (24–47 = next day 00–23)
    const state = {
      startH: 16,
      startM: 0,
      endH: 20,
      endM: 0
    };

    function pad(n) {
      return String(n).padStart(2, '0');
    }

    function render() {
      document.getElementById('startH').textContent = pad(state.startH);
      document.getElementById('startM').textContent = pad(state.startM);

      // End hour: display as actual clock time (mod 24)
      const endDisplay = state.endH % 24;
      const isNextDay = state.endH >= 24;
      document.getElementById('endH').textContent = pad(endDisplay);
      document.getElementById('endM').textContent = pad(state.endM);

      // Show/hide +1 hari badge
      const badge = document.getElementById('nextDayBadge');
      isNextDay ? badge.classList.remove('hidden') : badge.classList.add('hidden');

      updateDuration();
    }

    function adj(field, delta) {
      if (field === 'startH') {
        state.startH = Math.max(0, Math.min(23, state.startH + delta));
      } else {
        // endH: 0–47 (allows up to 23:xx next day)
        state.endH = Math.max(0, Math.min(47, state.endH + delta));
      }
      render();
    }

    function adjStep(field, delta) {
      const cur = field === 'startM' ? state.startM : state.endM;
      let idx = STEPS.indexOf(cur);
      if (idx === -1) idx = 0;
      idx = (idx + delta + STEPS.length) % STEPS.length;
      if (field === 'startM') state.startM = STEPS[idx];
      else state.endM = STEPS[idx];
      render();
    }

    // nextDay: jika true, endH akan di-set sebagai 24+jam (e.g. 01:00 esok = 25)
    function setPreset(start, end, nextDay) {
      const [sh, sm] = start.split(':').map(Number);
      const [eh, em] = end.split(':').map(Number);
      state.startH = sh;
      state.startM = sm;
      state.endH = nextDay ? 24 + eh : eh;
      state.endM = em;
      render();
    }

    function updateDuration() {
      const startMins = state.startH * 60 + state.startM;
      const endMins = state.endH * 60 + state.endM; // endH sudah 0-47
      const diff = endMins - startMins;
      const el = document.getElementById('durationDisplay');

      if (diff <= 0) {
        el.textContent = 'Waktu tidak valid';
        el.className = 'font-mono font-semibold text-red-500';
      } else {
        const h = Math.floor(diff / 60),
          m = diff % 60;
        el.textContent = (h ? h + ' jam ' : '') + (m ? m + ' menit' : '');
        el.className = 'font-mono font-semibold text-gray-800';
      }

      // Update overtime calculation display
      updateOvertimeCalculation();
    }

    function updateOvertimeCalculation() {
      const pad2 = n => String(n).padStart(2, '0');

      // Display start time
      document.getElementById('calcStartDisplay').textContent = `${pad2(state.startH)}:${pad2(state.startM)}`;

      // Display end time
      const endHourDisplay = state.endH % 24;
      const isNextDay = state.endH >= 24;
      document.getElementById('calcEndDisplay').textContent = `${pad2(endHourDisplay)}:${pad2(state.endM)}`;

      const nextDayTag = document.getElementById('calcNextDayTag');
      isNextDay ? nextDayTag.classList.remove('hidden') : nextDayTag.classList.add('hidden');

      // Calculate and display duration
      const startMins = state.startH * 60 + state.startM;
      const endMins = state.endH * 60 + state.endM;
      const diff = endMins - startMins;

      if (diff > 0) {
        const h = Math.floor(diff / 60);
        const m = diff % 60;
        document.getElementById('calcDurationDisplay').textContent =
          (h ? h + ' jam ' : '') + (m ? m + ' menit' : '');
      } else {
        document.getElementById('calcDurationDisplay').textContent = 'Tidak valid';
      }

      // Calculate overtime days based on company regulations
      const overtimeDays = calculateOvertimeDays();
      updateOvertimeDaysDisplay(overtimeDays);
    }

    function calculateOvertimeDays() {
      // Company Regulation:
      // 1. Overtime starts from 16:00
      // 2. If continues until 02:00 (early hours of following day) = 2 days
      // 3. If continues until 06:00 (morning of following day) = 3 days
      // 4. Pattern continues for additional periods

      const startMins = state.startH * 60 + state.startM;
      const endMins = state.endH * 60 + state.endM;
      const diff = endMins - startMins;

      if (diff <= 0) {
        return 0;
      }

      // If spans to next day (endH >= 24)
      if (state.endH >= 24) {
        const endHourNextDay = state.endH % 24;

        // Check thresholds based on time on the next day
        if (endHourNextDay >= 6) {
          // Reaches 06:00 or beyond = 3 days
          return 3;
        } else if (endHourNextDay >= 2) {
          // Reaches 02:00 to 05:59 = 3 days
          return 2;
        } else {
          // 00:00 to 01:59 (early hours) = 2 days
          return 0;
        }
      }

      // Same day only = 1 day
      return 0;
    }

    function updateOvertimeDaysDisplay(days) {
      const daysLabel = ['', '1 Hari', '2 Hari', '3 Hari', '4 Hari', '5 Hari'];
      const label = daysLabel[Math.min(days, 5)] || days + ' Hari';

      const badge = document.getElementById('calcOvertimeDays');
      badge.innerHTML = `<span class="text-lg">→</span> ${label}`;

      // Add visual emphasis for multi-day overtime
      if (days >= 2) {
        badge.classList.add('ring-2', 'ring-orange-300');
      } else {
        badge.classList.remove('ring-2', 'ring-orange-300');
      }
    }

    document.getElementById('overtimeForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const diff = (state.endH * 60 + state.endM) - (state.startH * 60 + state.startM);
      if (diff <= 0) {
        if (window.Swal) {
          window.Swal.fire({
            title: 'Waktu Tidak Valid',
            text: 'Waktu selesai harus lebih dari waktu mulai.',
            icon: 'error',
            confirmButtonText: 'OK',
          });
        } else {
          alert('Waktu selesai harus lebih dari waktu mulai.');
        }
        return;
      }

      const pad2 = n => String(n).padStart(2, '0');
      const isNextDay = state.endH >= 24;

      document.getElementById('start_time_input').value =
        `${TODAY} ${pad2(state.startH)}:${pad2(state.startM)}:00`;

      const endDateStr = isNextDay ? TOMORROW : TODAY;
      const endHourReal = state.endH % 24;
      document.getElementById('end_time_input').value =
        `${endDateStr} ${pad2(endHourReal)}:${pad2(state.endM)}:00`;

      // Add overtime_days to form data
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'overtime_days';
      input.value = calculateOvertimeDays();
      this.appendChild(input);

      // Submit form via AJAX
      submitOvertimeForm();
    });

    function submitOvertimeForm() {
      const form = document.getElementById('overtimeForm');
      const submitBtn = document.getElementById('submitBtn');
      const formData = new FormData(form);

      // Disable button and show loading state
      submitBtn.disabled = true;
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Sedang memproses...';

      fetch(form.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
          },
          body: formData
        })
        .then(response => {
          if (response.status === 422) {
            return response.json().then(data => {
              throw {
                errors: data.errors
              };
            });
          }
          return response.json().then(data => {
            if (!response.ok) {
              throw data;
            }
            return data;
          });
        })
        .then(data => {
          if (data.success || data.message) {
            if (window.Swal) {
              window.Swal.fire({
                title: 'Berhasil!',
                text: data.message || 'Permintaan lembur berhasil diajukan.',
                icon: 'success',
                confirmButtonText: 'OK',
              }).then(() => {
                window.location.href = data.redirect || '{{ route('dashboard') }}';
              });
            } else {
              alert(data.message || 'Permintaan lembur berhasil diajukan.');
              window.location.href = data.redirect || '{{ route('dashboard') }}';
            }
          }
        })
        .catch(error => {
          if (error.errors) {
            showErrors(error.errors);
          } else {
            if (window.Swal) {
              window.Swal.fire({
                title: 'Error',
                text: 'Terjadi kesalahan: ' + (error.message || 'Unknown error'),
                icon: 'error',
                confirmButtonText: 'OK',
              });
            } else {
              alert('Terjadi kesalahan: ' + (error.message || 'Unknown error'));
            }
          }
        })
        .finally(() => {
          // Re-enable button and restore text
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        });
    }

    function showErrors(errors) {
      const errorMessages = Object.values(errors).flat().join('\n');
      if (window.Swal) {
        window.Swal.fire({
          title: 'Validasi Gagal',
          text: errorMessages,
          icon: 'error',
          confirmButtonText: 'OK',
        });
      } else {
        alert('Validasi Gagal:\n' + errorMessages);
      }
    }

    render();

    // Image upload handling
    const imageInput = document.getElementById('imageInput');
    const imageLabel = document.getElementById('imageLabel');
    const imageName = document.getElementById('imageName');
    const imagePreview = document.getElementById('imagePreview');
    const uploadZone = imageInput.parentElement.querySelector('label');

    // File input change
    imageInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        displayImagePreview(file);
      }
    });

    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadZone.classList.add('border-blue-400', 'bg-blue-50');
    });

    uploadZone.addEventListener('dragleave', () => {
      uploadZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    uploadZone.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadZone.classList.remove('border-blue-400', 'bg-blue-50');
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        imageInput.files = files;
        displayImagePreview(files[0]);
      }
    });

    function displayImagePreview(file) {
      if (!file.type.startsWith('image/')) {
        if (window.Swal) {
          window.Swal.fire('Error', 'File harus berupa gambar', 'error');
        } else {
          alert('File harus berupa gambar');
        }
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.src = e.target.result;
        imagePreview.classList.remove('hidden');
        imageLabel.innerHTML = '<span class="text-green-600 font-medium">✓ Gambar dipilih</span>';
        imageName.textContent = file.name;
      };
      reader.readAsDataURL(file);
    }

    document.getElementById('imageInput').addEventListener('change', function(event) {
      const files = event.target.files;
      const container = document.getElementById('imagePreviewContainer');
      const countLabel = document.getElementById('imageCount');

      // Reset/bersihkan preview sebelumnya
      container.innerHTML = '';

      if (files.length === 0) {
        container.classList.add('hidden');
        countLabel.textContent = '';
        return;
      }

      // Validasi: Batasi maksimal 5 gambar
      if (files.length > 5) {
        alert('Maksimal hanya boleh mengunggah 5 gambar!');
        this.value = ''; // Reset input file jika melebihi batas
        container.classList.add('hidden');
        countLabel.textContent = '';
        return;
      }

      // Tampilkan container preview dan teks jumlah file
      container.classList.remove('hidden');
      countLabel.textContent = `${files.length} gambar dipilih`;

      // Looping setiap file yang dipilih untuk dijadikan preview
      Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();

          reader.onload = function(e) {
            // Membuat elemen pembungkus gambar (kotak persegi/aspect-square)
            const wrapper = document.createElement('div');
            wrapper.className =
              'relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100';

            // Membuat tag img
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-full object-cover';

            wrapper.appendChild(img);
            container.appendChild(wrapper);
          }

          reader.readAsDataURL(file);
        }
      });

      // --- Google Maps: Overtime location picker ---
      const MAPS_API_KEY = "{{ config('services.google.maps_api_key') ?? env('GOOGLE_MAPS_API_KEY', '') }}";
      const latInput = document.getElementById('latitude_input');
      const lngInput = document.getElementById('longitude_input');
      const coordsDisplay = document.getElementById('coordsDisplay');
      let overtimeMap = null;
      let overtimeMarker = null;

      async function initOvertimeMap() {
        if (typeof google === 'undefined' || !google.maps || !google.maps.importLibrary) {
          console.warn('Google Maps API belum siap.');
          return;
        }

        const { Map } = await google.maps.importLibrary('maps');
        const { Marker } = await google.maps.importLibrary('marker');

        const mapEl = document.getElementById('overtime-map');
        if (!mapEl) return;

        const initialLat = parseFloat(latInput.value) || -6.200000;
        const initialLng = parseFloat(lngInput.value) || 106.816666;

        overtimeMap = new Map(mapEl, {
          zoom: 14,
          center: { lat: initialLat, lng: initialLng },
          mapTypeControl: true,
          fullscreenControl: true,
          streetViewControl: false,
        });

        if (latInput.value && lngInput.value) {
          overtimeMarker = new Marker({
            position: { lat: parseFloat(latInput.value), lng: parseFloat(lngInput.value) },
            map: overtimeMap,
          });
          coordsDisplay.textContent = `${Number(latInput.value).toFixed(6)}, ${Number(lngInput.value).toFixed(6)}`;
        }

        // Click on map to place marker
        mapEl.addEventListener('click', (ev) => {
          const lat = ev.latLng.lat();
          const lng = ev.latLng.lng();
          if (!overtimeMarker) {
            overtimeMarker = new Marker({ position: { lat, lng }, map: overtimeMap });
          } else {
            overtimeMarker.setPosition({ lat, lng });
          }
          latInput.value = lat;
          lngInput.value = lng;
          coordsDisplay.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });

        // Use device geolocation
        document.getElementById('useLocationBtn').addEventListener('click', () => {
          if (!navigator.geolocation) {
            alert('Geolocation tidak didukung browser Anda.');
            return;
          }
          navigator.geolocation.getCurrentPosition((pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            overtimeMap.setCenter({ lat, lng });
            overtimeMap.setZoom(16);
            if (!overtimeMarker) {
              overtimeMarker = new Marker({ position: { lat, lng }, map: overtimeMap });
            } else {
              overtimeMarker.setPosition({ lat, lng });
            }
            latInput.value = lat;
            lngInput.value = lng;
            coordsDisplay.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
          }, (err) => {
            alert('Gagal mendapatkan lokasi: ' + err.message);
          }, { enableHighAccuracy: true, timeout: 10000 });
        });

        document.getElementById('clearLocationBtn').addEventListener('click', () => {
          if (overtimeMarker) {
            overtimeMarker.setMap(null);
            overtimeMarker = null;
          }
          latInput.value = '';
          lngInput.value = '';
          coordsDisplay.textContent = '—';
        });
      }

      function loadGoogleMapsAndInitOvertime() {
        if (!MAPS_API_KEY) {
          console.warn('GOOGLE_MAPS_API_KEY not configured');
          return;
        }

        if (window.google && window.google.maps && window.google.maps.importLibrary) {
          initOvertimeMap();
          return;
        }

        if (document.querySelector('script[data-gmaps-bootstrap]')) {
          window.addEventListener('google-maps-ready', initOvertimeMap, { once: true });
          return;
        }

        window.__googleMapsBootstrapOvertime = function() {
          window.dispatchEvent(new Event('google-maps-ready'));
          initOvertimeMap();
        };

        const script = document.createElement('script');
        script.setAttribute('data-gmaps-bootstrap', '1');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${MAPS_API_KEY}&callback=__googleMapsBootstrapOvertime&libraries=geometry&loading=async`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadGoogleMapsAndInitOvertime);
      } else {
        loadGoogleMapsAndInitOvertime();
      }
    });
  </script>

</x-app-layout>
