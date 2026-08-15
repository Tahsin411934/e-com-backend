<?php

namespace Modules\Reports\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Reports\Support\ReportFilters;

/**
 * Shared helpers every report service builds on. Keeps the status constants
 * and the filter-application logic in one place so all reports behave alike.
 */
abstract class BaseReportService
{
    /** Orders with these statuses are excluded from revenue/profit figures. */
    public const REVENUE_EXCLUDED_STATUSES = ['cancelled', 'refunded'];

    /**
     * Apply the date-range and entity filters to an Order query.
     */
    protected function scopeOrderQuery(Builder $query, ReportFilters $filters, bool $revenueOnly = false): Builder
    {
        if ($revenueOnly) {
            $query->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES);
        }

        if ($filters->from !== null) {
            $query->where('placed_at', '>=', $filters->from);
        }

        if ($filters->to !== null) {
            $query->where('placed_at', '<=', $filters->to);
        }

        if ($filters->storeId !== null) {
            $query->where('store_id', $filters->storeId);
        }

        if ($filters->productId !== null || $filters->categoryId !== null || $filters->brandId !== null) {
            $query->whereHas('items', function (Builder $q) use ($filters) {
                if ($filters->productId !== null) {
                    $q->where('product_id', $filters->productId);
                }

                if ($filters->categoryId !== null || $filters->brandId !== null) {
                    $q->whereHas('product', function (Builder $p) use ($filters) {
                        if ($filters->categoryId !== null) {
                            $p->where('category_id', $filters->categoryId);
                        }

                        if ($filters->brandId !== null) {
                            $p->where('brand_id', $filters->brandId);
                        }
                    });
                }
            });
        }

        return $query;
    }

    /**
     * Base Order query for a segment, with date + store filter applied.
     */
    protected function orderQuery(ReportFilters $filters, bool $revenueOnly = false): Builder
    {
        return $this->scopeOrderQuery(Order::query(), $filters, $revenueOnly);
    }

    /**
     * Scoped OrderItem query (used heavily for product / variant aggregations).
     */
    protected function orderItemQuery(ReportFilters $filters, bool $revenueOnly = true): Builder
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at');

        if ($revenueOnly) {
            $query->whereNotIn('orders.status', self::REVENUE_EXCLUDED_STATUSES);
        }

        if ($filters->from !== null) {
            $query->where('orders.placed_at', '>=', $filters->from);
        }

        if ($filters->to !== null) {
            $query->where('orders.placed_at', '<=', $filters->to);
        }

        if ($filters->storeId !== null) {
            $query->where('orders.store_id', $filters->storeId);
        }

        if ($filters->productId !== null) {
            $query->where('order_items.product_id', $filters->productId);
        }

        if ($filters->categoryId !== null || $filters->brandId !== null) {
            $query->whereHas('product', function (Builder $p) use ($filters) {
                if ($filters->categoryId !== null) {
                    $p->where('category_id', $filters->categoryId);
                }

                if ($filters->brandId !== null) {
                    $p->where('brand_id', $filters->brandId);
                }
            });
        }

        return $query;
    }

    /**
     * Compute a % change vs the previous period filling the common KPI shape.
     */
    protected function kpi(string $key, string $label, float $current, ?float $previous): array
    {
        $change = null;

        if ($previous !== null && $previous != 0) {
            $change = round((($current - $previous) / abs($previous)) * 100, 1);
        } elseif ($previous !== null && (int) $previous === 0) {
            $change = $current > 0 ? 100.0 : 0.0;
        }

        return [
            'key' => $key,
            'label' => $label,
            'value' => round((float) $current, 2),
            'previous' => $previous !== null ? round((float) $previous, 2) : null,
            'change' => $change,
            'currency' => 'BDT',
        ];
    }

    protected function percentChange(float $value): ?float
    {
        return $value !== 0.0 ? round($value, 1) : null;
    }
}