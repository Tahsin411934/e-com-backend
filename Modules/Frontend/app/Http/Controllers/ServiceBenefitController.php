<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Frontend\Models\ServiceBenefit;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreStaff;

class ServiceBenefitController extends Controller
{
    private function storeScope(Request $request): ?int
    {
        if ($request->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            return null;
        }

        $id = Store::where('owner_id', $request->user()->id)->value('id')
            ?? StoreStaff::where('user_id', $request->user()->id)->where('status', 'active')->value('store_id');
        abort_unless($id, 403, 'No active store is associated with your account.');

        return (int) $id;
    }

    private function scopedQuery(?int $storeId): Builder
    {
        return ServiceBenefit::query()->when($storeId !== null, fn ($query) => $query->where('store_id', $storeId));
    }

    private function validated(Request $request, ?int $storeId): array
    {
        $data = $request->validate([
            'store_id' => $storeId === null ? ['required', 'integer', 'exists:stores,id'] : ['exclude'],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', 'string', Rule::in(array_keys(ServiceBenefit::ICONS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['store_id'] = $storeId ?? (int) $data['store_id'];

        return $data;
    }

    public function index(Request $request)
    {
        $storeId = $this->storeScope($request);
        $request->validate(['store_id' => ['nullable', 'integer', 'exists:stores,id']]);
        $benefits = $this->scopedQuery($storeId)
            ->when($storeId === null && $request->filled('store_id'), fn ($query) => $query->where('store_id', $request->integer('store_id')))
            ->with('store')->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('frontend::service-benefits', [
            'benefits' => $benefits,
            'storeId' => $storeId,
            'stores' => Store::query()->when($storeId !== null, fn ($query) => $query->whereKey($storeId))->orderBy('name')->get(['id', 'name']),
            'icons' => ServiceBenefit::ICONS,
        ]);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeScope($request);
        $benefit = ServiceBenefit::create($this->validated($request, $storeId));

        return redirect()->route('frontend.service-benefits.index', ['store_id' => $benefit->store_id])->with('status', 'Service benefit added.');
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeScope($request);
        $benefit = $this->scopedQuery($storeId)->findOrFail($id);
        $benefit->update($this->validated($request, $storeId));

        return redirect()->route('frontend.service-benefits.index', ['store_id' => $benefit->store_id])->with('status', 'Service benefit updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $benefit = $this->scopedQuery($this->storeScope($request))->findOrFail($id);
        $storeId = $benefit->store_id;
        $benefit->delete();

        return redirect()->route('frontend.service-benefits.index', ['store_id' => $storeId])->with('status', 'Service benefit deleted.');
    }
}
