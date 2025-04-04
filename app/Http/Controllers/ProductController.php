<?php


namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\PriceApiService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $priceApiService;

    public function __construct(PriceApiService $priceApiService)
    {
        $this->priceApiService = $priceApiService;
    }

    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }
        
        $products = $query->with('category')->paginate(12);
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'priceHistories' => function($query) {
                $query->orderBy('price_date', 'desc');
            }, 'priceHistories.retailer'])
            ->firstOrFail();
        
        // Obtener el historial de precios para gráficos
        $priceHistory = $product->priceHistories()
            ->orderBy('price_date', 'asc')
            ->get()
            ->groupBy(function($date) {
                return $date->price_date->format('Y-m-d');
            })
            ->map(function($day) {
                return $day->min('price');
            });
        
        // Preparar datos para gráfico
        $chartLabels = $priceHistory->keys()->toJson();
        $chartData = $priceHistory->values()->toJson();
        
        return view('products.show', compact('product', 'chartLabels', 'chartData'));
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|string|min:3',
            'category' => 'nullable|exists:categories,id'
        ]);
        
        $category = $request->category;
        $results = $this->priceApiService->searchProducts($request->query, $category);
        
        return view('products.search_results', compact('results', 'category'));
    }

    public function saveFromApi(Request $request)
    {
        $this->validate($request, [
            'api_product' => 'required',
            'category_id' => 'required|exists:categories,id'
        ]);
        
        $apiProduct = json_decode($request->api_product, true);
        $product = $this->priceApiService->saveProduct($apiProduct, $request->category_id);
        
        return redirect()->route('products.show', $product->slug)
            ->with('success', 'Producto guardado correctamente');
    }
}