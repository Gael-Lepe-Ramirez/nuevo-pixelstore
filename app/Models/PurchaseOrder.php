<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

// Entidad ORDEN_COMPRA: reabastecimiento de inventario a un proveedor.
class PurchaseOrder extends Model
{
    use HasFactory;

    public const PENDING = 'pendiente';
    public const RECEIVED = 'recibida';
    public const CANCELLED = 'cancelada';

    protected $fillable = ['supplier_id', 'ordered_at', 'received_at', 'status'];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'date',
            'received_at' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // DETALLE_ORDEN_COMPRA
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'purchase_order_product')
            ->withPivot('quantity', 'unit_cost')
            ->withTimestamps();
    }

    public function total(): float
    {
        return (float) $this->products->sum(fn ($p) => $p->pivot->quantity * $p->pivot->unit_cost);
    }

    /**
     * RN1: el inventario solo aumenta al marcar la orden como «recibida».
     * Se hace en una transacción para que la orden y las existencias cambien juntas.
     */
    public function receive(?\DateTimeInterface $date = null): void
    {
        DB::transaction(function () use ($date) {
            $order = static::whereKey($this->getKey())->lockForUpdate()->first();

            if ($order->status !== self::PENDING) {
                throw new InvalidArgumentException('Solo se puede recibir una orden de compra pendiente.');
            }

            $lines = $order->products()->get();
            if ($lines->isEmpty()) {
                throw new InvalidArgumentException('La orden de compra no tiene productos (RN5).');
            }

            foreach ($lines as $product) {
                Product::whereKey($product->id)->increment('stock', $product->pivot->quantity);
            }

            $order->update([
                'status' => self::RECEIVED,
                'received_at' => $date ?? now(),
            ]);

            $this->refresh();
        });
    }

    public function cancel(): void
    {
        if ($this->status !== self::PENDING) {
            throw new InvalidArgumentException('Solo se puede cancelar una orden de compra pendiente.');
        }

        $this->update(['status' => self::CANCELLED]);
    }
}
