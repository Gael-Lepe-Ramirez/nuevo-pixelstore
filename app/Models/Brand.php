<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Entidad MARCA
class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country', 'website'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
