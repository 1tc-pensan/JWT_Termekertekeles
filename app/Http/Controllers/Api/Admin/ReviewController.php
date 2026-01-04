<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * ADMIN ONLY
     * GET /admin/reviews
     * Összes értékelés listázása.
     */
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $reviews = Reviews::with(['user', 'product'])->get()->map(function ($review) {
            return [
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                ],
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'email' => $review->user->email,
                ],
                'product' => [
                    'id' => $review->product->id,
                    'name' => $review->product->name,
                    'price' => $review->product->price,
                ]
            ];
        });

        return response()->json([
            'data' => $reviews
        ]);
    }

    /**
     * ADMIN ONLY
     * POST /admin/reviews
     * Új értékelés létrehozása.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Reviews::create($validated);
        
        return response()->json([
            'message' => 'Review created successfully',
            'review' => [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]
        ], 201);
    }

    /**
     * ADMIN ONLY
     * GET /admin/reviews/{id}
     * Értékelés lekérése ID alapján.
     */
    public function show(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review = Reviews::with(['user', 'product'])->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json([
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
            ],
            'user' => [
                'id' => $review->user->id,
                'name' => $review->user->name,
                'email' => $review->user->email,
            ],
            'product' => [
                'id' => $review->product->id,
                'name' => $review->product->name,
                'price' => $review->product->price,
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * PUT/PATCH /admin/reviews/{id}
     * Értékelés módosítása.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review = Reviews::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($validated);
        
        return response()->json([
            'message' => 'Review updated successfully',
            'review' => [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * DELETE /admin/reviews/{id}
     * Értékelés törlése.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review = Reviews::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    /**
     * ADMIN ONLY
     * GET /admin/reviews/trashed
     * Törölt értékelések listázása.
     */
    public function trashed(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $reviews = Reviews::onlyTrashed()->with(['user', 'product'])->get();
        return response()->json(['data' => $reviews]);
    }

    /**
     * ADMIN ONLY
     * POST /admin/reviews/{id}/restore
     * Törölt értékelés visszaállítása.
     */
    public function restore(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review = Reviews::withTrashed()->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->restore();
        return response()->json(['message' => 'Review restored successfully', 'review' => $review->load(['user', 'product'])], 200);
    }

    /**
     * ADMIN ONLY
     * DELETE /admin/reviews/{id}/force
     * Értékelés végleges törlése.
     */
    public function forceDestroy(Request $request, $id)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review = Reviews::withTrashed()->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->forceDelete();
        return response()->json(['message' => 'Review permanently deleted'], 200);
    }
}
