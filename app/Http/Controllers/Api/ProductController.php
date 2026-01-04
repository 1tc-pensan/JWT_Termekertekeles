<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::all();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Products::create($validated);
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Products::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth('api')->user() || !auth('api')->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product soft deleted successfully'], 200);
    }

    /**
     * Display a listing of trashed products.
     */
    public function trashed()
    {
        $products = Products::onlyTrashed()->get();
        return response()->json($products);
    }

    /**
     * Restore a soft deleted product.
     */
    public function restore(string $id)
    {
        $product = Products::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json(['message' => 'Product restored successfully', 'product' => $product], 200);
    }

    /**
     * Permanently delete a product.
     */
    public function forceDestroy(string $id)
    {
        $product = Products::withTrashed()->findOrFail($id);
        $product->forceDelete();
        return response()->json(['message' => 'Product permanently deleted'], 200);
    }
}
