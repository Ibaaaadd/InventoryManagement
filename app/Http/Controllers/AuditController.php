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
            ->get()
            ->map(function ($audit) {
                $changes = [];
                foreach ($audit->getModified() as $field => $values) {
                    $changes[] = [
                        'field' => $field,
                        'old_value' => $values['old'] ?? null,
                        'new_value' => $values['new'] ?? null,
                    ];
                }

                return [
                    'id' => $audit->id,
                    'event' => $audit->event,
                    'user_name' => $audit->user?->name ?? 'System',
                    'changes' => $changes,
                    'created_at' => $audit->created_at,
                ];
            });

        return response()->json($audits);
    }
}
