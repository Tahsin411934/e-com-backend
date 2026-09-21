<?php
namespace Modules\Store\Http\Controllers;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\FeatureRequest;
use Modules\Store\Models\Feature;
use Yajra\DataTables\DataTables;
class FeatureController extends Controller
{
    public function index() { return view('store::features.index'); }
    public function dataTable(Request $request)
    {
        return DataTables::of(Feature::query()->latest('id'))
            ->editColumn('type', fn(Feature $feature) => ucfirst($feature->type))
            ->editColumn('is_active', fn(Feature $feature) => $feature->is_active ? '<span class="text-green-600 font-medium">Active</span>' : '<span class="text-gray-500">Inactive</span>')
            ->addColumn('action', fn(Feature $feature) => view('components.action-buttons', ['permission'=>'features','entityLabel'=>'Feature','id'=>$feature->id,'edit'=>'featureEdit','delete'=>'featureDelete'])->render())
            ->rawColumns(['is_active','action'])->make(true);
    }
    public function store(FeatureRequest $request) { return $this->save($request->validated()); }
    public function show(Feature $feature) { return ApiResponse::success($feature); }
    public function update(FeatureRequest $request, Feature $feature) { return $this->save($request->validated(), $feature); }
    public function destroy(Feature $feature)
    {
        if ($feature->plans()->exists()) return ApiResponse::error('This feature is assigned to one or more plans. Remove the plan assignment first.', 422);
        $feature->delete(); return ApiResponse::success(null, 'Feature deleted successfully.');
    }
    private function save(array $data, ?Feature $feature = null)
    {
        try { $feature ? $feature->update($data) : $feature = Feature::create($data); return ApiResponse::success($feature->fresh(), $feature->wasRecentlyCreated ? 'Feature created successfully.' : 'Feature updated successfully.'); }
        catch (\Throwable $e) { report($e); return ApiResponse::error('Unable to save feature.', 500); }
    }
}
