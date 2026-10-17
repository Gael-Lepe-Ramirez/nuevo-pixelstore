<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Order extends Model
{
    use HasFactory;

    public const PENDING = 'pendiente';
    public const SHIPPED = 'enviado';
    public const DELIVERED = 'entregado';
    public const CANCELLED = 'cancelado';

    public const PAY_PENDING = 'pendiente';
    public const PAID = 'pagado';
    public const PAY_REJECTED = 'rechazado';

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

    public function folio(): string
    {
        return str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    // ------------------------------------------------------------------
    // Reglas de estado (RN8). El estado de pago es independiente del estado
    // del pedido, pero un pedido solo puede pasar a «enviado» si ya está pagado.
    // Cada cambio corre en una transacción y bloquea el pedido para evitar
    // que dos clics seguidos lo cambien dos veces o devuelvan el stock dos veces.
    // ------------------------------------------------------------------

    public function canMarkPaid(): bool
    {
        return $this->status === self::PENDING && $this->payment_status === self::PAY_PENDING;
    }

    public function canRejectPayment(): bool
    {
        return $this->canMarkPaid();
    }

    public function canShip(): bool
    {
        return $this->status === self::PENDING && $this->payment_status === self::PAID;
    }

    public function canDeliver(): bool
    {
        return $this->status === self::SHIPPED;
    }

    public function canCancel(): bool
    {
        return $this->status === self::PENDING;
    }

    public function markAsPaid(): void
    {
        $this->transition(function (self $o) {
            if (! $o->canMarkPaid()) {
                throw new InvalidArgumentException('Solo se puede marcar como pagado un pedido pendiente con pago pendiente.');
            }
            $o->update(['payment_status' => self::PAID]);
        });
    }

    /** Pago rechazado: el pedido se cancela y las unidades regresan al inventario. */
    public function rejectPayment(): void
    {
        $this->transition(function (self $o) {
            if (! $o->canRejectPayment()) {
                throw new InvalidArgumentException('Solo se puede rechazar el pago de un pedido pendiente con pago pendiente.');
            }
            $o->restock();
            $o->update(['payment_status' => self::PAY_REJECTED, 'status' => self::CANCELLED]);
        });
    }

    public function ship(): void
    {
        $this->transition(function (self $o) {
            if ($o->status === self::PENDING && $o->payment_status !== self::PAID) {
                throw new InvalidArgumentException('RN8: el pedido no puede enviarse hasta que el pago esté confirmado.');
            }
            if (! $o->canShip()) {
                throw new InvalidArgumentException('Solo se puede enviar un pedido pendiente y pagado.');
            }
            $o->update(['status' => self::SHIPPED]);
        });
    }

    public function deliver(): void
    {
        $this->transition(function (self $o) {
            if (! $o->canDeliver()) {
                throw new InvalidArgumentException('Solo se puede marcar como entregado un pedido enviado.');
            }
            $o->update(['status' => self::DELIVERED]);
        });
    }

    /** Cancelación antes del envío: las unidades regresan al inventario (RN2). */
    public function cancel(): void
    {
        $this->transition(function (self $o) {
            if (! $o->canCancel()) {
                throw new InvalidArgumentException('Solo se puede cancelar un pedido que aún no se ha enviado.');
            }
            $o->restock();
            $o->update(['status' => self::CANCELLED]);
        });
    }

    private function transition(\Closure $change): void
    {
        DB::transaction(function () use ($change) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $change($locked);
        });

        $this->refresh();
    }

    private function restock(): void
    {
        foreach ($this->products()->get() as $product) {
            Product::whereKey($product->id)->increment('stock', $product->pivot->quantity);
        }
    }
}
