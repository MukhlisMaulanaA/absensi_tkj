<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Timesheet - {{ $user->name }}</title>
  <style>
    /* A4 sizing and basic reset */
    @page { size: A4; margin: 20mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 12px; }
    .meta { display:flex; gap: 18px; }
    .meta div { line-height: 1.4; }
    .title { text-align:center; margin-bottom: 6px; font-weight:700; }

    table { width:100%; border-collapse: collapse; border: 1px solid #000; }
    th, td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
    th { background: #f2f2f2; font-weight:700; }
    td.center { text-align:center; }
    td.left { text-align:left; }

    /* Signature placeholder style */
    .ttd { height: 36px; }
    .handwritten { font-family: 'Brush Script MT', 'Segoe Script', 'Caveat', cursive; font-size: 13px; }

    /* Sunday row highlight */
    .sunday { background: #fff7ed; }

    .footer { display:flex; justify-content:space-between; margin-top: 12px; }
    .summary { width: 40%; }
    .sigs { width: 55%; display:flex; justify-content:space-between; }
    .sig-block { width:45%; text-align:left; }
    .sig-line { margin-top: 48px; border-bottom: 1px solid #000; width: 70%; }

    /* Small print adjustments */
    .small { font-size: 11px; }
  </style>
</head>
<body>
  <div class="title">REKAP ABSENSI</div>

  <div class="header">
    <div class="meta">
      <div><strong>Nama:</strong> {{ $user->name }}</div>
      <div><strong>Project:</strong> {{ $project ?? '-' }}</div>
      <div><strong>Jabatan:</strong> {{ $jabatan ?? '-' }}</div>
    </div>
    <div class="meta" style="text-align:right">
      <div><strong>Periode:</strong></div>
      <div>{{ $period_string }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:8%">Hari</th>
        <th style="width:18%">Tgl - Bln</th>
        <th style="width:12%">In</th>
        <th style="width:12%">Ttd</th>
        <th style="width:12%">Out</th>
        <th style="width:12%">Ttd</th>
        <th style="width:26%">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr class="{{ $row['is_sunday'] ? 'sunday' : '' }}">
          <td class="center">{{ $row['day'] }}</td>
          <td class="center">{{ $row['date_display'] }}</td>
          <td class="center">{{ $row['in'] }}</td>
          <td class="ttd"></td>
          <td class="center">{{ $row['out'] }}</td>
          <td class="ttd"></td>
          <td class="left small">{{ $row['keterangan'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    <div class="summary small">
      <div><strong>Hari Kerja:</strong> {{ $summary['hari_kerja'] }}</div>
      <div><strong>Libur Masuk:</strong> {{ $summary['libur_masuk'] }}</div>
      <div><strong>Overtime (Jam):</strong> {{ $summary['overtime_hours'] }}</div>
    </div>

    <div class="sigs">
      <div class="sig-block">
        <div>Checked by:</div>
        <div class="sig-line"></div>
      </div>
      <div class="sig-block">
        <div>Approved by:</div>
        <div class="sig-line"></div>
      </div>
    </div>
  </div>

</body>
</html>
