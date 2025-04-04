<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Productos con alertas del usuario
        $alertedProducts = Product::whereHas('priceAlerts', function($query) {
            $query->where('user_id', Auth::id())->where('is_active', true);
        })->with(['priceHistories' => function($query) {
            $query->orderBy('price_date', 'desc')->limit(1);
        }, 'priceAlerts' => function($query) {
            $query->where('user_id', Auth::id())->where('is_active', true);
        }])->take(5)->get();
        
        // Productos con mayores bajadas de precio
        $priceDrop = PriceHistory::selectRaw('product_id, 
            MAX(CASE WHEN price_date = (SELECT MAX(price_date) FROM price_histories WHERE product_id = p.product_id) THEN price END) as current_price,
            MAX(CASE WHEN price_date = (SELECT MAX(price_date) FROM price_histories WHERE product_id = p.product_id AND price_date < (SELECT MAX(price_date) FROM price_histories WHERE product_id = p.product_id)) THEN price END) as previous_price')
            ->from('price_histories as p')
            ->groupBy('product_id')
            ->havingRaw('previous_price > 0 AND ((previous_price - current_price) / previous_price) > 0.05')
            ->orderByRaw('(previous_price - current_price) / previous_price DESC')
            ->limit(5)
            ->with('product')
            ->get();
        
        return view('dashboard', compact('alertedProducts', 'priceDrop'));
    }
}