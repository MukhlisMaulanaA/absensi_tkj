<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Request Leave / Sick / Permission') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
        <div class="p-6 text-gray-900">

          @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
              {{ session('status') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <h3 class="text-base font-semibold text-gray-800 mb-1">Submit Leave Request</h3>

          <div
            class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg px-3 py-1.5 text-xs font-medium mb-6">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <path d="M16 2v4M8 2v4M3 10h18" />
            </svg>
            {{ now()->translatedFormat('l, d F Y') }}
          </div>

          <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-6">

              <!-- Leave Type -->
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Leave Type <span class="text-red-400">*</span>
                </label>
                <select name="type" required
                  class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  value="{{ old('type') }}">
                  <option value="">-- Select Leave Type --</option>
                  <option value="sick" {{ old('type') === 'sick' ? 'selected' : '' }}>Sakit</option>
                  <option value="permission" {{ old('type') === 'permission' ? 'selected' : '' }}>Izin</option>
                  <option value="leave" {{ old('type') === 'leave' ? 'selected' : '' }}>Cuti</option>
                </select>
              </div>

              <!-- Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Start Date <span class="text-red-400">*</span>
                </label>
                <input type="date" name="start_date" required
                  class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  value="{{ old('start_date', now()->toDateString()) }}" />
              </div>

              <!-- End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  End Date <span class="text-red-400">*</span>
                </label>
                <input type="date" name="end_date" required
                  class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  value="{{ old('end_date', now()->toDateString()) }}" />
              </div>

              <!-- Reason -->
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Reason <span class="text-red-400">*</span>
                </label>
                <textarea name="reason" rows="4" required
                  class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  placeholder="Explain your reason for this leave request...">{{ old('reason') }}</textarea>
              </div>

              <!-- Attachment (Optional) -->
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">
                  Attachment (Optional)
                </label>
                <p class="text-xs text-gray-500 mb-2">
                  For sick leave, attach a doctor's note. File types: PDF, JPG, PNG. Max size: 5MB
                </p>
                <div class="relative">
                  <input type="file" name="attachment" id="attachmentInput" accept="application/pdf,image/jpeg,image/png" class="hidden" />

                  <label for="attachmentInput"
                    class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-colors">
                    <div class="text-center">
                      <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      <p id="attachmentLabel" class="text-sm text-gray-600">
                        <span class="font-medium text-blue-600">Click to select</span> or drag file here
                      </p>
                      <p id="attachmentCount" class="text-xs text-gray-400 mt-1"></p>
                    </div>
                  </label>

                  <div id="attachmentPreview" class="mt-3 hidden">
                    <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div class="flex-1">
                        <p id="attachmentFileName" class="text-sm font-medium text-blue-900"></p>
                        <p id="attachmentFileSize" class="text-xs text-blue-700"></p>
                      </div>
                      <button type="button" id="removeAttachment"
                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">Remove</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Info Box -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">📋 Important Information</h4>
                <ul class="text-xs text-blue-800 space-y-1">
                  <li>• Your leave request will be submitted for approval</li>
                  <li>• Admin will review and approve or reject your request</li>
                  <li>• Once approved, attendance records will be automatically created</li>
                  <li>• You cannot submit overlapping leave requests</li>
                </ul>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-3">
                <button type="submit"
                  class="inline-flex items-center px-5 py-2.5 bg-blue-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition-colors">
                  Submit Request
                </button>
                <a href="{{ route('dashboard') }}"
                  class="text-sm text-gray-400 hover:text-gray-700">Cancel</a>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Handle attachment file input
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentLabel = document.getElementById('attachmentLabel');
    const attachmentCount = document.getElementById('attachmentCount');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const attachmentFileName = document.getElementById('attachmentFileName');
    const attachmentFileSize = document.getElementById('attachmentFileSize');
    const removeAttachmentBtn = document.getElementById('removeAttachment');

    attachmentInput.addEventListener('change', function (e) {
      const file = e.target.files[0];

      if (file) {
        attachmentFileName.textContent = file.name;
        attachmentFileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
        attachmentPreview.classList.remove('hidden');
        attachmentLabel.classList.add('hidden');
        attachmentCount.textContent = '1 file selected';
      }
    });

    removeAttachmentBtn.addEventListener('click', function (e) {
      e.preventDefault();
      attachmentInput.value = '';
      attachmentPreview.classList.add('hidden');
      attachmentLabel.classList.remove('hidden');
      attachmentCount.textContent = '';
    });

    // Drag and drop
    const dropZone = document.querySelector('[for="attachmentInput"]').parentElement;

    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
      dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropZone.classList.remove('border-blue-500', 'bg-blue-50');

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        attachmentInput.files = files;
        const event = new Event('change', { bubbles: true });
        attachmentInput.dispatchEvent(event);
      }
    });
  </script>
</x-app-layout>
