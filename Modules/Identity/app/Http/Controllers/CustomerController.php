<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        return view('identity::customers.index');
    }

    public function dataTable(Request $request)
    {
        $query = Customer::with('store')->when(
            CurrentStore::id() !== null,
            fn ($q) => $q->where('store_id', CurrentStore::id())
        )->latest();

        return DataTables::of($query)
            ->addColumn('full_name', fn (Customer $customer) => e($customer->name))
            ->addColumn('store_name', fn (Customer $customer) => e($customer->store?->name ?? '—'))
            ->editColumn('status', fn (Customer $customer) => ucfirst($customer->status))
            ->editColumn('last_login_at', fn (Customer $customer) => $customer->last_login_at?->format('d M Y H:i') ?? 'Never')
            ->editColumn('created_at', fn (Customer $customer) => $customer->created_at->format('d M Y H:i'))
            ->rawColumns([])
            ->make(true);
    }
}
