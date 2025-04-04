<?php
// app/Http/Livewire/ProductCard.php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\PriceAlert;
use Illuminate\Support\Facades\Auth;

class ProductCard extends Component
{
    public $product;
    public $showAlert = false;
    public $targetPrice;
    
    public function mount(Product $product)
    {
        $this->product = $product;
        
        // Obtener el precio más bajo actual para sugerir como precio objetivo
        $lowestPrice = $product->priceHistories()->min('price');
        $this->targetPrice = $lowestPrice ? round($lowestPrice * 0.9, 2) : 0;
    }
    
    public function toggleAlert()
    {
        $this->showAlert = !$this->showAlert;
    }
    
    public function createAlert()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $this->validate([
            'targetPrice' => 'required|numeric|min:0'
        ]);
        
        PriceAlert::create([
            'user_id' => Auth::id(),
            'product_id' => $this->product->id,
            'target_price' => $this->targetPrice,
            'is_active' => true
        ]);
        
        $this->showAlert = false;
        $this->emit('alertCreated');
        
        session()->flash('message', 'Alerta creada correctamente');
    }
    
    public function render()
    {
        // Obtenemos el precio actual y el precio más bajo histórico
        $currentPrice = $this->product->priceHistories()
            ->orderBy('price_date', 'desc')
            ->first();
            
        $lowestPrice = $this->product->priceHistories()
            ->orderBy('price', 'asc')
            ->first();
            
        // Calculamos la tendencia de precio (subiendo, bajando o estable)
        $previousPrice = $this->product->priceHistories()
            ->orderBy('price_date', 'desc')
            ->skip(1)
            ->take(1)
            ->first();
            
        $priceTrend = 'stable';
        if ($previousPrice && $currentPrice) {
            if ($currentPrice->price < $previousPrice->price) {
                $priceTrend = 'down';
            } elseif ($currentPrice->price > $previousPrice->price) {
                $priceTrend = 'up';
            }
        }
        
        return view('livewire.product-card', [
            'currentPrice' => $currentPrice,
            'lowestPrice' => $lowestPrice,
            'priceTrend' => $priceTrend
        ]);
    }
}