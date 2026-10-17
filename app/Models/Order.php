<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pendiente', 'enviado', 'entregado', 'cancelado'];
    public const PAYMENT_STATUSES = ['pendiente', 'pagado', 'rechazado'];

    protected $fillable = [
        'user_id', 'shipping_method_id', 'payment_method_id', 'status', 'payment_status',
        'shipping_address', 'total_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // DETALLE_PEDIDO: unit_price guarda el precio vigente al momento de la compra (RN3).
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    public function subtotal(): float
    {
        return (float) $this->products->sum(fn ($p) => $p->pivot->quantity * $p->pivot->unit_price);
    }
}