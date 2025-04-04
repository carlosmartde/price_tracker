<!-- resources/views/products/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Explorar Productos</h1>
        <p class="text-gray-600">Encuentra y compara precios para tus productos favoritos</p>
    </div>
    
    <div class="flex flex-col md:flex-row md:space-x-6">
        <!-- Filtros laterales -->
        <div class="md:w-1/4 mb-6 md:mb-0">
            <div class="bg-white rounded-lg shadow-md p-5">
                <h2 class="text-lg font-semibold mb-4">Filtros</h2>
                
                <!-- Búsqueda -->
                <form action="{{ route('products.index') }}" method="GET" class="mb-6">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <div class="flex">
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               class="flex-1 border-gray-300 rounded-l-md">
                        <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-md hover:bg-blue-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
                
                <!-- Categorías -->
                <div>
                    <h3 class="font-medium mb-2">Categorías</h3>
                    <div class="space-y-2">
                        <a href="{{ route('products.index') }}" class="block text-gray-700 hover:text-blue-600 {{ !request('category') ? 'font-semibold text-blue-600' : '' }}">
                            Todas las categorías
                        </a>
                        
                        @foreach($categories as $category)
                            <a href="{{ route('products.index', ['category' => $category->id]) }}" 
                               class="block text-gray-700 hover:text-blue-600 {{ request('category') == $category->id ? 'font-semibold text-blue-600' : '' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Búsqueda externa -->
            <div class="bg-white rounded-lg shadow-md p-5 mt-6">
                <h2 class="text-lg font-semibold mb-4">Buscar nuevos productos</h2>
                <p class="text-sm text-gray-600 mb-4">¿No encuentras lo que buscas? Busca en otras tiendas:</p>
                
                <form action="{{ route('products.search') }}" method="GET">
                    <div class="mb-3">
                        <label for="api_query" class="block text-sm font-medium text-gray-700 mb-1">Término de búsqueda</label>
                        <input type="text" id="api_query" name="query" required class="w-full border-gray-300 rounded-md">
                    </div>
                    
                    <div class="mb-4">
                        <label for="api_category" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select id="api_category" name="category" class="w-full border-gray-300 rounded-md">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        Buscar productos
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Lista de productos -->
        <div class="md:w-3/4">
            @if(request('search'))
                <div class="mb-4 bg-blue-50 border border-blue-200 p-3 rounded-md">
                    <p>Resultados de búsqueda para: <strong>{{ request('search') }}</strong></p>
                </div>
            @endif
            
            @if($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div>
                            @livewire('product-card', ['product' => $product], key($product->id))
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12a3 3 0 110-6 3 3 0 010 6z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No se encontraron productos</h3>
                    <p class="text-gray-500">Intenta con otros filtros o busca nuevos productos utilizando el formulario de búsqueda.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection