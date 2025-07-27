<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $order->id }} - Pizza Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">Orden #{{ $order->id }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status === 'preparing') bg-orange-100 text-orange-800
                        @elseif($order->status === 'ready') bg-green-100 text-green-800
                        @elseif($order->status === 'delivered') bg-gray-100 text-gray-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-gray-600 mt-2">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Cliente</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Nombre:</label>
                            <p class="text-gray-900">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Método de Pago:</label>
                            <p class="text-gray-900">{{ $order->payment_method }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Productos Ordenados</h2>
                    <div class="space-y-4">
                        @foreach($items as $item)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900">{{ $item['product']->name }}</h3>
                                        @if($item['product']->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $item['product']->description }}</p>
                                        @endif
                                        
                                        @if($item['pizza_size'])
                                            <p class="text-sm text-blue-600 mt-1">
                                                Tamaño: {{ $item['pizza_size']->name }} ({{ $item['pizza_size']->description }})
                                            </p>
                                        @endif
                                        
                                        @if($item['product']->pizza && $item['product']->pizza->ingredients->count() > 0)
                                            <p class="text-sm text-gray-600 mt-1">
                                                <strong>Ingredientes:</strong> {{ $item['product']->pizza->ingredients->pluck('name')->join(', ') }}
                                            </p>
                                        @endif
                                        
                                        <p class="text-sm text-gray-600 mt-1">
                                            Cantidad: {{ $item['quantity'] }} × ${{ number_format($item['unit_price'], 2) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">${{ number_format($item['subtotal'], 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Total -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <p class="text-lg font-semibold text-gray-900">Total:</p>
                            <p class="text-2xl font-bold text-green-600">${{ number_format($order->total, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>