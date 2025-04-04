<!-- resources/views/livewire/product-card.blade.php -->
<div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg">
    <!-- Imagen del producto -->
    <div class="relative overflow-hidden h-48">
        @if($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center">
        @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
        
        <!-- Precio actual con indicador de tendencia -->
        @if(isset($currentPrice))
            <div class="absolute bottom-0 right-0 bg-white px-3 py-1 rounded-tl-lg shadow flex items-center">
                <span class="font-bold text-lg">${{ number_format($currentPrice->price, 2) }}</span>
                
                @if($priceTrend == 'down')
                    <svg class="w-5 h-5 ml-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                @elseif($priceTrend == 'up')
                    <svg class="w-5 h-5 ml-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                @endif
            </div>
        @endif
    </div>
    
    <div class="p-4">
        <!-- Información del producto -->
        <h3 class="text-lg font-semibold line-clamp-2 mb-1">{{ $product->name }}</h3>
        <div class="text-sm text-gray-600 mb-3">
            <span class="font-medium">{{ $product->brand }}</span>
            @if($product->model)
                <span class="mx-1">·</span>
                <span>{{ $product->model }}</span>
            @endif
        </div>
        
        <!-- Precio más bajo histórico -->
        @if(isset($lowestPrice) && $lowestPrice->id != ($currentPrice->id ?? null))
            <div class="text-sm text-gray-600 mb-3">
                <span>Precio más bajo:</span>
                <span class="font-semibold text-green-600">${{ number_format($lowestPrice->price, 2) }}</span>
                <span class="text-xs">({{ $lowestPrice->price_date->format('d/m/Y') }})</span>
            </div>
        @endif
        
        <!-- Enlaces de acción -->
        <div class="flex justify-between items-center mt-4">
            <a href="{{ route('products.show', $product->slug) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Ver detalles
            </a>
            
            <button wire:click="toggleAlert" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                Crear alerta
            </button>
        </div>
        
        <!-- Modal de alerta de precio -->
        @if($showAlert)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="toggleAlert">
                <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
                    <h3 class="text-lg font-bold mb-4">Crear alerta de precio</h3>
                    <p class="text-sm text-gray-600 mb-4">Recibirás una notificación cuando el precio baje del valor indicado:</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio objetivo</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2">$</span>
                            <input type="number" step="0.01" min="0" wire:model="targetPrice" class="pl-7 w-full border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button wire:click="toggleAlert" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button wire:click="createAlert" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Crear Alerta
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>