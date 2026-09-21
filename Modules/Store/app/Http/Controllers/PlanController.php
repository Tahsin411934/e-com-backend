<?php

namespace Modules\Store\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\Store\Models\Plan;
use Modules\Store\Models\Feature;
use Modules\Store\Http\Requests\PlanRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PlanController extends Controller
{
    public function index()
    {
        return view('store::plans.index', ['features' => Feature::where('is_active', true)->orderBy('name')->get()]);
    }

    public function dataTable(Request $request)
    {
        return DataTables::of(Plan::query()->orderBy('price')->orderBy('id'))
            ->editColumn('price', fn (Plan $plan) => $plan->is_free ? 'Free' : number_format((float) $plan->price, 2).' '.$plan->currency)
            ->editColumn('duration_days', fn (Plan $plan) => $plan->duration_days ? $plan->duration_days.' days' : 'No expiry')
            ->editColumn('product_limit', fn (Plan $plan) => $plan->product_limit ?? 'Unlimited')
            ->editColumn('is_active', fn (Plan $plan) => $plan->is_active ? '<span class="text-green-600 font-medium">Active</span>' : '<span class="text-gray-500">Inactive</span>')
            ->addColumn('action', fn (Plan $plan) => view('components.action-buttons', ['permission'=>'plans','entityLabel'=>'Plan','id'=>$plan->id,'edit'=>'planEdit','delete'=>'planDelete'])->render())
            ->rawColumns(['is_active','action'])->make(true);
    }

    public function store(PlanRequest $request) { return $this->save($request->validated()); }
    public function show(Plan $plan) { return ApiResponse::success($plan->load('features')); }
    public function update(PlanRequest $request, Plan $plan) { return $this->save($request->validated(), $plan); }
    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists()) return ApiResponse::error('This plan has subscription history and cannot be deleted. Deactivate it instead.', 422);
        $plan->delete(); return ApiResponse::success(null, 'Plan deleted successfully.');
    }

    private function save(array $data, ?Plan $plan = null)
    {
        try {
            $featureIds = $data['feature_ids'] ?? [];
            unset($data['feature_ids']);
            if (isset($data['features'])) $data['features'] = collect(preg_split('/[\r\n,]+/', $data['features']))->map(fn($item)=>trim($item))->filter()->values()->all();
            $data['currency'] = strtoupper($data['currency']);
            foreach (['is_free','is_public','is_active'] as $flag) $data[$flag] = (bool)($data[$flag] ?? false);
            $plan ? $plan->update($data) : $plan = Plan::create($data);
            $plan->features()->sync(collect($featureIds)->mapWithKeys(fn($id) => [$id => ['enabled' => true]])->all());
            return ApiResponse::success($plan->fresh(), $plan->wasRecentlyCreated ? 'Plan created successfully.' : 'Plan updated successfully.');
        } catch (\Throwable $e) { report($e); return ApiResponse::error('Unable to save plan.', 500); }
    }

    public function publicIndex()
    {
        return ApiResponse::success(Plan::with('features')->where('is_active', true)->where('is_public', true)->orderBy('price')->get());
    }
}
