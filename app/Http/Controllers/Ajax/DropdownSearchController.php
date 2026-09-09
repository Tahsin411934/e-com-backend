<?php

namespace App\Http\Controllers\Ajax;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Traits\SearchableDropdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Account\Models\AccountAccount;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\TaxRate;
use Modules\Catalog\Models\Unit;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Store\Models\Store;

class DropdownSearchController extends Controller
{
    use SearchableDropdown;

    /**
     * Whitelisted searchable sources.
     *
     * The {source} route segment is never mapped to a class directly —
     * it must exist here, keeping the endpoint closed to user input.
     *
     * Config keys:
     *  - model   (required) Eloquent class to search
     *  - label   (optional) searched + default text column, default "name"
     *  - filters (optional) extra where() pairs, e.g. ['status' => 'active']
     *  - text    (optional) closure for composite display text, e.g.
     *            "Unit (pc)" or "Account - ৳1,200.00". Selects all columns.
     *
     * @return array<string, array{model: class-string<\Illuminate\Database\Eloquent\Model>, label?: string, filters?: array<string, mixed>, text?: callable}>
     */
    private function sources(): array
    {
        return [
        'products' => [
            'model' => Product::class,
            'label' => 'name',
            'filters' => ['status' => 'active'],
        ],
        'categories' => [
            'model' => Category::class,
            'label' => 'name',
            'filters' => ['status' => 'active'],
        ],
        'brands' => [
            'model' => Brand::class,
            'label' => 'name',
            'filters' => ['status' => 'active'],
        ],
        'units' => [
            'model' => Unit::class,
            'label' => 'name',
            'text' => fn (Unit $unit) => $unit->name.' ('.$unit->short_name.')',
        ],
        'sizes' => [
            'model' => Size::class,
            'label' => 'group_name',
        ],
        'tax-rates' => [
            'model' => TaxRate::class,
            'label' => 'name',
            'text' => fn (TaxRate $taxRate) => $taxRate->name.' ('
                .($taxRate->type === 'percentage' ? $taxRate->rate.'%' : '৳'.number_format((float) $taxRate->rate, 2)).')',
        ],
        'navbar-items' => [
            'model' => NavbarItem::class,
            'label' => 'name',
        ],
        'subnavbar-items' => [
            'model' => SubnavbarItem::class,
            'label' => 'name',
        ],
        'accounts' => [
            'model' => AccountAccount::class,
            'label' => 'name',
            'text' => fn (AccountAccount $account) => $account->name.' - ৳'.number_format((float) $account->current_balance, 2),
        ],
        'stores' => [
            'model' => Store::class,
            'label' => 'name',
        ],
        ];
    }

    /**
     * GET /ajax/dropdown-search/{source}?q=term&page=1&limit=10
     *
     * Response (App\Helpers\ApiResponse shape consumed by select2-init.js):
     * { status: "success", data: { results: [{id, text}], pagination: { more } } }
     */
    public function search(Request $request, string $source): JsonResponse
    {
        $sources = $this->sources();

        if (! isset($sources[$source])) {
            return ApiResponse::notFound("Dropdown search source [{$source}] is not defined.");
        }

        $config = $sources[$source];

        return ApiResponse::success(
            $this->dropdownOptions(
                $request,
                $config['model'],
                $config['label'] ?? 'name',
                $config['filters'] ?? [],
                10,
                50,
                $config['text'] ?? null,
            )
        );
    }
}
