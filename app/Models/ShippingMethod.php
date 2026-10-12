<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Entidad METODO_ENVIO
class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'cost', 'estimated_days'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
