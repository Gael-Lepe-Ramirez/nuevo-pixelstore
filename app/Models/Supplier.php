<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Entidad PROVEEDOR
class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rfc', 'email', 'phone', 'address'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
