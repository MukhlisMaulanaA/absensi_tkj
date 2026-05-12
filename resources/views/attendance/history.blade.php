<x-app-layout>
  <x-slot name="header">
    <div class="flex justify-between items-center">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Attendance & Overtime History') }}
      </h2>
      <a href="{{ route('dashboard') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
        ← {{ __('Back to Dashboard') }}
      </a>
    </div>
  </x-slot>

  <div class="py-8 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'attendance' }">

      <!-- Navigation Tabs -->
      <div class="mb-8 flex gap-4 border-b border-gray-200">
        <button @click="activeTab = 'attendance'"
          :class="activeTab === 'attendance' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-800'"
          class="pb-3 px-4 transition">
          <span class="inline-flex items-center gap-2">
            <span class="text-lg">📋</span>
            {{ __('Attendance History') }} ({{ $attendances->total() }})
          </span>
        </button>
        <button @click="activeTab = 'overtime'"
          :class="activeTab === 'overtime' ? 'border-b-2 border-amber-600 text-amber-600 font-semibold' : 'text-gray-600 hover:text-gray-800'"
          class="pb-3 px-4 transition">
          <span class="inline-flex items-center gap-2">
            <span class="text-lg">⏰</span>
            {{ __('Overtime Requests') }} ({{ $overtimeRequests->total() }})
          </span>
        </button>
      </div>

      <!-- ATTENDANCE SECTION -->
      <div x-show="activeTab === 'attendance'" x-transition class="space-y-6">

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Total Records') }}</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $attendances->total() }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('On Site') }}</p>
            <p class="text-3xl font-bold text-green-600 mt-2">
              {{ $attendances->getCollection()->where('is_within_radius', true)->count() }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Off Site') }}</p>
            <p class="text-3xl font-bold text-red-600 mt-2">
              {{ $attendances->getCollection()->where('is_within_radius', false)->count() }}
            </p>
          </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Date') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Check In') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Check Out') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Duration') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Status') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Location') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Photos') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                @forelse($attendances as $attendance)
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <strong>{{ $attendance->check_in_time->format('l, d M Y') }}</strong>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                        {{ $attendance->check_in_time->format('H:i:s') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      @if ($attendance->check_out_time)
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                          {{ $attendance->check_out_time->format('H:i:s') }}
                        </span>
                      @else
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                          {{ __('Not checked out') }}
                        </span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      @if ($attendance->working_hours)
                        <span class="font-semibold text-indigo-600">{{ $attendance->working_hours }} hrs</span>
                      @else
                        <span class="text-gray-500">--</span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                      @if ($attendance->is_within_radius)
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                          <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                          {{ __('On Site') }}
                        </span>
                      @else
                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                          <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                          {{ __('Off Site') }}
                        </span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      @if ($attendance->location)
                        <span class="text-gray-700">{{ $attendance->location->name }}</span>
                      @else
                        <span class="text-gray-500">--</span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                      <div class="flex gap-2">
                        @if ($attendance->check_in_photo)
                          <a href="{{ Storage::url($attendance->check_in_photo) }}" target="_blank"
                            class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs hover:bg-blue-200 transition"
                            title="Check In Photo">
                            📷
                          </a>
                        @endif
                        @if ($attendance->check_out_photo)
                          <a href="{{ Storage::url($attendance->check_out_photo) }}" target="_blank"
                            class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded text-xs hover:bg-red-200 transition"
                            title="Check Out Photo">
                            📷
                          </a>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                      <div class="space-y-2">
                        <p class="text-gray-500">{{ __('No attendance records found') }}</p>
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                          {{ __('Go to Dashboard') }}
                        </a>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          @if ($attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              {{ $attendances->links('pagination::tailwind') }}
            </div>
          @endif
        </div>

      </div>

      <!-- OVERTIME SECTION -->
      <div x-show="activeTab === 'overtime'" x-transition class="space-y-6">

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Total Requests') }}</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $overtimeRequests->total() }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Pending') }}</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">
              {{ $overtimeRequests->getCollection()->where('status', 'pending')->count() }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Approved') }}</p>
            <p class="text-3xl font-bold text-green-600 mt-2">
              {{ $overtimeRequests->getCollection()->where('status', 'approved')->count() }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">{{ __('Rejected') }}</p>
            <p class="text-3xl font-bold text-red-600 mt-2">
              {{ $overtimeRequests->getCollection()->where('status', 'rejected')->count() }}
            </p>
          </div>
        </div>

        <!-- Request Button -->
        <div class="flex gap-4">
          <a href="{{ route('overtime.create') }}"
            class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition">
            <span class="text-lg">⏰</span>
            {{ __('New Overtime Request') }}
          </a>
        </div>

        <!-- Overtime Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Request Date') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Start Time') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('End Time') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Duration') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Description') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Status') }}</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('Approver') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                @forelse($overtimeRequests as $overtime)
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <strong>{{ $overtime->created_at->format('d M Y') }}</strong>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                        {{ $overtime->start_time->format('H:i') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                        {{ $overtime->end_time->format('H:i') }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <span class="font-semibold text-indigo-600">{{ $overtime->duration_hours }} hrs</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <div class="max-w-xs">
                        <p class="truncate" title="{{ $overtime->description }}">
                          {{ $overtime->description ?? '-' }}
                        </p>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                      @if ($overtime->status === 'pending')
                        <span
                          class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                          <span class="w-2 h-2 bg-yellow-600 rounded-full"></span>
                          {{ __('Pending') }}
                        </span>
                      @elseif($overtime->status === 'approved')
                        <span
                          class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                          <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                          {{ __('Approved') }}
                        </span>
                      @else
                        <span
                          class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                          <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                          {{ __('Rejected') }}
                        </span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      @if ($overtime->approver)
                        <span class="text-gray-700">{{ $overtime->approver->name }}</span>
                      @else
                        <span class="text-gray-500">--</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                      <div class="space-y-2">
                        <p class="text-gray-500">{{ __('No overtime requests yet') }}</p>
                        <a href="{{ route('overtime.create') }}"
                          class="text-amber-600 hover:text-amber-700 font-semibold">
                          {{ __('Create First Request') }}
                        </a>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          @if ($overtimeRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              {{ $overtimeRequests->links('pagination::tailwind') }}
            </div>
          @endif
        </div>

      </div>

    </div>
  </div>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</x-app-layout>
