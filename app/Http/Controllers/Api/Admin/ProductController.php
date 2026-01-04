<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * ADMIN ONLY
     * GET /admin/products
     * Összes termék listázása.
     */
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $products = Products::all()->map(function ($product) {
            return [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                ],
                'stats' => [
                    'totalReviews' => $product->reviews()->count(),
                    'averageRating' => round($product->reviews()->avg('rating') ?? 0, 2),
                ]
            ];
        });

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * ADMIN ONLY
     * POST /admin/products
     * Új termék létrehozása.
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
        
        return response()->json([
            'message' => 'Product created successfully',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            ]
        ], 201);
    }

    /**
     * ADMIN ONLY
     * GET /admin/products/{id}
     * Termék lekérése ID alapján.
     */
    public function show(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            ],
            'stats' => [
                'totalReviews' => $product->reviews()->count(),
                'averageRating' => round($product->reviews()->avg('rating') ?? 0, 2),
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * PUT/PATCH /admin/products/{id}
     * Termék módosítása.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $product->update($validated);
        
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * DELETE /admin/products/{id}
     * Termék törlése.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * ADMIN ONLY
     * GET /admin/products/trashed
     * Törölt termékek listázása.
     */
    public function trashed(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $products = Products::onlyTrashed()->get();
        return response()->json(['data' => $products]);
    }

    /**
     * ADMIN ONLY
     * POST /admin/products/{id}/restore
     * Törölt termék visszaállítása.
     */
    public function restore(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::withTrashed()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->restore();
        return response()->json(['message' => 'Product restored successfully', 'product' => $product], 200);
    }

    /**
     * ADMIN ONLY
     * DELETE /admin/products/{id}/force
     * Termék végleges törlése.
     */
    public function forceDestroy(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Products::withTrashed()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->forceDelete();
        return response()->json(['message' => 'Product permanently deleted'], 200);
    }
}
