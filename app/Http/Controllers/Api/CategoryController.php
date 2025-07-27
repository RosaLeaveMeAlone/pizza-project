<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();
        
        // Buscar por nombre
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Incluir conteo de productos
        $query->withCount('products');
        
        // Verificar si se solicita todo sin paginación
        if ($request->boolean('all')) {
            $categories = $query->get();
            return response()->json([
                'data' => $categories,
                'total' => $categories->count(),
            ]);
        }
        
        // Paginación por defecto
        $perPage = $request->input('per_page', 15);
        $categories = $query->paginate($perPage);
        
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);
        
        $category = Category::create($validated);
        
        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load('products');
        
        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);
        
        $category->update($validated);
        
        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Verificar si tiene productos asociados
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados',
            ], 422);
        }
        
        $category->delete();
        
        return response()->json([
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }
}
