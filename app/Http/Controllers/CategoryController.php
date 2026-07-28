<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->withCount('items');

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(code) LIKE ?', ['%' . $search . '%']);
            });
        }

        if ($request->has('sort') && $request->has('order')) {
            $sortField = $request->sort;
            $sortDirection = $request->order;

            $allowedSorts = ['name', 'code', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            } elseif ($sortField === 'items_count') {
                $query->orderBy('items_count', $sortDirection);
            }
        }

        $categories = $query->paginate(10);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('categories', 'name')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'regex:/^[A-Z0-9]+$/', Rule::unique('categories', 'code')->whereNull('deleted_at')],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        $category->loadCount('items');
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('categories', 'name')->ignore($category->id)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'regex:/^[A-Z0-9]+$/', Rule::unique('categories', 'code')->ignore($category->id)->whereNull('deleted_at')],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        if ($category->items()->count() > 0 && $validated['code'] !== $category->code) {
            return response()->json([
                'message' => 'Kode kategori tidak bisa diubah karena sudah digunakan oleh item.'
            ], 422);
        }

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->items()->count() > 0) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh item.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.'
        ]);
    }
}
