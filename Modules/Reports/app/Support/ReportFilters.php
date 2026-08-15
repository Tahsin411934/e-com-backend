<?php

namespace Modules\Reports\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Central value object that parses the common report filters:
 * date range, store, product, category, brand and period preset.
 *
 * It also computes the corresponding "previous" period so every report
 * can include a previous-period vs current-period comparison.
 */
class ReportFilters
{
    public ?Carbon $from;

    public ?Carbon $to;

    public ?Carbon $previousFrom;

    public ?Carbon $previousTo;

    public ?int $storeId;

    public ?int $productId;

    public ?int $categoryId;

    public ?int $brandId;

    /** @var string one of today|week|month|all|custom */
    public string $period;

    private function __construct(array $data)
    {
        $this->from = $data['from'];
        $this->to = $data['to'];
        $this->previousFrom = $data['previousFrom'];
        $this->previousTo = $data['previousTo'];
        $this->period = $data['period'];
        $this->storeId = $data['storeId'];
        $this->productId = $data['productId'];
        $this->categoryId = $data['categoryId'];
        $this->brandId = $data['brandId'];
    }

    public static function fromRequest(Request $request): self
    {
        $period = $request->input('period', '');

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $period = 'custom';
            $from = $request->filled('date_from')
                ? Carbon::parse($request->input('date_from'))->startOfDay()
                : null;
            $to = $request->filled('date_to')
                ? Carbon::parse($request->input('date_to'))->endOfDay()
                : null;
        } else {
            switch ($period) {
                case 'today':
                    $from = Carbon::today()->startOfDay();
                    $to = Carbon::now();
                    break;
                case 'week':
                    $from = Carbon::now()->startOfWeek();
                    $to = Carbon::now();
                    break;
                case 'month':
                    $from = Carbon::now()->startOfMonth();
                    $to = Carbon::now();
                    break;
                default:
                    $from = null;
                    $to = null;
                    $period = 'all';
                    break;
            }
        }

        [$previousFrom, $previousTo] = self::previousPeriod($period, $from, $to);

        return new self([
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'previousFrom' => $previousFrom,
            'previousTo' => $previousTo,
            'storeId' => $request->filled('store_id') ? (int) $request->input('store_id') : null,
            'productId' => $request->filled('product_id') ? (int) $request->input('product_id') : null,
            'categoryId' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
            'brandId' => $request->filled('brand_id') ? (int) $request->input('brand_id') : null,
        ]);
    }

    private static function previousPeriod(string $period, ?Carbon $from, ?Carbon $to): array
    {
        if ($from === null || $to === null) {
            return [null, null];
        }

        if ($period === 'custom') {
            $span = (int) $to->copy()->diffInDays($from) + 1;
            return [$from->copy()->subDays($span), $to->copy()->subDays($span)];
        }

        return match ($period) {
            'today' => [$from->copy()->subDay(), $to->copy()->subDay()],
            'week' => [$from->copy()->subWeek(), $to->copy()->subWeek()],
            'month' => [$from->copy()->subMonth(), $to->copy()->subMonth()],
            default => [null, null],
        };
    }

    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'from' => $this->from?->toDateString(),
            'to' => $this->to?->toDateString(),
            'store_id' => $this->storeId,
            'product_id' => $this->productId,
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
        ];
    }
}