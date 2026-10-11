<x-mail::message>
# ¡Gracias por tu compra, {{ $order->user->name }}!

Tu orden ha sido procesada exitosamente y tus componentes ya se están preparando para el envío. 

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

### **Total Pagado:** ${{ number_format($granTotal, 2) }}

Si tienes alguna duda con tu pedido, puedes responder directamente a este correo.

<x-mail::button :url="route('products.index')" color="error">
Volver a la Tienda
</x-mail::button>

Gracias por tu preferencia,<br>
**El equipo de {{ config('app.name') }}**
</x-mail::message>