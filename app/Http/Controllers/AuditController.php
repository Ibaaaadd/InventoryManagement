<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::query();

        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->has('auditable_id')) {
            $query->where('auditable_id', $request->auditable_id);
        }

        $audits = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 5);

        return response()->json($audits);
    }
}
