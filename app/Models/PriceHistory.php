<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'retailer_id', 'price', 'currency', 'product_url', 'in_stock', 'price_date'];

    protected $casts = [
        'price_date' => 'datetime',
        'in_stock' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }
}