<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Compra</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px solid #f33f3f; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #f33f3f; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; text-transform: uppercase; font-size: 12px; }
        .total { text-align: right; font-size: 1.2rem; font-weight: bold; margin-top: 20px; color: #f33f3f; }
        .info-cliente { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PIXELSTORE</h1>
        <p>Comprobante de Compra #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
        <p>Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-cliente">
        <strong>Datos del Cliente:</strong><br>
        Nombre: {{ $order->user->name }}<br>
        Email: {{ $order->user->email }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $granTotal = 0; @endphp
            @foreach($order->products as $product)
            @php $granTotal += $product->pivot->unit_price * $product->pivot->quantity; @endphp
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->pivot->quantity }}</td>
                <td>${{ number_format($product->pivot->unit_price, 2) }}</td>
                <td>${{ number_format($product->pivot->unit_price * $product->pivot->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Pagado: ${{ number_format($granTotal, 2) }}
    </div>
</body>
</html>