<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Support\ItemCodeGenerator;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->with('category');

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . $search . '%']);
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('sort') && $request->has('order')) {
            $sortField = $request->sort;
            $sortDirection = $request->order;

            $allowedSorts = ['name', 'sku', 'price', 'stock_quantity', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        if ($request->has('all') && $request->boolean('all')) {
            $items = $query->get();
            return response()->json(['data' => $items]);
        }

        $items = $query->paginate(10);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|uuid|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'metadata' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['sku'] = ItemCodeGenerator::generate($validated['category_id']);

        $item = Item::create($validated);
        $item->load('category');

        return response()->json($item, 201);
    }

    public function show(Item $item)
    {
        $item->load('category');
        return response()->json($item);
    }

    public function update(Request $request, Item $item)
    {
        if ($request->has('category_id') && $request->category_id !== $item->category_id) {
            return response()->json([
                'message' => 'Kategori tidak bisa diubah setelah item dibuat.'
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'metadata' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $item->update($validated);
        $item->load('category');

        return response()->json($item);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus.'
        ]);
    }
}
