<x-mail::message>
# ¡Gracias por tu compra, {{ $order->user->name }}!

Tu orden ha sido registrada. Entrega en: {{ $order->shipping_address }} ({{ $order->shippingMethod->name }}). Pago: {{ $order->paymentMethod->name }}, estado «{{ $order->payment_status }}».

Aquí tienes el resumen exacto de tu transacción:

@php $granTotal = 0; @endphp

<x-mail::table>
| Producto       | Cantidad   | Precio Unitario | Subtotal  |
|:---------------|:----------:|:----------------|:----------|
@foreach($order->products as $product)
@php $granTotal += $product->pivot->unit_price * $product->pivot->quantity; @endphp
| {{ $product->name }} | {{ $product->pivot->quantity }} | ${{ number_format($product->pivot->unit_price, 2) }} | ${{ number_format($product->pivot->unit_price * $product->pivot->quantity, 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** ${{ number_format($granTotal, 2) }}  
**Envío:** ${{ number_format($order->total_amount - $granTotal, 2) }}

### **Total:** ${{ number_format($order->total_amount, 2) }}

Si tienes alguna duda con tu pedido, puedes responder directamente a este correo.

<x-mail::button :url="route('products.index')" color="error">
Volver a la Tienda
</x-mail::button>

Gracias por tu preferencia,<br>
**El equipo de {{ config('app.name') }}**
</x-mail::message>