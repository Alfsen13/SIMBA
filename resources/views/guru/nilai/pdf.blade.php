<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('guru_analitik.nilai.pdf.document_title', ['nama' => $siswa->name]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #6b7280; }
        .header { border-bottom: 2px solid #374151; padding-bottom: 10px; margin-bottom: 16px; }
        .meta { font-size: 11px; margin-top: 6px; line-height: 1.6; }
        .summary { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .summary td { border: 1px solid #d1d5db; padding: 6px 8px; width: 25%; }
        .summary .label { color: #6b7280; font-size: 10px; }
        .summary .value { font-size: 14px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; }
        table.data th { background: #f3f4f6; text-align: left; font-size: 11px; }
        .center { text-align: center; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('guru_analitik.nilai.pdf.heading') }}</h1>
        <div class="muted">{{ __('guru_analitik.nilai.pdf.tagline') }}</div>
        <div class="meta">
            <strong>{{ __('guru_analitik.nilai.pdf.name_label') }}</strong> {{ $siswa->name }}
            @if($siswa->nisn) &nbsp;|&nbsp; <strong>{{ __('guru_analitik.nilai.pdf.nisn_label') }}</strong> {{ $siswa->nisn }} @endif
            &nbsp;|&nbsp; <strong>{{ __('guru_analitik.nilai.pdf.class_label') }}</strong> {{ $kelasDipilih?->nama_kelas ?? __('guru_analitik.nilai.pdf.all_classes') }}
            <br>
            <strong>{{ __('guru_analitik.nilai.pdf.teacher_label') }}</strong> {{ $guru->name }}
            &nbsp;|&nbsp; <strong>{{ __('guru_analitik.nilai.pdf.printed_label') }}</strong> {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td><div class="label">{{ __('guru_analitik.nilai.rata_kuis') }}</div><div class="value">{{ $rataKuis === null ? '-' : number_format($rataKuis, 1) }}</div></td>
            <td><div class="label">{{ __('guru_analitik.nilai.rata_tugas') }}</div><div class="value">{{ $rataTugas === null ? '-' : number_format($rataTugas, 1) }}</div></td>
            <td><div class="label">{{ __('guru_analitik.nilai.rata_total') }}</div><div class="value">{{ $rataTotal === null ? '-' : number_format($rataTotal, 1) }}</div></td>
            <td><div class="label">{{ __('guru_analitik.nilai.nilai_terisi') }}</div><div class="value">{{ $jumlahDinilai }}</div></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="center" style="width: 24px;">{{ __('guru_analitik.label.no') }}</th>
                <th style="width: 50px;">{{ __('guru_analitik.label.type') }}</th>
                <th>{{ __('guru_analitik.label.title') }}</th>
                <th style="width: 110px;">{{ __('guru_analitik.label.class') }}</th>
                <th style="width: 120px;">{{ __('guru_analitik.label.meeting') }}</th>
                <th class="center" style="width: 55px;">{{ __('guru_analitik.label.grade') }}</th>
                <th style="width: 80px;">{{ __('guru_analitik.label.date') }}</th>
                <th style="width: 70px;">{{ __('guru_analitik.label.notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $row['jenis'] }}</td>
                    <td>{{ $row['judul'] }}</td>
                    <td>{{ $row['kelas'] ?? '-' }}</td>
                    <td>{{ $row['pertemuan'] ?? '-' }}</td>
                    <td class="center">{{ $row['nilai'] === null ? '-' : number_format($row['nilai'], 1) }}</td>
                    <td>{{ $row['tanggal']?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $row['catatan'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">{{ __('guru_analitik.nilai.no_score_for_student') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ __('guru_analitik.nilai.pdf.footer') }}</div>
</body>
</html>
