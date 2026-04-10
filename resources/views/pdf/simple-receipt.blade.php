<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 13px; color: #333; padding: 30px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #FF6A00; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #FF6A00; margin-bottom: 4px; }
        .header .store-name { font-size: 16px; font-weight: bold; color: #111; }
        .header .subtitle { font-size: 11px; color: #888; margin-top: 4px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: bold; padding: 3px 10px 3px 0; width: 140px; color: #555; }
        .info-value { display: table-cell; padding: 3px 0; }
        table.items { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table.items th { background: #f8f8f8; border-bottom: 2px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        table.items td { border-bottom: 1px solid #eee; padding: 7px 8px; }
        table.items .right { text-align: right; }
        .total-row { font-weight: bold; font-size: 15px; text-align: right; margin-top: 10px; padding: 10px; background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 4px; }
        .notes { margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px; font-size: 12px; }
        .disclaimer { margin-top: 25px; padding: 12px; background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 4px; font-size: 11px; color: #92400E; text-align: center; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #aaa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPROBANTE DE VENTA</h1>
        <div class="store-name">{{ $store->name ?? 'Tienda' }}</div>
        <div class="subtitle">{{ $store->address ?? '' }} {{ $store->city ? '- ' . $store->city : '' }}</div>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">No. Comprobante:</span>
            <span class="info-value">{{ $receipt->receipt_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span class="info-value">{{ $receipt->receipt_date->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pedido:</span>
            <span class="info-value">#{{ $order->id }}</span>
        </div>
    </div>

    @if(count($items) > 0)
    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="right">Cant.</th>
                <th class="right">Precio</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->product_name ?? $item->name ?? 'Producto' }}</td>
                <td class="right">{{ $item->quantity ?? 1 }}</td>
                <td class="right">${{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                <td class="right">${{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="total-row">
        TOTAL: ${{ number_format($receipt->total, 0, ',', '.') }} COP
    </div>

    @if($receipt->notes)
    <div class="notes">
        <strong>Notas:</strong> {{ $receipt->notes }}
    </div>
    @endif

    <div class="disclaimer">
        Este documento es un comprobante de venta interno.<br>
        <strong>NO es una factura electr&oacute;nica v&aacute;lida ante la DIAN.</strong>
    </div>

    <div class="footer">
        Generado por ComercioPlus &mdash; {{ $generated }}
    </div>
</body>
</html>
