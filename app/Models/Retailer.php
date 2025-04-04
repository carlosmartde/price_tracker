<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'website_url', 'logo_url'];

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }
}