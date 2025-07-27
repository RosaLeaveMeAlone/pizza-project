<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PizzaSize;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PizzaSizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PizzaSize::query();
        
        // Buscar por nombre
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Ordenar por multiplicador de precio
        $query->orderBy('price_multiplier');
        
        // Verificar si se solicita todo sin paginación
        if ($request->boolean('all')) {
            $sizes = $query->get();
            return response()->json([
                'data' => $sizes,
                'total' => $sizes->count(),
            ]);
        }
        
        // Paginación por defecto
        $perPage = $request->input('per_page', 15);
        $sizes = $query->paginate($perPage);
        
        return response()->json($sizes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_multiplier' => 'required|numeric|min:0.01',
        ]);
        
        $size = PizzaSize::create($validated);
        
        return response()->json([
            'message' => 'Tamaño de pizza creado exitosamente',
            'data' => $size,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PizzaSize $pizzaSize): JsonResponse
    {
        return response()->json([
            'data' => $pizzaSize,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PizzaSize $pizzaSize): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_multiplier' => 'required|numeric|min:0.01',
        ]);
        
        $pizzaSize->update($validated);
        
        return response()->json([
            'message' => 'Tamaño de pizza actualizado exitosamente',
            'data' => $pizzaSize,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PizzaSize $pizzaSize): JsonResponse
    {
        $pizzaSize->delete();
        
        return response()->json([
            'message' => 'Tamaño de pizza eliminado exitosamente',
        ]);
    }
}
