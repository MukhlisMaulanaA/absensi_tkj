<x-app-layout>

  {{-- Mobile-first header --}}
  <div class="sticky top-0 z-10 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('dashboard') }}"
      class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition flex-shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
    </a>
    <div>
      <h1 class="text-base font-semibold text-gray-900 leading-tight">History</h1>
      <p class="text-xs text-gray-400">Attendance & Requests</p>
    </div>
  </div>

  <div class="min-h-screen bg-gray-50" x-data="{ activeTab: 'attendance' }">

    {{-- Tab Navigation --}}
    <div class="bg-white border-b border-gray-100 px-4">
      <div class="flex gap-0 -mb-px overflow-x-auto scrollbar-hide">
        <button @click="activeTab = 'attendance'"
          :class="activeTab === 'attendance'
              ?
              'border-b-2 border-blue-500 text-blue-600 font-semibold' :
              'text-gray-400 font-normal'"
          class="flex items-center gap-1.5 px-4 py-3 text-sm whitespace-nowrap transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Absensi
          <span :class="activeTab === 'attendance' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
            class="text-xs px-1.5 py-0.5 rounded-full font-semibold transition-colors">{{ $attendances->total() }}</span>
        </button>

        <button @click="activeTab = 'overtime'"
          :class="activeTab === 'overtime'
              ?
              'border-b-2 border-amber-500 text-amber-600 font-semibold' :
              'text-gray-400 font-normal'"
          class="flex items-center gap-1.5 px-4 py-3 text-sm whitespace-nowrap transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Lembur
          <span :class="activeTab === 'overtime' ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-400'"
            class="text-xs px-1.5 py-0.5 rounded-full font-semibold transition-colors">{{ $overtimeRequests->total() }}</span>
        </button>

        <button @click="activeTab = 'leave'"
          :class="activeTab === 'leave'
              ?
              'border-b-2 border-violet-500 text-violet-600 font-semibold' :
              'text-gray-400 font-normal'"
          class="flex items-center gap-1.5 px-4 py-3 text-sm whitespace-nowrap transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Izin
          <span :class="activeTab === 'leave' ? 'bg-violet-100 text-violet-600' : 'bg-gray-100 text-gray-400'"
            class="text-xs px-1.5 py-0.5 rounded-full font-semibold transition-colors">{{ $leaveRequests->total() }}</span>
        </button>
      </div>
    </div>

    {{-- ======= ATTENDANCE TAB ======= --}}
    <div x-show="activeTab === 'attendance'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
      class="p-4 space-y-3">

      {{-- Stats Row --}}
      <div class="grid grid-cols-3 gap-2">
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Total</p>
          <p class="text-2xl font-bold text-blue-500">{{ $attendances->total() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">On Site</p>
          <p class="text-2xl font-bold text-green-500">
            {{ $attendances->getCollection()->where('is_within_radius', true)->count() }}
          </p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Off Site</p>
          <p class="text-2xl font-bold text-red-400">
            {{ $attendances->getCollection()->where('is_within_radius', false)->count() }}
          </p>
        </div>
      </div>

      {{-- Attendance Cards --}}
      @forelse($attendances as $attendance)
        @php
          $attendanceDate = $attendance->check_in_time->toDateString();
          $attendanceOvertimes = \App\Models\OvertimeRequest::where('user_id', $attendance->user_id)
              ->whereDate('start_time', '<=', $attendanceDate)
              ->whereDate('end_time', '>=', $attendanceDate)
              ->get();
          $totalDays = 0;
          $totalHours = 0;
          foreach ($attendanceOvertimes as $request) {
              if ($request->overtime_days == 0) {
                  $totalHours += \Carbon\Carbon::parse($request->start_time)->diffInHours(
                      \Carbon\Carbon::parse($request->end_time),
                  );
              } else {
                  $totalDays += $request->overtime_days;
              }
          }
          $result = [];
          if ($totalDays > 0) {
              $result[] = $totalDays . ' hari';
          }
          if ($totalHours > 0) {
              $result[] = $totalHours . ' jam';
          }
          $overtimeText = !empty($result) ? implode(' ', $result) : null;
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          {{-- Card Header --}}
          <div class="px-4 pt-3.5 pb-2 flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-800">{{ $attendance->check_in_time->format('l') }}</p>
              <p class="text-xs text-gray-400">{{ $attendance->check_in_time->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
              @if ($overtimeText)
                <span
                  class="text-xs bg-amber-50 text-amber-600 font-semibold px-2 py-1 rounded-full border border-amber-100">
                  +{{ $overtimeText }}
                </span>
              @endif
              @if ($attendance->is_within_radius)
                <span
                  class="flex items-center gap-1 text-xs bg-green-50 text-green-600 font-semibold px-2.5 py-1 rounded-full border border-green-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                  On Site
                </span>
              @else
                <span
                  class="flex items-center gap-1 text-xs bg-red-50 text-red-500 font-semibold px-2.5 py-1 rounded-full border border-red-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                  Off Site
                </span>
              @endif
            </div>
          </div>

          {{-- Time Row --}}
          <div class="px-4 pb-3 flex items-center gap-3">
            <div class="flex-1 flex items-center gap-2 bg-blue-50 rounded-xl px-3 py-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
              <div>
                <p class="text-xs text-blue-400 leading-none">Check In</p>
                <p class="text-sm font-bold text-blue-600 leading-tight mt-0.5">
                  {{ $attendance->check_in_time->format('H:i') }}</p>
              </div>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-200 flex-shrink-0" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>

            <div
              class="flex-1 flex items-center gap-2 {{ $attendance->check_out_time ? 'bg-rose-50' : 'bg-gray-50' }} rounded-xl px-3 py-2">
              <svg xmlns="http://www.w3.org/2000/svg"
                class="w-3.5 h-3.5 {{ $attendance->check_out_time ? 'text-rose-400' : 'text-gray-300' }} flex-shrink-0"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
              </svg>
              <div>
                <p class="text-xs {{ $attendance->check_out_time ? 'text-rose-400' : 'text-gray-300' }} leading-none">
                  Check Out</p>
                @if ($attendance->check_out_time)
                  <p class="text-sm font-bold text-rose-500 leading-tight mt-0.5">
                    {{ $attendance->check_out_time->format('H:i') }}</p>
                @else
                  <p class="text-sm font-semibold text-gray-300 leading-tight mt-0.5">--:--</p>
                @endif
              </div>
            </div>
          </div>

          {{-- Footer Row --}}
          @if ($attendance->working_hours || $attendance->location || $attendance->check_in_photo || $attendance->check_out_photo)
            <div class="px-4 py-2.5 border-t border-gray-50 flex items-center justify-between">
              <div class="flex items-center gap-3">
                @if ($attendance->working_hours)
                  <span class="flex items-center gap-1 text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $attendance->working_hours }} jam
                  </span>
                @endif
                @if ($attendance->location)
                  <span class="flex items-center gap-1 text-xs text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $attendance->location->name }}
                  </span>
                @endif
              </div>
              <div class="flex items-center gap-2">
                @if ($attendance->check_in_photo)
                  <a href="{{ Storage::url($attendance->check_in_photo) }}" target="_blank"
                    class="flex items-center gap-1 text-xs bg-blue-50 text-blue-500 px-2.5 py-1 rounded-full border border-blue-100 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    In
                  </a>
                @endif
                @if ($attendance->check_out_photo)
                  <a href="{{ Storage::url($attendance->check_out_photo) }}" target="_blank"
                    class="flex items-center gap-1 text-xs bg-rose-50 text-rose-500 px-2.5 py-1 rounded-full border border-rose-100 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Out
                  </a>
                @endif
              </div>
            </div>
          @endif
        </div>

      @empty
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-12 text-center">
          <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <p class="text-sm text-gray-400 mb-3">Belum ada data absensi</p>
          <a href="{{ route('dashboard') }}" class="text-sm text-blue-500 font-semibold">Ke Dashboard</a>
        </div>
      @endforelse

      {{-- Pagination --}}
      @if ($attendances->hasPages())
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-3">
          {{ $attendances->links('pagination::tailwind') }}
        </div>
      @endif

    </div>

    {{-- ======= OVERTIME TAB ======= --}}
    <div x-show="activeTab === 'overtime'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
      class="p-4 space-y-3">

      {{-- Stats Row --}}
      <div class="grid grid-cols-2 gap-2">
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Total</p>
          <p class="text-2xl font-bold text-amber-500">{{ $overtimeRequests->total() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Pending</p>
          <p class="text-2xl font-bold text-yellow-500">{{ $overtimeRequests->where('status', 'pending')->count() }}
          </p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Disetujui</p>
          <p class="text-2xl font-bold text-green-500">{{ $overtimeRequests->where('status', 'approved')->count() }}
          </p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Ditolak</p>
          <p class="text-2xl font-bold text-red-400">{{ $overtimeRequests->where('status', 'rejected')->count() }}</p>
        </div>
      </div>

      {{-- New Overtime Button --}}
      <a href="{{ route('overtime.create') }}"
        class="flex items-center justify-center gap-2 w-full bg-amber-500 text-white font-semibold rounded-2xl py-3 text-sm hover:bg-amber-600 active:scale-95 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Ajukan Lembur
      </a>

      {{-- Overtime Cards --}}
      @forelse($overtimeRequests as $overtime)
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 pt-3.5 pb-3">
            <div class="flex items-start justify-between mb-3">
              <div>
                <p class="text-sm font-semibold text-gray-800">{{ $overtime->created_at->format('d M Y') }}</p>
                @if ($overtime->description)
                  <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 max-w-[180px]">{{ $overtime->description }}</p>
                @endif
              </div>
              @if ($overtime->status === 'pending')
                <span
                  class="flex items-center gap-1 text-xs bg-yellow-50 text-yellow-600 font-semibold px-2.5 py-1 rounded-full border border-yellow-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>Pending
                </span>
              @elseif ($overtime->status === 'approved')
                <span
                  class="flex items-center gap-1 text-xs bg-green-50 text-green-600 font-semibold px-2.5 py-1 rounded-full border border-green-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Disetujui
                </span>
              @else
                <span
                  class="flex items-center gap-1 text-xs bg-red-50 text-red-500 font-semibold px-2.5 py-1 rounded-full border border-red-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Ditolak
                </span>
              @endif
            </div>

            <div class="flex items-center gap-2">
              <div class="flex-1 bg-amber-50 rounded-xl px-3 py-2">
                <p class="text-xs text-amber-400 leading-none">Mulai</p>
                <p class="text-sm font-bold text-amber-600 leading-tight mt-0.5">
                  {{ $overtime->start_time->format('H:i') }}</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-200 flex-shrink-0" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
              <div class="flex-1 bg-orange-50 rounded-xl px-3 py-2">
                <p class="text-xs text-orange-400 leading-none">Selesai</p>
                <p class="text-sm font-bold text-orange-500 leading-tight mt-0.5">
                  {{ $overtime->end_time->format('H:i') }}</p>
              </div>
              <div class="flex-shrink-0 bg-gray-50 rounded-xl px-3 py-2 text-center">
                <p class="text-xs text-gray-400 leading-none">Durasi</p>
                <p class="text-sm font-bold text-gray-700 leading-tight mt-0.5">{{ $overtime->duration_hours }}j</p>
              </div>
            </div>
          </div>

          @if ($overtime->approver || ($overtime->user_id === auth()->id() && $overtime->status === 'pending'))
            <div class="px-4 py-2.5 border-t border-gray-50 flex items-center justify-between">
              @if ($overtime->approver)
                <span class="text-xs text-gray-400">Disetujui oleh <span
                    class="font-medium text-gray-600">{{ $overtime->approver->name }}</span></span>
              @else
                <span></span>
              @endif
              @if ($overtime->user_id === auth()->id() && $overtime->status === 'pending')
                <form action="{{ route('overtime.destroy', $overtime->id) }}" method="POST"
                  onsubmit="return confirm('Hapus request lembur ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                    class="flex items-center gap-1 text-xs bg-red-50 text-red-500 px-2.5 py-1.5 rounded-full border border-red-100 font-medium hover:bg-red-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                  </button>
                </form>
              @endif
            </div>
          @endif
        </div>

      @empty
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-12 text-center">
          <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-300" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-sm text-gray-400 mb-3">Belum ada request lembur</p>
          <a href="{{ route('overtime.create') }}" class="text-sm text-amber-500 font-semibold">Ajukan Sekarang</a>
        </div>
      @endforelse

      @if ($overtimeRequests->hasPages())
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-3">
          {{ $overtimeRequests->links('pagination::tailwind') }}
        </div>
      @endif

    </div>

    {{-- ======= LEAVE TAB ======= --}}
    <div x-show="activeTab === 'leave'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
      class="p-4 space-y-3">

      {{-- Stats Row --}}
      <div class="grid grid-cols-2 gap-2">
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Total</p>
          <p class="text-2xl font-bold text-violet-500">{{ $leaveRequests->total() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Pending</p>
          <p class="text-2xl font-bold text-yellow-500">{{ $leaveRequests->where('status', 'pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Disetujui</p>
          <p class="text-2xl font-bold text-green-500">{{ $leaveRequests->where('status', 'approved')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 text-center border border-gray-100">
          <p class="text-xs text-gray-400 mb-1">Ditolak</p>
          <p class="text-2xl font-bold text-red-400">{{ $leaveRequests->where('status', 'rejected')->count() }}</p>
        </div>
      </div>

      {{-- New Leave Button --}}
      <a href="{{ route('leave.create') }}"
        class="flex items-center justify-center gap-2 w-full bg-violet-500 text-white font-semibold rounded-2xl py-3 text-sm hover:bg-violet-600 active:scale-95 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Ajukan Izin / Cuti
      </a>

      {{-- Leave Cards --}}
      @forelse($leaveRequests as $leave)
        @php
          $types = ['sick' => 'Sakit', 'permission' => 'Izin', 'leave' => 'Cuti'];
          $typeColors = [
              'sick' => 'bg-red-50 text-red-500 border-red-100',
              'permission' => 'bg-blue-50 text-blue-500 border-blue-100',
              'leave' => 'bg-violet-50 text-violet-500 border-violet-100',
          ];
          $typeColor = $typeColors[$leave->type] ?? 'bg-gray-50 text-gray-500 border-gray-100';
          $duration = $leave->start_date->diffInDays($leave->end_date) + 1;
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 pt-3.5 pb-3">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ $typeColor }}">
                  {{ $types[$leave->type] ?? $leave->type }}
                </span>
                <span class="text-xs text-gray-400 font-medium">{{ $duration }} hari</span>
              </div>
              @if ($leave->status === 'pending')
                <span
                  class="flex items-center gap-1 text-xs bg-yellow-50 text-yellow-600 font-semibold px-2.5 py-1 rounded-full border border-yellow-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>Pending
                </span>
              @elseif ($leave->status === 'approved')
                <span
                  class="flex items-center gap-1 text-xs bg-green-50 text-green-600 font-semibold px-2.5 py-1 rounded-full border border-green-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Disetujui
                </span>
              @else
                <span
                  class="flex items-center gap-1 text-xs bg-red-50 text-red-500 font-semibold px-2.5 py-1 rounded-full border border-red-100">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Ditolak
                </span>
              @endif
            </div>

            <div class="flex items-center gap-2 mb-3">
              <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-xs text-gray-400 leading-none">Mulai</p>
                <p class="text-sm font-bold text-gray-700 leading-tight mt-0.5">
                  {{ $leave->start_date->format('d M Y') }}</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-200 flex-shrink-0" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
              <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-xs text-gray-400 leading-none">Selesai</p>
                <p class="text-sm font-bold text-gray-700 leading-tight mt-0.5">
                  {{ $leave->end_date->format('d M Y') }}</p>
              </div>
            </div>

            @if ($leave->reason)
              <p class="text-xs text-gray-400 line-clamp-2 leading-relaxed">{{ $leave->reason }}</p>
            @endif
          </div>

          @if ($leave->attachment || $leave->approver || $leave->status === 'pending')
            <div class="px-4 py-2.5 border-t border-gray-50 flex items-center justify-between">
              <div class="flex items-center gap-2">
                @if ($leave->attachment)
                  <a href="{{ Storage::url($leave->attachment) }}" target="_blank"
                    class="flex items-center gap-1 text-xs bg-indigo-50 text-indigo-500 px-2.5 py-1 rounded-full border border-indigo-100 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Lampiran
                  </a>
                @endif
                @if ($leave->approver)
                  <span class="text-xs text-gray-400">oleh <span
                      class="font-medium text-gray-600">{{ $leave->approver->name }}</span></span>
                @endif
              </div>
              @if ($leave->status === 'pending')
                <form method="POST" action="{{ route('leave.destroy', $leave) }}"
                  onsubmit="return confirm('Hapus request izin ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                    class="flex items-center gap-1 text-xs bg-red-50 text-red-500 px-2.5 py-1.5 rounded-full border border-red-100 font-medium hover:bg-red-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                  </button>
                </form>
              @endif
            </div>
          @endif
        </div>

      @empty
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-12 text-center">
          <div class="w-12 h-12 bg-violet-50 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-violet-300" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-sm text-gray-400 mb-3">Belum ada request izin</p>
          <a href="{{ route('leave.create') }}" class="text-sm text-violet-500 font-semibold">Ajukan Sekarang</a>
        </div>
      @endforelse

      @if ($leaveRequests->hasPages())
        <div class="bg-white rounded-2xl border border-gray-100 px-4 py-3">
          {{ $leaveRequests->links('pagination::tailwind') }}
        </div>
      @endif

    </div>

    {{-- Bottom safe area spacer --}}
    <div class="h-6"></div>

  </div>

  <style>
    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .line-clamp-1 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
    }

    .line-clamp-2 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
  </style>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</x-app-layout>
