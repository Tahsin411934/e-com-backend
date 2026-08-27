<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * Server-rendered report pages. Each page defines the report sections it
 * shows; every section loads its data from the reports JSON API
 * (ReportApiController::$dispatchMap) and can be exported to CSV.
 *
 * Sections must reference whitelisted {category}.{method} pairs — pages for
 * categories that have no backing service yet (campaigns, finance, refunds,
 * purchases) render with an empty section list until those services exist.
 */
class ReportController extends Controller
{
    /**
     * Normalise section definitions (each entry: method, title, category?).
     */
    protected function sections(string $category, array $sections): array
    {
        return collect($sections)
            ->map(fn (array $section) => [
                'category' => $section['category'] ?? $category,
                'method' => $section['method'],
                'title' => $section['title'],
            ])
            ->all();
    }

    public function dashboard()
    {
        return view('reports::page', [
            'title' => 'Executive Dashboard',
            'category' => 'executive',
            'sections' => $this->sections('executive', [
                ['method' => 'dashboard', 'title' => 'Dashboard Overview'],
                ['method' => 'kpis', 'title' => 'Key Metrics'],
                ['method' => 'alerts', 'title' => 'Alerts'],
                ['method' => 'salesTrend', 'title' => 'Sales Trend'],
            ]),
        ]);
    }

    public function sales()
    {
        return view('reports::page', [
            'title' => 'Sales Reports',
            'category' => 'sales',
            'sections' => $this->sections('sales', [
                ['method' => 'salesByDate', 'title' => 'Sales by Date'],
                ['method' => 'salesByStore', 'title' => 'Sales by Store'],
                ['method' => 'salesBySource', 'title' => 'Sales by Source (Online / POS / Admin)'],
                ['method' => 'salesByPaymentMethod', 'title' => 'Sales by Payment Method'],
                ['method' => 'salesByOrderStatus', 'title' => 'Sales by Order Status'],
            ]),
        ]);
    }

    public function products()
    {
        return view('reports::page', [
            'title' => 'Product Reports',
            'category' => 'product-perf',
            'sections' => $this->sections('product-perf', [
                ['method' => 'variantWise', 'title' => 'Variant-wise Performance'],
                ['method' => 'returnRate', 'title' => 'Return Rate'],
                ['method' => 'conversion', 'title' => 'Conversion'],
                ['method' => 'stockTurnover', 'title' => 'Stock Turnover'],
            ]),
        ]);
    }

    public function inventory()
    {
        return view('reports::page', [
            'title' => 'Inventory Reports',
            'category' => 'inventory-stock',
            'sections' => $this->sections('inventory-stock', [
                ['method' => 'movements', 'title' => 'Stock Movements'],
                ['method' => 'damagedExpiredLost', 'title' => 'Damaged / Expired / Lost'],
                ['method' => 'deadStock', 'title' => 'Dead Stock'],
                ['method' => 'reorder', 'title' => 'Reorder Suggestions'],
                ['method' => 'transfers', 'title' => 'Stock Transfers'],
                ['category' => 'inventory', 'method' => 'currentStock', 'title' => 'Current Stock'],
                ['category' => 'inventory', 'method' => 'lowOutOfStock', 'title' => 'Low / Out of Stock'],
                ['category' => 'inventory', 'method' => 'variantLevelStock', 'title' => 'Variant-level Stock'],
                ['category' => 'inventory', 'method' => 'valuation', 'title' => 'Stock Valuation'],
            ]),
        ]);
    }

    public function orders()
    {
        return view('reports::page', [
            'title' => 'Order & Fulfilment Reports',
            'category' => 'order-timing',
            'sections' => $this->sections('order-timing', [
                ['method' => 'fulfillmentTimes', 'title' => 'Fulfilment Times'],
                ['method' => 'failedDeliveries', 'title' => 'Failed Deliveries'],
                ['method' => 'codVsPrepaid', 'title' => 'COD vs Prepaid'],
            ]),
        ]);
    }

    public function shipping()
    {
        return view('reports::page', [
            'title' => 'Shipping & Delivery Reports',
            'category' => 'shipping',
            'sections' => $this->sections('shipping', [
                ['method' => 'overview', 'title' => 'Shipping Overview'],
                ['method' => 'driverPerformance', 'title' => 'Driver Performance'],
                ['method' => 'zoneCost', 'title' => 'Zone Costs'],
                ['method' => 'failedReasons', 'title' => 'Failure Reasons'],
            ]),
        ]);
    }

    public function customers()
    {
        return view('reports::page', [
            'title' => 'Customer Reports',
            'category' => 'customers',
            'sections' => $this->sections('customers', [
                ['method' => 'kpis', 'title' => 'Customer KPIs'],
                ['method' => 'topCustomers', 'title' => 'Top Customers'],
                ['method' => 'citySales', 'title' => 'Sales by City'],
                ['method' => 'orderFrequency', 'title' => 'Order Frequency'],
            ]),
        ]);
    }

    public function campaigns()
    {
        return view('reports::page', [
            'title' => 'Campaign & Coupon Reports',
            'category' => 'campaigns',
            // No CampaignReportService exists yet — the page renders with a
            // notice until the service is implemented.
            'sections' => [],
        ]);
    }

    public function finance()
    {
        return view('reports::page', [
            'title' => 'Finance Reports',
            'category' => 'finance',
            'sections' => [],
        ]);
    }

    public function refunds()
    {
        return view('reports::page', [
            'title' => 'Refund & Return Reports',
            'category' => 'refunds',
            'sections' => [],
        ]);
    }

    public function purchases()
    {
        return view('reports::page', [
            'title' => 'Purchase & Supplier Reports',
            'category' => 'purchases',
            'sections' => [],
        ]);
    }

    public function staff()
    {
        return view('reports::page', [
            'title' => 'Staff Reports',
            'category' => 'performance',
            'sections' => $this->sections('performance', [
                ['method' => 'posSalesByCashier', 'title' => 'POS Sales by Cashier'],
                ['method' => 'topProducts', 'title' => 'Top Selling Products'],
                ['method' => 'topCategories', 'title' => 'Top Selling Categories'],
            ]),
        ]);
    }
}

