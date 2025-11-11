<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Batik Caraka</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #000;
            line-height: 1.4;
        }

        .container {
            width: 100%;
        }

        .header-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .header-table td {
            border: none !important;
            vertical-align: middle;
            text-align: center;
            padding: 5px;
        }

        .header-table img {
            height: 60px;
            width: auto;
        }

        .address {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .print-date {
            text-align: right;
            font-size: 12px;
            margin-bottom: 20px;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            border-style: double;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        th {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }

        td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
            font-size: 12px;
        }

        .signature .line {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 50px auto 5px auto;
        }

        .currency {
            white-space: nowrap;
        }

        .nametag {
            position: absolute;
            right: 10px;
            margin-top: 60px;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo">
                    <img src="{{ public_path('logo-pokdarwis.png') }}" alt="Pokdarwis">
                </td>
                <td class="title" style="display: flex; flex-direction: column;">
                    <div style="font-weight: bold; font-size: 18px;">
                        {{ $report_title ?? 'Laporan Keuangan' }}
                    </div>
                    <div class="address">
                        Jl. Raya Banjarpanepen No. 45, Sumpiuh, Banyumas<br>
                        Email: visitbanpen@gmail.com
                    </div>
                </td>
                <td class="logo">
                    <img src="{{ public_path('logo-bms.png') }}" alt="Banyumas">
                </td>
            </tr>
        </table>

        <hr>

        <!-- Tanggal Cetak -->
        <div class="print-date">
            Tanggal Cetak: {{ \Carbon\Carbon::parse($print_date ?? now())->format('d F Y') }}
        </div>

        <!-- Tabel Transaksi -->
        <table>
            <thead>
                <tr>
                    <th width="15%">Tanggal Transaksi</th>
                    <th width="30%">Uraian Transaksi</th>
                    <th width="15%">Pemasukan</th>
                    <th width="15%">Pengeluaran</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $running_total = 0;
                @endphp
                @foreach ($transactions as $index => $t)
                    @php
                        $income = $t->type === 'income' ? $t->amount : 0;
                        $expense = $t->type === 'expense' ? $t->amount : 0;
                        $running_total += $income - $expense;
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y') }}</td>
                        <td class="text-left">{{ $t->title }}</td>
                        <td class="text-right currency">
                            {{ $income > 0 ? 'Rp' . number_format($income, 0, ',', '.') : '-' }}</td>
                        <td class="text-right currency">
                            {{ $expense > 0 ? 'Rp' . number_format($expense, 0, ',', '.') : '-' }}</td>
                        <td class="text-right currency">Rp{{ number_format($running_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="4" class="text-center"><strong>Total</strong></td>
                    <td class="text-right"><strong>Rp{{ number_format($running_total, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="signature">
            <p>Banjarpanepen, {{ \Carbon\Carbon::parse($print_date ?? now())->format('d F Y') }}</p>
            <div
                style="display:flex; flex-direction: column; justify-items: center; margin-right: 40px; margin-top: -20px">
                <p><strong>Ketua Pokdarwis</strong></p>
                <p class="nametag"><em>(..............................................)</em></p>
            </div>
        </div>

    </div>
</body>

</html>
