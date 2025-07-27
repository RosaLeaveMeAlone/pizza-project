<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PizzaSizeController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas públicas (sin autenticación)
Route::prefix('v1')->group(function () {
    
    // Categorías
    Route::apiResource('categories', CategoryController::class);
    
    // Productos
    Route::apiResource('products', ProductController::class);
    Route::get('products/{product}/pricing', [ProductController::class, 'pricing']);
    
    // Tamaños de pizza
    Route::apiResource('pizza-sizes', PizzaSizeController::class);
    
    // Carrito
    Route::prefix('cart')->group(function () {
        Route::post('create', [CartController::class, 'create']);
        Route::get('{token}', [CartController::class, 'show']);
        Route::post('add-product', [CartController::class, 'addProduct']);
        Route::put('update-item', [CartController::class, 'updateItem']);
        Route::delete('remove-item', [CartController::class, 'removeItem']);
        Route::delete('{token}/clear', [CartController::class, 'clear']);
    });
    
    // Pedidos
    Route::apiResource('orders', OrderController::class);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('orders/stats/summary', [OrderController::class, 'stats']);
    
    // Endpoint especial para IA - Catálogo completo
    Route::get('ai/catalog', function () {
        return response()->json([
            'data' => [
                'categories' => \App\Models\Category::with('products')->get()->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'products' => $category->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'description' => $product->description,
                                'base_price' => $product->base_price,
                                'available' => $product->available,
                            ];
                        }),
                    ];
                }),
                'products' => \App\Models\Product::with(['category', 'pizza.ingredients'])->get()->map(function ($product) {
                    $data = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price,
                        'available' => $product->available,
                        'category' => $product->category ? $product->category->name : null,
                    ];
                    
                    if ($product->pizza) {
                        $data['ingredients'] = $product->pizza->ingredients->map(function ($ingredient) {
                            return $ingredient->name;
                        });
                        
                        // Calcular precios por tamaño para pizzas
                        $pizzaSizes = \App\Models\PizzaSize::all();
                        $data['prices_by_size'] = [];
                        
                        foreach ($pizzaSizes as $size) {
                            $finalPrice = $product->base_price * $size->price_multiplier;
                            $data['prices_by_size'][$size->name] = [
                                'id' => $size->id,
                                'price' => round($finalPrice, 2),
                                'description' => $size->description,
                            ];
                        }
                    }
                    
                    return $data;
                }),
                'pizza_sizes' => \App\Models\PizzaSize::all()->map(function ($size) {
                    return [
                        'id' => $size->id,
                        'name' => $size->name,
                        'description' => $size->description,
                        'price_multiplier' => $size->price_multiplier,
                    ];
                }),
                'ingredients' => \App\Models\Ingredient::with('pizzas.product')->get()->map(function ($ingredient) {
                    return [
                        'name' => $ingredient->name,
                        'used_in_pizzas' => $ingredient->pizzas->map(function ($pizza) {
                            return $pizza->product->name;
                        }),
                    ];
                }),
            ],
        ]);
    });
    
    // Endpoint de salud de la API
    Route::get('health', function () {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now(),
            'version' => '1.0.0',
        ]);
    });
});
