<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PriceApiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        // BestBuy API - Obtendrás tu API key al registrarte en https://developer.bestbuy.com/
        $this->apiKey = env('NPTjtN6valvL9JVAFbuNIORc');
        $this->baseUrl = 'https://api.bestbuy.com/v1/';
    }

    /**
     * Buscar productos en la API
     */
    public function searchProducts($query, $category = null, $limit = 10)
    {
        try {
            $categoryFilter = $category ? "(categoryPath.id=" . $category . ")" : "";
            $url = $this->baseUrl . "products(search=" . urlencode($query) . $categoryFilter . ")";
            
            $response = Http::get($url, [
                'apiKey' => $this->apiKey,
                'format' => 'json',
                'show' => 'sku,name,salePrice,manufacturer,image,url,modelNumber,categoryPath',
                'pageSize' => $limit
            ]);

            if ($response->successful()) {
                return $response->json()['products'];
            } else {
                Log::error('API Error: ' . $response->status() . ' - ' . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Exception in API call: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar un producto desde la API en nuestra base de datos
     */
    public function saveProduct($apiProduct, $categoryId)
    {
        // Encontrar o crear el retailer para BestBuy
        $retailer = Retailer::firstOrCreate(
            ['slug' => 'best-buy'],
            [
                'name' => 'Best Buy',
                'website_url' => 'https://www.bestbuy.com',
                'logo_url' => '/images/retailers/bestbuy.png'
            ]
        );

        // Crear o actualizar el producto
        $product = Product::updateOrCreate(
            ['slug' => Str::slug($apiProduct['name'] . '-' . $apiProduct['sku'])],
            [
                'name' => $apiProduct['name'],
                'description' => $apiProduct['name'],
                'image_url' => $apiProduct['image'],
                'brand' => $apiProduct['manufacturer'],
                'model' => $apiProduct['modelNumber'] ?? null,
                'category_id' => $categoryId,
                'specifications' => json_encode([
                    'sku' => $apiProduct['sku'],
                    'categories' => $apiProduct['categoryPath'] ?? []
                ]),
                'is_active' => true
            ]
        );

        // Registrar el precio actual
        PriceHistory::create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
            'price' => $apiProduct['salePrice'],
            'currency' => 'USD', // BestBuy usa USD
            'product_url' => $apiProduct['url'],
            'in_stock' => true,
            'price_date' => Carbon::now()
        ]);

        return $product;
    }

    /**
     * Actualizar precios de productos existentes
     */
    public function updateProductPrices()
    {
        $products = Product::where('is_active', true)->get();
        $retailer = Retailer::where('slug', 'best-buy')->first();
        
        foreach ($products as $product) {
            $specs = json_decode($product->specifications, true);
            $sku = $specs['sku'] ?? null;
            
            if (!$sku) continue;
            
            try {
                $url = $this->baseUrl . "products(sku={$sku})";
                $response = Http::get($url, [
                    'apiKey' => $this->apiKey,
                    'format' => 'json',
                    'show' => 'sku,salePrice,url'
                ]);
                
                if ($response->successful() && !empty($response->json()['products'])) {
                    $apiProduct = $response->json()['products'][0];
                    
                    PriceHistory::create([
                        'product_id' => $product->id,
                        'retailer_id' => $retailer->id,
                        'price' => $apiProduct['salePrice'],
                        'currency' => 'USD',
                        'product_url' => $apiProduct['url'],
                        'in_stock' => true,
                        'price_date' => Carbon::now()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error updating product {$product->id}: " . $e->getMessage());
            }
        }
    }
}