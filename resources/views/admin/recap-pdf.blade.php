<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Recap Penjualan Harian</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #14171B; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #1E2229; color: #F6F1E7; }
        td.num, th.num { text-align: right; }
        .total-row td { font-weight: bold; background: #EDE7DA; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
        .badge-final { background: #4E9A51; color: #fff; }
        .badge-pending { background: #D64545; color: #fff; }
    </style>
</head>
<body>
    <h1>Recap Penjualan Harian</h1>
    <div class="meta">
        Dicetak: {{ now()->translatedFormat('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="num">Pendapatan</th>
                <th class="num">Pesanan</th>
                <th class="num">Item</th>
                <th>Top Menu</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recaps as $recap)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($recap->period_start)->translatedFormat('d M Y') }}</td>
                    <td class="num">Rp {{ number_format($recap->total_revenue, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($recap->total_orders, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($recap->total_items_sold, 0, ',', '.') }}</td>
                    <td>
                        @if(!empty($recap->top_menus))
                            @foreach(array_slice($recap->top_menus, 0, 3) as $menu)
                                {{ $loop->iteration }}. {{ $menu['nama'] }} ({{ $menu['total'] }})<br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($recap->is_finalized)
                            <span class="badge badge-final">FINAL</span>
                        @else
                            <span class="badge badge-pending">PENDING</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">Belum ada recap.</td></tr>
            @endforelse

            <tr class="total-row">
                <td>Total</td>
                <td class="num">Rp {{ number_format($totals['total_revenue'], 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['total_orders'], 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['total_items_sold'], 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
