<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Models\StoreDomain;
use Yajra\DataTables\DataTables;

class StoreDomainAdminController extends Controller
{
    public function index()
    {
        return view('store::domains.index');
    }

    public function dataTable(Request $request)
    {
        return DataTables::of(StoreDomain::query()->with('store')->latest())
            ->addColumn('store_name', fn (StoreDomain $domain) => e($domain->store?->name ?? '—'))
            ->editColumn('type', fn (StoreDomain $domain) => ucfirst($domain->type))
            ->editColumn('is_primary', fn (StoreDomain $domain) => $domain->is_primary ? 'Yes' : 'No')
            ->editColumn('ssl_status', fn (StoreDomain $domain) => ucfirst($domain->ssl_status))
            ->editColumn('verified_at', fn (StoreDomain $domain) => $domain->verified_at?->format('d M Y H:i') ?? 'Pending')
            ->editColumn('created_at', fn (StoreDomain $domain) => $domain->created_at->format('d M Y H:i'))
            ->rawColumns([])
            ->make(true);
    }
}
