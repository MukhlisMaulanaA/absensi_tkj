<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preview Timesheet - {{ $user->name }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 h-screen flex flex-col overflow-hidden">

  <div class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex justify-between items-center shadow-lg">
    <div>
      <h1 class="text-white font-bold text-lg">Pratinjau Dokumen Absensi</h1>
      <p class="text-gray-400 text-xs mt-0.5">{{ $filename }}</p>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="window.frames['pdfFrame'].focus(); window.frames['pdfFrame'].print();"
        class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-xl text-sm transition shadow-md">
        Cetak Dokumen
      </button>
      <button onclick="window.close();"
        class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium px-4 py-2 rounded-xl text-sm transition">
        Tutup Halaman
      </button>
    </div>
  </div>

  <div class="flex-1 bg-gray-900 p-4 md:p-6 flex justify-center items-center">
    <iframe name="pdfFrame" src="data:application/pdf;base64,{{ $pdfData }}#toolbar=1"
      class="w-full h-full max-w-5xl rounded-2xl shadow-2xl border border-gray-800 bg-white" allow="print">
    </iframe>
  </div>

</body>

</html>
