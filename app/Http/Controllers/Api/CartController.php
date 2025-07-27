<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\PizzaSize;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Crear un nuevo carrito
     */
    public function create(): JsonResponse
    {
        $cart = Cart::create([
            'token' => Str::random(32),
        ]);
        
        return response()->json([
            'message' => 'Carrito creado exitosamente',
            'data' => [
                'cart_token' => $cart->token,
                'cart_id' => $cart->id,
            ],
        ], 201);
    }

    /**
     * Obtener carrito por token
     */
    public function show(string $token): JsonResponse
    {
        $cart = Cart::where('token', $token)->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $cart->load([
            'products' => function ($query) {
                $query->with(['category', 'pizza.ingredients']);
            }
        ]);
        
        // Calcular total del carrito
        $total = 0;
        $items = [];
        
        foreach ($cart->products as $product) {
            $quantity = $product->pivot->quantity;
            $pizzaSizeId = $product->pivot->pizza_size_id;
            
            $price = $product->base_price;
            
            // Si es pizza y tiene tamaño, calcular precio con multiplicador
            if ($pizzaSizeId) {
                $size = PizzaSize::find($pizzaSizeId);
                if ($size) {
                    $price = $product->base_price * $size->price_multiplier;
                }
            }
            
            $subtotal = $price * $quantity;
            $total += $subtotal;
            
            $pizzaSize = $pizzaSizeId ? PizzaSize::find($pizzaSizeId) : null;
            
            $items[] = [
                'product' => [
                    'name' => $product->name,
                    'description' => $product->description,
                    'base_price' => $product->base_price,
                    'available' => $product->available,
                    'category' => $product->category ? $product->category->name : null,
                ],
                'quantity' => $quantity,
                'pizza_size' => $pizzaSize ? [
                    'name' => $pizzaSize->name,
                    'description' => $pizzaSize->description,
                    'price_multiplier' => $pizzaSize->price_multiplier,
                ] : null,
                'unit_price' => round($price, 2),
                'subtotal' => round($subtotal, 2),
            ];
        }
        
        return response()->json([
            'data' => [
                'cart' => [
                    'token' => $cart->token,
                ],
                'items' => $items,
                'total' => round($total, 2),
                'items_count' => count($items),
            ],
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function addProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'pizza_size_id' => 'nullable|exists:pizza_sizes,id',
        ]);
        
        $cart = Cart::where('token', $validated['cart_token'])->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $product = Product::find($validated['product_id']);
        
        if (!$product->available) {
            return response()->json([
                'message' => 'Producto no disponible',
            ], 422);
        }
        
        // Verificar si el producto ya existe en el carrito con el mismo tamaño
        $existingPivot = $cart->products()
            ->where('product_id', $validated['product_id'])
            ->where('pizza_size_id', $validated['pizza_size_id'] ?? null)
            ->first();
        
        if ($existingPivot) {
            // Actualizar cantidad
            $newQuantity = $existingPivot->pivot->quantity + $validated['quantity'];
            $cart->products()->updateExistingPivot($validated['product_id'], [
                'quantity' => $newQuantity,
                'pizza_size_id' => $validated['pizza_size_id'] ?? null,
            ]);
        } else {
            // Agregar nuevo producto
            $cart->products()->attach($validated['product_id'], [
                'quantity' => $validated['quantity'],
                'pizza_size_id' => $validated['pizza_size_id'] ?? null,
            ]);
        }
        
        return response()->json([
            'message' => 'Producto agregado al carrito exitosamente',
        ]);
    }

    /**
     * Actualizar cantidad de un producto en el carrito
     */
    public function updateItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'pizza_size_id' => 'nullable|exists:pizza_sizes,id',
        ]);
        
        $cart = Cart::where('token', $validated['cart_token'])->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $exists = $cart->products()
            ->where('product_id', $validated['product_id'])
            ->where('pizza_size_id', $validated['pizza_size_id'] ?? null)
            ->exists();
        
        if (!$exists) {
            return response()->json([
                'message' => 'Producto no encontrado en el carrito',
            ], 404);
        }
        
        $cart->products()->updateExistingPivot($validated['product_id'], [
            'quantity' => $validated['quantity'],
            'pizza_size_id' => $validated['pizza_size_id'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Cantidad actualizada exitosamente',
        ]);
    }

    /**
     * Eliminar producto del carrito
     */
    public function removeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'pizza_size_id' => 'nullable|exists:pizza_sizes,id',
        ]);
        
        $cart = Cart::where('token', $validated['cart_token'])->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $cart->products()->detach($validated['product_id']);
        
        return response()->json([
            'message' => 'Producto eliminado del carrito exitosamente',
        ]);
    }

    /**
     * Vaciar carrito
     */
    public function clear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => 'required|string',
        ]);
        
        $cart = Cart::where('token', $validated['cart_token'])->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $cart->products()->detach();
        
        return response()->json([
            'message' => 'Carrito vaciado exitosamente',
        ]);
    }
}
