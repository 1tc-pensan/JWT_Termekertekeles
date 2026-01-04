<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Reviews::with(['user', 'product'])->get();
        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Reviews::create($validated);
        return response()->json($review->load(['user', 'product']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Reviews::with(['user', 'product'])->findOrFail($id);
        return response()->json($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = Reviews::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($validated);
        return response()->json($review->load(['user', 'product']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Reviews::findOrFail($id);
        $review->delete();
        return response()->json(['message' => 'Review soft deleted successfully'], 200);
    }

    /**
     * Display a listing of trashed reviews.
     */
    public function trashed()
    {
        $reviews = Reviews::onlyTrashed()->with(['user', 'product'])->get();
        return response()->json($reviews);
    }

    /**
     * Restore a soft deleted review.
     */
    public function restore(string $id)
    {
        $review = Reviews::withTrashed()->findOrFail($id);
        $review->restore();
        return response()->json(['message' => 'Review restored successfully', 'review' => $review->load(['user', 'product'])], 200);
    }

    /**
     * Permanently delete a review.
     */
    public function forceDestroy(string $id)
    {
        $review = Reviews::withTrashed()->findOrFail($id);
        $review->forceDelete();
        return response()->json(['message' => 'Review permanently deleted'], 200);
    }
}
