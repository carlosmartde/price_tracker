<!-- resources/views/products/search_results.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Volver a productos
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Resultados de búsqueda</h1>
        <p class="text-gray-600">Resultados para: <strong>{{ request('query') }}</strong></p>
    </div>
    
    @if(count($results) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($results as $result)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="relative h-48 overflow-hidden">
                        @if(!empty($result['image']))
                            <img src="{{ $result['image'] }}" alt="{{ $result['name'] }}" class="w-full h-full object-cover object-center">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="absolute bottom-0 right-0 bg-white px-3 py-1 rounded-tl-lg shadow">
                            <span class="font-bold text-lg">${{ number_format($result['salePrice'], 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold line-clamp-2 mb-2">{{ $result['name'] }}</h3>
                        <div class="text-sm text-gray-600 mb-4">
                            <span class="font-medium">{{ $result['manufacturer'] }}</span>
                            @if(!empty($result['modelNumber']))
                                <span class="mx-1">·</span>
                                <span>{{ $result['modelNumber'] }}</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between mt-4">
                            <a href="{{ $result['url'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver en tienda
                            </a>
                            
                            <form action="{{ route('products.save-from-api') }}" method="POST">
                                @csrf
                                <input type="hidden" name="api_product" value="{{ json_encode($result) }}">
                                <input type="hidden" name="category_id" value="{{ $category }}">
                                <button type="submit" class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                    Guardar producto
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12a3 3 0 110-6 3 3 0 010 6z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No se encontraron productos</h3>
            <p class="text-gray-500">Intenta con otros términos de búsqueda o categorías.</p>
        </div>
    @endif
</div>
@endsection