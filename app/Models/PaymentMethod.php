<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Entidad METODO_PAGO
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
