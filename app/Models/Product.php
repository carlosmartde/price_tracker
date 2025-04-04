<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image_url', 'brand', 'model', 'category_id', 'specifications', 'is_active'];

    protected $casts = [
        'specifications' => 'array',
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function getCurrentPrices()
    {
        return $this->priceHistories()
            ->selectRaw('retailer_id, MAX(price_date) as latest_date')
            ->groupBy('retailer_id')
            ->with(['retailer', 'latestPrice']);
    }

    public function getLowestPrice()
    {
        return $this->priceHistories()
            ->orderBy('price', 'asc')
            ->first();
    }
}