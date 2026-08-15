<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tiket Dapur - Meja {{ $pesanan->meja->nomor ?? '-' }}</title>
    <style>
        @page { margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            font-size: 14px;
            width: 80mm;
            margin: 0 auto;
        }
        .center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin: 6px 0; }
        .item-name { font-weight: bold; }
        .option { font-weight: bold; }
        .note { white-space: pre-wrap; border: 1px solid #000; padding: 4px 6px; margin: 8px 0; }
        .qty { font-size: 16px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        <div class="item-name">BURJOORDER</div>
        <div>TIKET DAPUR</div>
    </div>
    <div class="divider"></div>
    <div class="row"><span>Meja</span><span>: {{ $pesanan->meja->nomor ?? '-' }}</span></div>
    <div class="row"><span>Waktu</span><span>: {{ $pesanan->created_at->format('d/m/Y H:i:s') }}</span></div>
    <div class="row"><span>Order #</span><span>: {{ $pesanan->id }}</span></div>
    <div class="divider"></div>

    @foreach($pesanan->details as $detail)
        <div class="item">
            <div class="row">
                <span class="item-name">{{ $detail->menu->nama ?? 'Menu' }}</span>
                <span class="qty">x{{ $detail->jumlah }}</span>
            </div>
            @if($detail->selected_option)
                <div class="option">[ {{ strtoupper($detail->selected_option) }} ]</div>
            @endif
        </div>
    @endforeach

    @if($pesanan->catatan)
        <div class="divider"></div>
        <div>Catatan:</div>
        <div class="note">{{ $pesanan->catatan }}</div>
    @endif

    <div class="divider"></div>
    <div class="center" style="font-size:12px;">*** {{ strtoupper($pesanan->status->value) }} ***</div>

    <div class="no-print" style="margin-top:24px; text-align:center;">
        <button onclick="window.print()" style="padding:10px 20px; font-size:14px; cursor:pointer;">Cetak Ulang</button>
    </div>
</body>
</html>
