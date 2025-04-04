<!-- resources/views/products/show.blade.php -->
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

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="md:flex">
            <!-- Imagen del producto -->
            <div class="md:w-1/3 p-4">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-auto object-contain">
                @else
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Información del producto -->
            <div class="md:w-2/3 p-6">
                <div class="mb-2">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ $product->category->name }}</span>
                </div>
                
                <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>
                
                <div class="text-gray-600 mb-4">
                    <span class="font-medium">{{ $product->brand }}</span>
                    @if($product->model)
                        <span class="mx-1">·</span>
                        <span>{{ $product->model }}</span>
                    @endif
                </div>
                
                @if($product->description)
                    <div class="text-gray-700 mb-6">
                        {{ $product->description }}
                    </div>
                @endif
                
                <!-- Precios actuales por retailer -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-3">Precios actuales</h2>
                    
                    <div class="space-y-3">
                        @forelse($product->priceHistories->unique('retailer_id') as $price)
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center">
                                    @if($price->retailer->logo_url)
                                        <img src="{{ $price->retailer->logo_url }}" alt="{{ $price->retailer->name }}" class="h-6 mr-2">
                                    @endif
                                    <span class="font-medium">{{ $price->retailer->name }}</span>
                                </div>
                                
                                <div class="flex items-center">
                                    <span class="text-xl font-bold">${{ number_format($price->price, 2) }}</span>
                                    <a href="{{ $price->product_url }}" target="_blank" class="ml-2 text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                        Ver tienda
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-500 italic">No hay precios disponibles actualmente</div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Crear alerta de precio -->
                @auth
                    <form action="{{ route('alerts.store') }}" method="POST" class="mt-4 border-t pt-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        <div class="flex items-end">
                            <div class="w-1/2">
                                <label for="target_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Crear alerta de precio
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2">$</span>
                                    <input type="number" id="target_price" name="target_price" step="0.01" min="0" 
                                           class="pl-7 w-full border-gray-300 rounded-md" 
                                           value="{{ $product->priceHistories->min('price') * 0.9 }}">
                                </div>
                            </div>
                            
                            <button type="submit" class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                                Crear Alerta
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-4 border-t pt-4">
                        <p class="text-gray-600">
                            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Inicia sesión</a> 
                            para crear alertas de precio
                        </p>
                    </div>
                @endauth
            </div>
        </div>
        
        <!-- Gráfico de historial de precios -->
        <div class="p-6 border-t">
            <h2 class="text-xl font-semibold mb-4">Historial de precios</h2>
            
            <div class="w-full h-80">
                <canvas id="priceChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('priceChart').getContext('2d');
        
        const labels = {!! $chartLabels !!};
        const data = {!! $chartData !!};
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Precio',
                    data: data,
                    fill: false,
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1,
                    borderWidth: 2,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection