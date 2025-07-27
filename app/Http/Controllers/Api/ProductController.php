<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PizzaSize;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();
        
        // Incluir relaciones
        $query->with(['category', 'pizza.ingredients']);
        
        // Filtrar por categoría
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filtrar por disponibilidad
        if ($request->has('available')) {
            $query->where('available', $request->boolean('available'));
        }
        
        // Buscar por nombre
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Verificar si se solicita todo sin paginación
        if ($request->boolean('all')) {
            $products = $query->get();
            return response()->json([
                'data' => $products,
                'total' => $products->count(),
            ]);
        }
        
        // Paginación por defecto
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);
        
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'available' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ]);
        
        $product = Product::create($validated);
        $product->load(['category', 'pizza.ingredients']);
        
        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'pizza.ingredients']);
        
        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'available' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ]);
        
        $product->update($validated);
        $product->load(['category', 'pizza.ingredients']);
        
        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        
        return response()->json([
            'message' => 'Producto eliminado exitosamente',
        ]);
    }

    /**
     * Obtener precios calculados para una pizza específica
     */
    public function pricing(Product $product, Request $request): JsonResponse
    {
        $sizeId = $request->input('size_id');
        
        if (!$sizeId) {
            return response()->json([
                'message' => 'Se requiere size_id para calcular el precio',
            ], 422);
        }
        
        $size = PizzaSize::find($sizeId);
        
        if (!$size) {
            return response()->json([
                'message' => 'Tamaño de pizza no encontrado',
            ], 404);
        }
        
        $finalPrice = $product->base_price * $size->price_multiplier;
        
        return response()->json([
            'data' => [
                'product' => $product->load(['category', 'pizza.ingredients']),
                'size' => $size,
                'base_price' => $product->base_price,
                'multiplier' => $size->price_multiplier,
                'final_price' => round($finalPrice, 2),
            ],
        ]);
    }
}
