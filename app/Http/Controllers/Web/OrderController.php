<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PizzaSize;

class OrderController extends Controller
{
    /**
     * Mostrar una orden específica por ID
     */
    public function show($id)
    {
        $order = Order::with(['cart', 'products.category', 'products.pizza.ingredients'])
            ->findOrFail($id);
        
        // Construir items del pedido con detalles
        $items = [];
        foreach ($order->products as $product) {
            $pizzaSizeId = $product->pivot->pizza_size_id;
            $pizzaSize = $pizzaSizeId ? PizzaSize::find($pizzaSizeId) : null;
            
            $items[] = [
                'product' => $product,
                'quantity' => $product->pivot->quantity,
                'pizza_size' => $pizzaSize,
                'unit_price' => $product->pivot->unit_price,
                'subtotal' => $product->pivot->unit_price * $product->pivot->quantity,
            ];
        }
        
        return view('orders.show', compact('order', 'items'));
    }
}