<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Display all categories
    public function index()
    {
        return response()->json([
            'data' => Category::latest()->paginate(10)
        ]);
    }

    // Create a category (admin only)
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can create categories.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    // Display one category
    public function show(Category $category)
    {
        return response()->json([
            'data' => $category
        ]);
    }

    // Update a category (admin only)
    public function update(Request $request, Category $category)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can update categories.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category
        ]);
    }

    // Delete a category (admin only)
    public function destroy(Request $request, Category $category)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can delete categories.'
            ], 403);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }
}