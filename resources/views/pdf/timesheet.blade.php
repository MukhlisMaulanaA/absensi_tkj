<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Absen Harian - {{ $user->name }}</title>
  <style>
    @page { size: A4; margin: 18mm 20mm; }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #111;
      background: #fff;
    }

    /* ── Company Header ── */
    .company-header {
      text-align: center;
      margin-bottom: 8px;
    }
    .company-name {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      text-decoration: underline;
    }
    .company-subtitle {
      font-size: 11px;
      font-weight: 700;
      text-decoration: underline;
      margin-top: 2px;
    }

    /* ── Meta info block ── */
    .meta-block {
      margin: 8px 0 6px 0;
    }
    .meta-row {
      display: flex;
      align-items: baseline;
      line-height: 1.75;
      font-size: 11px;
    }
    .meta-label {
      width: 65px;
    }
    .meta-colon {
      width: 14px;
      text-align: center;
    }
    .meta-value {
      flex: 1;
    }

    /* ── Main Table ── */
    table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #333;
      margin-top: 4px;
    }

    th, td {
      border: 1px solid #555;
      padding: 4px 5px;
      font-size: 10.5px;
      vertical-align: middle;
    }

    thead tr th {
      background: #e0e0e0;
      font-weight: 700;
      text-align: center;
      font-size: 10.5px;
      padding: 5px 4px;
    }

    td.center    { text-align: center; }
    td.left      { text-align: left; }

    /* Signature / TTD cell */
    td.ttd {
      min-width: 36px;
      height: 22px;
      padding: 2px 4px;
      text-align: center;
    }

    /* In/Out time cell */
    td.time-cell {
      text-align: center;
    }

    /* Sunday / Minggu row — orange tint matching the image */
    tr.sunday td {
      background-color: #ffe0b2;
    }

    tr.weekday td {
      background-color: #ffffff;
    }

    td.keterangan {
      text-align: left;
      font-size: 10px;
    }

    td.day-cell {
      text-align: center;
    }

    /* ── Footer ── */
    .footer-area {
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .summary-block {
      width: 42%;
      font-size: 11px;
    }

    .summary-row {
      display: flex;
      align-items: baseline;
      line-height: 1.85;
    }
    .summary-label  { width: 100px; }
    .summary-colon  { width: 14px; }
    .summary-unit   { margin-left: 5px; }

    /* Signature area */
    .sig-area {
      width: 54%;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding-top: 2px;
    }

    .sig-block {
      width: 46%;
      font-size: 11px;
    }

    .sig-block .sig-label {
      text-align: left;
      margin-bottom: 2px;
    }

    .sig-line-wrap {
      display: flex;
      align-items: center;
      margin-top: 46px;
    }

    .sig-bracket { font-size: 11px; }

    .sig-dots {
      border-bottom: 1px solid #333;
      width: 110px;
      margin: 0 1px;
    }
  </style>
</head>
<body>

  {{-- ── Company Header ── --}}
  <div class="company-header">
    <div class="company-name">PT. TANJUNG KARYA JAYA</div>
    <div class="company-subtitle">Absen Harian</div>
  </div>

  {{-- ── Meta Info ── --}}
  <div class="meta-block">
    <div class="meta-row">
      <span class="meta-label">Nama</span>
      <span class="meta-colon">:</span>
      <span class="meta-value">{{ $user->name }}</span>
    </div>
    <div class="meta-row">
      <span class="meta-label">Project</span>
      <span class="meta-colon">:</span>
      <span class="meta-value">{{ $project ?? '-' }}</span>
    </div>
    <div class="meta-row">
      <span class="meta-label">Jabatan</span>
      <span class="meta-colon">:</span>
      <span class="meta-value">{{ $jabatan ?? '-' }}</span>
    </div>
    <div class="meta-row">
      <span class="meta-label">Periode</span>
      <span class="meta-colon">:</span>
      <span class="meta-value">{{ $period_string }}</span>
    </div>
  </div>

  {{-- ── Attendance Table ── --}}
  <table>
    <thead>
      <tr>
        <th style="width:9%">Hari</th>
        <th style="width:17%">Tgl - Bln</th>
        <th style="width:11%">In</th>
        <th style="width:11%">Out</th>
        <th style="width:11%">Overtimes</th>
        <th style="width:32%">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr class="{{ $row['is_sunday'] ? 'sunday' : 'weekday' }}">
          <td class="day-cell">{{ $row['day'] }}</td>
          <td class="center">{{ $row['date_display'] }}</td>
          <td class="time-cell">{{ $row['in'] }}</td>
          <td class="time-cell">{{ $row['out'] }}</td>
          <td class="time-cell">{{ $row['overtime_hours_daily'] }} Jam</td>
          <td class="keterangan">{{ $row['keterangan'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ── Footer: Summary + Signatures ── --}}
  <div class="footer-area">

    <div class="summary-block">
      <div class="summary-row">
        <span class="summary-label">Hari kerja</span>
        <span class="summary-colon">:</span>
        <span class="summary-unit">{{ $summary['hari_kerja'] }} Hari</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Libur masuk</span>
        <span class="summary-colon">:</span>
        <span class="summary-unit">{{ $summary['libur_masuk'] }} Hari</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Over time</span>
        <span class="summary-colon">:</span>
        <span class="summary-unit">{{ $summary['overtime_hours'] }} Jam</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">U. Over time</span>
        <span class="summary-colon">:</span>
        <span class="summary-unit">{{ $summary['u_overtime_days'] ?? '-' }} Hari</span>
      </div>
      @if(isset($summary['casbon']))
      <div class="summary-row" style="margin-top:3px;">
        <span class="summary-label">Casbon</span>
        <span class="summary-colon">: Rp.</span>
        <span class="summary-unit" style="font-weight:700;">{{ number_format($summary['casbon'], 0, ',', '.') }}</span>
      </div>
      @endif
    </div>

    <div class="sig-area">
      <div class="sig-block">
        <div class="sig-label">Checked by : SYSTEM</div>
        <div class="sig-line-wrap">
          <span class="sig-bracket">(</span>
          <span class="sig-dots"></span>
          <span class="sig-bracket">)</span>
        </div>
      </div>
      <div class="sig-block">
        <div class="sig-label">Approved by :</div>
        <div class="sig-line-wrap">
          <span class="sig-bracket">(</span>
          <span class="sig-dots"></span>
          <span class="sig-bracket">)</span>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
