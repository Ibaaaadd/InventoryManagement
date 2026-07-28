<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function overview(Request $request)
    {
        $userId = auth()->id();
        
        $summary = [
            'total_items' => Item::count(),
            'pending_mutations' => StockMutation::where('status', 'pending')->count(),
            'low_stock_items' => Item::where('stock_quantity', '<=', Item::LOW_STOCK_THRESHOLD)
                ->where('is_active', true)
                ->count(),
            'total_users' => User::count(),
        ];

        $mutationStatusBreakdown = [
            'pending' => StockMutation::where('status', 'pending')->count(),
            'approved' => StockMutation::where('status', 'approved')->count(),
            'rejected' => StockMutation::where('status', 'rejected')->count(),
        ];

        $lowStockItems = Item::where('stock_quantity', '<=', Item::LOW_STOCK_THRESHOLD)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'stock_quantity' => $item->stock_quantity,
                    'category_name' => $item->category->name ?? '-',
                ];
            });

        $pendingApprovalsForMe = StockMutation::where('status', 'pending')
            ->whereHas('user', function ($query) use ($userId) {
                $query->where('approver_id', $userId);
            })
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($mutation) {
                return [
                    'id' => $mutation->id,
                    'item_name_snapshot' => $mutation->item_name_snapshot,
                    'type' => $mutation->type,
                    'quantity' => $mutation->quantity,
                    'user_name' => $mutation->user->name ?? '-',
                    'created_at' => $mutation->created_at,
                ];
            });

        return response()->json([
            'summary' => $summary,
            'mutation_status_breakdown' => $mutationStatusBreakdown,
            'low_stock_items' => $lowStockItems,
            'pending_approvals_for_me' => $pendingApprovalsForMe,
        ]);
    }

    public function mutationTrend(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
        ]);

        $month = $validated['month'] ?? now()->month;
        $year = $validated['year'] ?? now()->year;

        $mutations = StockMutation::where('status', 'approved')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                'type',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;

        $labels = [];
        $fullDates = [];
        $stockInData = [];
        $stockOutData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::createFromDate($year, $month, $day);
            $dateStr = $date->format('Y-m-d');
            
            $labels[] = (string) $day;
            $fullDates[] = $dateStr;

            $stockIn = $mutations->where('date', $dateStr)->where('type', 'in')->first();
            $stockOut = $mutations->where('date', $dateStr)->where('type', 'out')->first();

            $stockInData[] = $stockIn ? (int) $stockIn->total_quantity : 0;
            $stockOutData[] = $stockOut ? (int) $stockOut->total_quantity : 0;
        }

        return response()->json([
            'labels' => $labels,
            'full_dates' => $fullDates,
            'stock_in' => $stockInData,
            'stock_out' => $stockOutData,
        ]);
    }

    public function mutationYears(Request $request)
    {
        $years = StockMutation::selectRaw('DISTINCT YEAR(transaction_date) as year')
            ->whereNotNull('transaction_date')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        return response()->json($years);
    }
}
