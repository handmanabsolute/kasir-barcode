<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan - {{ $label }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header p {
            font-size: 9pt;
            margin: 2px 0;
            color: #555;
        }
        .header .divider {
            border-top: 1px dashed #999;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 9pt;
        }

        h2 {
            font-size: 12pt;
            text-align: center;
            margin: 20px 0 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-grid {
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        .summary-table .label {
            font-weight: bold;
            background: #f5f5f5;
            width: 40%;
        }
        .summary-table .value {
            text-align: right;
            width: 60%;
        }
        .summary-table .value.bold {
            font-weight: bold;
            font-size: 11pt;
        }
        .summary-table .highlight {
            color: #059669;
            font-weight: bold;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }
        table.details th {
            background: #222;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.details th.right {
            text-align: right;
        }
        table.details td {
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
        }
        table.details td.right {
            text-align: right;
        }
        table.details tr.total td {
            border-top: 2px solid #000;
            font-weight: bold;
            padding-top: 8px;
        }
        table.details tr.alt td {
            background: #fafafa;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            font-size: 9pt;
        }
        .footer .signature {
            margin-top: 25px;
        }
        .footer .signature div {
            display: inline-block;
            width: 150px;
            text-align: center;
        }
        .footer .signature .line {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 40px;
        }

        .page-break {
            page-break-before: always;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            border: 1px solid #ddd;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>KASIR BARCODE</h1>
        <p>Laporan Penjualan {{ $period === 'daily' ? 'Harian' : 'Bulanan' }}</p>
        <div class="divider">
            <strong>Periode:</strong> {{ $label }}<br>
            <strong>Cetak:</strong> {{ now()->isoFormat('dddd, D MMMM Y H:mm') }}
        </div>
    </div>

    {{-- Summary --}}
    <h2>Ringkasan Penjualan</h2>
    <div class="summary-grid">
        <table class="summary-table">
            <tr>
                <td class="label">Total Transaksi</td>
                <td class="value">{{ number_format($total_transactions, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Produk Terjual</td>
                <td class="value">{{ number_format($products_sold, 0, ',', '.') }} unit</td>
            </tr>
            <tr>
                <td class="label">Total Pendapatan</td>
                <td class="value bold">{{ $total_revenue_formatted }}</td>
            </tr>
            <tr>
                <td class="label">Total Modal</td>
                <td class="value">{{ $total_cost_formatted }}</td>
            </tr>
            <tr>
                <td class="label">Laba Kotor</td>
                <td class="value bold highlight">{{ $total_profit_formatted }}</td>
            </tr>
            <tr>
                <td class="label">Rata-rata per Transaksi</td>
                <td class="value">{{ $average_transaction_formatted }}</td>
            </tr>
            <tr>
                <td class="label">Margin Keuntungan</td>
                <td class="value">{{ $profit_margin }}%</td>
            </tr>
        </table>
    </div>

    {{-- Product Details --}}
    <h2>Rincian Produk Terjual</h2>
    @if (count($product_sales) > 0)
        <table class="details">
            <thead>
                <tr>
                    <th style="width: 35%;">Produk</th>
                    <th class="right" style="width: 13%;">Terjual</th>
                    <th class="right" style="width: 18%;">Pendapatan</th>
                    <th class="right" style="width: 16%;">Modal</th>
                    <th class="right" style="width: 18%;">Laba</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product_sales as $i => $item)
                    <tr class="{{ $i % 2 === 0 ? '' : 'alt' }}">
                        <td>{{ $item['product_name'] }}</td>
                        <td class="right">{{ number_format((int) $item['total_qty'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((int) $item['total_revenue'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((int) $item['total_cost'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((int) $item['total_profit'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>TOTAL</td>
                    <td class="right">{{ number_format(collect($product_sales)->sum('total_qty'), 0, ',', '.') }}</td>
                    <td class="right">{{ $total_revenue_formatted }}</td>
                    <td class="right">{{ $total_cost_formatted }}</td>
                    <td class="right highlight">{{ $total_profit_formatted }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #999; margin: 20px 0;">Tidak ada data penjualan pada periode ini.</p>
    @endif

    {{-- Daily Breakdown for Monthly Report --}}
    @if ($period === 'monthly' && !empty($daily_breakdown))
        <div class="page-break"></div>
        <h2>Rincian Harian</h2>
        <p style="text-align: center; font-size: 9pt; margin-bottom: 15px;">
            Penjualan per hari selama {{ $label }}
        </p>
        <table class="details">
            <thead>
                <tr>
                    <th style="width: 40%;">Tanggal</th>
                    <th class="right" style="width: 20%;">Transaksi</th>
                    <th class="right" style="width: 20%;">Pendapatan</th>
                    <th class="right" style="width: 20%;">Laba</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daily_breakdown as $i => $day)
                    <tr class="{{ $i % 2 === 0 ? '' : 'alt' }}">
                        <td>{{ \Carbon\Carbon::parse($day['sale_date'])->isoFormat('dddd, D MMMM Y') }}</td>
                        <td class="right">{{ number_format((int) $day['transaction_count'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((int) $day['daily_revenue'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((int) $day['daily_profit'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p><em>Laporan ini digenerate secara otomatis oleh Sistem Kasir Barcode</em></p>
        <div class="signature">
            <div>
                <p>Hormat Kami,</p>
                <div class="line">({{ auth()->user()->name }})</div>
            </div>
        </div>
    </div>
</body>
</html>
