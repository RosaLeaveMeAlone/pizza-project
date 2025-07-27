<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\PizzaSize;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query();
        
        // Incluir relaciones
        $query->with(['cart', 'products']);
        
        // Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Buscar por nombre del cliente
        if ($request->has('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }
        
        // Ordenar por fecha más reciente
        $query->orderBy('created_at', 'desc');
        
        // Verificar si se solicita todo sin paginación
        if ($request->boolean('all')) {
            $orders = $query->get();
            return response()->json([
                'data' => $orders,
                'total' => $orders->count(),
            ]);
        }
        
        // Paginación por defecto
        $perPage = $request->input('per_page', 15);
        $orders = $query->paginate($perPage);
        
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage (Finalizar carrito)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'payment_method' => 'required|string|max:50',
        ]);
        
        $cart = Cart::where('token', $validated['cart_token'])->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Carrito no encontrado',
            ], 404);
        }
        
        $cart->load('products');
        
        if ($cart->products->isEmpty()) {
            return response()->json([
                'message' => 'El carrito está vacío',
            ], 422);
        }
        
        // Calcular total del pedido
        $total = 0;
        $orderProducts = [];
        
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
            
            $orderProducts[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'pizza_size_id' => $pizzaSizeId,
                'unit_price' => round($price, 2),
            ];
        }
        
        // Crear la orden
        $order = Order::create([
            'cart_id' => $cart->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'total' => round($total, 2),
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);
        
        // Asociar productos al pedido
        foreach ($orderProducts as $orderProduct) {
            $order->products()->attach($orderProduct['product_id'], [
                'quantity' => $orderProduct['quantity'],
                'pizza_size_id' => $orderProduct['pizza_size_id'],
                'unit_price' => $orderProduct['unit_price'],
            ]);
        }
        
        // Vaciar el carrito después de crear la orden
        $cart->products()->detach();
        
        $order->load(['products', 'cart']);
        
        return response()->json([
            'message' => 'Pedido creado exitosamente',
            'data' => $order,
            'view_url' => url('/order/' . $order->id),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['cart', 'products.category', 'products.pizza.ingredients']);
        
        // Construir items del pedido con detalles
        $items = [];
        foreach ($order->products as $product) {
            $pizzaSizeId = $product->pivot->pizza_size_id;
            $items[] = [
                'product' => $product,
                'quantity' => $product->pivot->quantity,
                'pizza_size' => $pizzaSizeId ? PizzaSize::find($pizzaSizeId) : null,
                'unit_price' => $product->pivot->unit_price,
                'subtotal' => $product->pivot->unit_price * $product->pivot->quantity,
            ];
        }
        
        return response()->json([
            'data' => [
                'order' => $order,
                'items' => $items,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,cancelled',
        ]);
        
        $order->update($validated);
        
        return response()->json([
            'message' => 'Estado del pedido actualizado exitosamente',
            'data' => $order,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        
        return response()->json([
            'message' => 'Pedido eliminado exitosamente',
        ]);
    }

    /**
     * Actualizar solo el estado del pedido
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,cancelled',
        ]);
        
        $order->update(['status' => $validated['status']]);
        
        return response()->json([
            'message' => 'Estado del pedido actualizado exitosamente',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'updated_at' => $order->updated_at,
            ],
        ]);
    }

    /**
     * Obtener estadísticas de pedidos
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::whereIn('status', ['delivered'])->sum('total'),
            'average_order_value' => Order::avg('total'),
        ];
        
        return response()->json([
            'data' => $stats,
        ]);
    }
}
