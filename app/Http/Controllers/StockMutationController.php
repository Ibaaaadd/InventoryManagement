<?php

namespace App\Http\Controllers;

use App\Models\StockMutation;
use App\Models\StockMutationApproval;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockMutationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = StockMutation::with(['item', 'user', 'approvals']);

        if (!$user->isAdministrator()) {
            $query->where('user_id', $user->id);
            
            if ($request->has('all') && $request->all == '1') {
                $subordinateIds = $user->subordinates()->pluck('id')->toArray();
                if (!empty($subordinateIds)) {
                    $query->orWhereIn('user_id', $subordinateIds);
                }
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(notes) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(item_name_snapshot) LIKE ?', ['%' . $search . '%']);
            });
        }

        $mutations = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($mutations);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->approver_id) {
            return response()->json([
                'message' => 'Anda belum memiliki approver yang ditunjuk. Hubungi administrator untuk mengatur approver Anda terlebih dahulu.'
            ], 422);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'required|file|mimes:pdf|min:100|max:500',
        ]);

        $attachmentPath = Storage::disk('local')->putFile('stock-mutation-attachments', $request->file('attachment'));

        $validated['user_id'] = auth()->id();
        $validated['attachment_path'] = $attachmentPath;

        $mutation = StockMutation::create($validated);
        $mutation->load('item', 'user');

        return response()->json($mutation, 201);
    }

    public function show(StockMutation $stockMutation)
    {
        $stockMutation->load('item', 'user', 'approvals.approver');
        return response()->json($stockMutation);
    }

    public function update(Request $request, StockMutation $stockMutation)
    {
        if ($stockMutation->status !== 'pending') {
            return response()->json([
                'message' => 'Mutation yang sudah diproses tidak bisa diubah.'
            ], 422);
        }

        if ($stockMutation->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Anda hanya bisa mengubah mutation milik sendiri.'
            ], 403);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|min:100|max:500',
        ]);

        if ($request->hasFile('attachment')) {
            if ($stockMutation->attachment_path) {
                Storage::disk('local')->delete($stockMutation->attachment_path);
            }
            $validated['attachment_path'] = Storage::disk('local')->putFile('stock-mutation-attachments', $request->file('attachment'));
        }

        if (isset($validated['item_id']) && $validated['item_id'] !== $stockMutation->item_id) {
            $stockMutation->item_id = $validated['item_id'];
            $stockMutation->refreshSnapshot();
        }

        $stockMutation->update($validated);
        $stockMutation->load('item', 'user');

        return response()->json($stockMutation);
    }

    public function destroy(StockMutation $stockMutation)
    {
        if ($stockMutation->status !== 'pending') {
            return response()->json([
                'message' => 'Mutation yang sudah diproses tidak bisa dihapus.'
            ], 422);
        }

        if ($stockMutation->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Anda hanya bisa menghapus mutation milik sendiri.'
            ], 403);
        }

        if ($stockMutation->attachment_path) {
            Storage::disk('local')->delete($stockMutation->attachment_path);
        }

        $stockMutation->delete();

        return response()->json([
            'message' => 'Stock mutation deleted successfully.'
        ]);
    }

    public function approve(Request $request, StockMutation $stockMutation)
    {
        $stockMutation->load('user');

        if (auth()->id() !== $stockMutation->user->approver_id) {
            return response()->json([
                'message' => 'Anda bukan approver yang ditunjuk untuk user ini.'
            ], 403);
        }

        if (auth()->id() === $stockMutation->user_id) {
            return response()->json([
                'message' => 'Tidak bisa approve mutation milik sendiri.'
            ], 403);
        }

        if ($stockMutation->status !== 'pending') {
            return response()->json([
                'message' => 'Mutation ini sudah diproses sebelumnya.'
            ], 422);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable',
        ]);

        try {
            DB::transaction(function() use ($stockMutation, $validated) {
                $item = Item::where('id', $stockMutation->item_id)->lockForUpdate()->firstOrFail();

                if ($stockMutation->type === 'out' && $item->stock_quantity < $stockMutation->quantity) {
                    throw new \Exception('Stok tidak mencukupi untuk mutation ini.');
                }

                if ($stockMutation->type === 'in') {
                    $item->stock_quantity += $stockMutation->quantity;
                } else {
                    $item->stock_quantity -= $stockMutation->quantity;
                }
                $item->save();

                $stockMutation->status = 'approved';
                $stockMutation->save();

                StockMutationApproval::create([
                    'stock_mutation_id' => $stockMutation->id,
                    'approved_by' => auth()->id(),
                    'decision' => 'approved',
                    'approval_notes' => $validated['approval_notes'] ?? null,
                    'approved_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }

        $stockMutation->load('approvals');
        return response()->json($stockMutation);
    }

    public function reject(Request $request, StockMutation $stockMutation)
    {
        $stockMutation->load('user');

        if (auth()->id() !== $stockMutation->user->approver_id) {
            return response()->json([
                'message' => 'Anda bukan approver yang ditunjuk untuk user ini.'
            ], 403);
        }

        if (auth()->id() === $stockMutation->user_id) {
            return response()->json([
                'message' => 'Tidak bisa reject mutation milik sendiri.'
            ], 403);
        }

        if ($stockMutation->status !== 'pending') {
            return response()->json([
                'message' => 'Mutation ini sudah diproses sebelumnya.'
            ], 422);
        }

        $validated = $request->validate([
            'approval_notes' => 'required',
        ]);

        $stockMutation->status = 'rejected';
        $stockMutation->save();

        StockMutationApproval::create([
            'stock_mutation_id' => $stockMutation->id,
            'approved_by' => auth()->id(),
            'decision' => 'rejected',
            'approval_notes' => $validated['approval_notes'],
            'approved_at' => now(),
        ]);

        $stockMutation->load('approvals');
        return response()->json($stockMutation);
    }

    public function downloadAttachment(StockMutation $stockMutation)
    {
        if (!$stockMutation->attachment_path) {
            return response()->json(['message' => 'No attachment found'], 404);
        }

        if (!Storage::disk('local')->exists($stockMutation->attachment_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('local')->download(
            $stockMutation->attachment_path,
            'mutation-' . $stockMutation->id . '.pdf'
        );
    }

    public function viewAttachment(StockMutation $stockMutation)
    {
        if (!$stockMutation->attachment_path) {
            return response()->json(['message' => 'No attachment found'], 404);
        }

        if (!Storage::disk('local')->exists($stockMutation->attachment_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file(
            Storage::disk('local')->path($stockMutation->attachment_path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
