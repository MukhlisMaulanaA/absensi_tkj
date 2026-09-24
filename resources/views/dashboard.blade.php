<x-app-layout>
  <div class="min-h-screen bg-slate-50 antialiased" x-data="attendanceApp()" x-init="init()">

    <!-- Mobile Header -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-40">
      <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 bg-slate-900 rounded-lg flex items-center justify-center">
            <span class="text-white text-[10px] font-bold tracking-tight">TKJ</span>
          </div>
          <span class="text-sm font-semibold text-slate-900">Attendance</span>
        </div>

        <div>
          @if ($todayStatus['status'] === 'not_checked_in')
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
              {{ __('Not Checked In') }}
            </span>
          @elseif($todayStatus['status'] === 'checked_in')
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
              <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
              {{ __('Checked In') }}
            </span>
          @else
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              {{ __('Checked Out') }}
            </span>
          @endif
        </div>
      </div>
    </header>

    <main class="max-w-md mx-auto px-4 py-5 space-y-3.5 pb-10">

      <!-- Identity Card -->
      <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div
              class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-white text-sm font-semibold shrink-0">
              {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <div class="min-w-0">
              <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">
                {{ __('Welcome Back') }}
              </p>

              <p class="text-base font-semibold text-slate-900 truncate">
                {{ explode(' ', $user->name)[0] }}
              </p>

              <p class="text-xs text-slate-400 truncate">
                {{ $user->jabatan }} &middot;
                {{ $user->location?->name ?? 'No Location Assigned' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Clock & Attendance -->
      <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">

        <div class="text-center mb-7">
          <p class="text-5xl font-thin text-slate-900 tracking-tighter tabular-nums leading-none" x-text="currentTime">
            00:00:00
          </p>

          <p class="text-xs text-slate-400 mt-2.5 font-medium" x-text="currentDate">
            Loading...
          </p>
        </div>

        <!-- Attendance Button -->
        <div class="flex justify-center mb-7">

          @if ($todayStatus['status'] === 'not_checked_in')
            <button @click="openModal('check-in')"
              class="group flex flex-col items-center justify-center w-36 h-36 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-200 transition-all duration-200 hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-emerald-200">

              <svg xmlns="http://www.w3.org/2000/svg"
                class="w-7 h-7 mb-1.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
              </svg>

              <span class="text-sm font-semibold tracking-wide">
                {{ __('Check In') }}
              </span>
            </button>
          @elseif($todayStatus['status'] === 'checked_in')
            <button @click="openModal('check-out')"
              class="group flex flex-col items-center justify-center w-36 h-36 rounded-full bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-200 transition-all duration-200 hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-rose-200">

              <svg xmlns="http://www.w3.org/2000/svg"
                class="w-7 h-7 mb-1.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h14" />
              </svg>

              <span class="text-sm font-semibold tracking-wide">
                {{ __('Check Out') }}
              </span>
            </button>
          @else
            <div
              class="flex flex-col items-center justify-center w-36 h-36 rounded-full bg-slate-100 text-slate-400 cursor-not-allowed">

              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mb-1.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
              </svg>

              <span class="text-sm font-semibold tracking-wide">
                {{ __('Completed') }}
              </span>
            </div>
          @endif
        </div>

        <!-- Time Info -->
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
            <div class="flex items-center gap-1.5 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
              </svg>

              <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
                {{ __('In') }}
              </span>
            </div>

            <p class="text-xl font-semibold text-slate-900 tabular-nums">
              @if ($todayStatus['data'])
                {{ $todayStatus['data']->check_in_time->format('H:i') }}
              @else
                <span class="text-slate-200 font-light">--:--</span>
              @endif
            </p>
          </div>

          <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
            <div class="flex items-center gap-1.5 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h14" />
              </svg>

              <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
                {{ __('Out') }}
              </span>
            </div>

            <p class="text-xl font-semibold text-slate-900 tabular-nums">
              @if ($todayStatus['data'] && $todayStatus['data']->check_out_time)
                {{ $todayStatus['data']->check_out_time->format('H:i') }}
              @else
                <span class="text-slate-200 font-light">--:--</span>
              @endif
            </p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-4 py-3 border-b border-slate-50">
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
            {{ __('Quick Actions') }}
          </p>
        </div>

        <a href="{{ route('overtime.create') }}"
          class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-slate-50 transition-colors border-b border-slate-50 group">

          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2h8a2 2 0 002-2v-1a2 2 0 012-2h1.945M7.884 5.884l.707-.707a2 2 0 012.828 0l.707.707m0 0l.707-.707a2 2 0 012.828 0l.707.707M12 5v3" />
              </svg>
            </div>

            <span class="text-sm font-medium text-slate-700">
              {{ __('Request Overtime') }}
            </span>
          </div>

          <svg xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>

        <a href="{{ route('leave.create') }}"
          class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-slate-50 transition-colors border-b border-slate-50 group">

          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7v12m8-12v12m-8-12a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2v-6a2 2 0 00-2-2M9 7h6m0-4h-4m0 0H7m2 0v4" />
              </svg>
            </div>

            <span class="text-sm font-medium text-slate-700">
              {{ __('Request Izin') }}
            </span>
          </div>

          <svg xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>

        <a href="{{ route('attendance.history') }}"
          class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-slate-50 transition-colors group">

          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>

            <span class="text-sm font-medium text-slate-700">
              {{ __('View All History') }}
            </span>
          </div>

          <svg xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>

      <!-- Recent Attendance -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
            {{ __('Recent Attendance') }}
          </p>

          <a href="{{ route('attendance.history') }}"
            class="flex items-center gap-0.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition-colors">
            {{ __('All') }}

            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>

        @if ($recentAttendances->count() > 0)
          <div class="divide-y divide-slate-50">
            @foreach ($recentAttendances as $attendance)
              <div class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">

                <div>
                  <p class="text-sm font-medium text-slate-800">
                    {{ $attendance->check_in_time->format('l, d M Y') }}
                  </p>

                  <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span
                      class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">

                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                      </svg>

                      {{ $attendance->check_in_time->format('H:i') }}
                    </span>

                    @if ($attendance->check_out_time)
                      <span
                        class="inline-flex items-center gap-1 text-[11px] font-medium text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>

                        {{ $attendance->check_out_time->format('H:i') }}
                      </span>
                    @endif
                  </div>
                </div>

                <div class="shrink-0 ml-3">
                  @if ($attendance->is_within_radius)
                    <span
                      class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg">

                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>

                      {{ __('On Site') }}
                    </span>
                  @else
                    <span
                      class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-lg">

                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                      </svg>

                      {{ __('Off Site') }}
                    </span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="px-4 py-10 text-center">
            <p class="text-sm text-slate-400">
              {{ __('No attendance records yet') }}
            </p>
          </div>
        @endif
      </div>
    </main>

    <!-- MODAL -->
    <div x-show="modalOpen" x-transition.opacity
      class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-end justify-center"
      @click.self="closeModal()">

      <div class="bg-white w-full max-w-md rounded-t-2xl shadow-2xl max-h-[92vh] overflow-y-auto">

        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-slate-100 px-5 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-slate-900">
              <span x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span>
            </h2>

            <p class="text-xs text-slate-400 mt-0.5" x-text="currentDate"></p>
          </div>

          <button @click="closeModal()" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">

            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="px-5 py-4 space-y-4">

          <!-- Clock -->
          <div class="text-center py-2">
            <p class="text-4xl font-light text-slate-900 tracking-tight tabular-nums" x-text="currentTime"></p>
          </div>

          <!-- Location -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">

            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-600">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>

                <span>Location</span>
              </div>

              <button @click="refreshLocation()" :disabled="locationLoading"
                class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 disabled:opacity-40 transition-colors font-medium">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" :class="locationLoading ? 'animate-spin' : ''"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>

                Refresh
              </button>
            </div>

            <template x-if="latitude && longitude">
              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-slate-400 uppercase tracking-wide text-[10px] font-semibold">
                    Latitude
                  </span>

                  <p class="font-mono text-slate-700 mt-1" x-text="latitude ? latitude.toFixed(6) : '-'">
                  </p>
                </div>

                <div>
                  <span class="text-slate-400 uppercase tracking-wide text-[10px] font-semibold">
                    Longitude
                  </span>

                  <p class="font-mono text-slate-700 mt-1" x-text="longitude ? longitude.toFixed(6) : '-'">
                  </p>
                </div>
              </div>
            </template>

            <template x-if="!latitude || !longitude">
              <p class="text-xs text-slate-400">
                <span x-show="locationLoading">Detecting location...</span>
                <span x-show="!locationLoading">Location unavailable</span>
              </p>
            </template>
          </div>

          <!-- Map -->
          <div>
            <div :id="'map-' + modalType" class="w-full h-52 rounded-xl border border-slate-200"></div>
          </div>

          <!-- Camera -->
          <div>
            <p class="text-sm font-medium text-slate-700 mb-2.5">Selfie Photo</p>

            <template x-if="!photoPreview">
              <div class="space-y-2.5">
                <div class="relative bg-slate-900 rounded-xl overflow-hidden aspect-video">
                  <video x-ref="modalVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                </div>

                <button @click="capturePhoto()"
                  class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium transition-colors">

                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 9a2 2 0 012-2h1l1-2h10l1 2h1a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 13a3 3 0 100-6 3 3 0 000 6z" />
                  </svg>

                  Capture Photo
                </button>
              </div>
            </template>

            <template x-if="photoPreview">
              <div class="space-y-2.5">
                <div class="rounded-xl overflow-hidden aspect-video">
                  <img :src="photoPreview" class="w-full h-full object-cover" />
                </div>

                <button @click="retakePhoto()"
                  class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium transition-colors">

                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>

                  Retake
                </button>
              </div>
            </template>

            <canvas x-ref="modalCanvas" class="hidden"></canvas>
          </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-white border-t border-slate-100 px-5 py-4 space-y-2">

          <button @click="submit()" :disabled="!photo || !latitude || !longitude || submitting"
            :class="modalType === 'check-in'
                ?
                'bg-emerald-500 hover:bg-emerald-600 shadow-sm shadow-emerald-100' :
                'bg-rose-500 hover:bg-rose-600 shadow-sm shadow-rose-100'"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-white text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">

            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>

            <span x-show="!submitting">
              Confirm
              <span x-text="modalType === 'check-in' ? 'Check In' : 'Check Out'"></span>
            </span>

            <span x-show="submitting">Processing...</span>
          </button>

          <button @click="closeModal()"
            class="w-full py-2.5 rounded-xl text-sm font-medium text-slate-500 hover:bg-slate-50 transition-colors">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '{{ session('success') }}',
          confirmButtonText: 'OK',
          confirmButtonColor: '#4F46E5'
        });
      });
    </script>
  @endif

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
          this.stopCamera();
          this.$nextTick(() => {
            this.openCamera();
          });
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
