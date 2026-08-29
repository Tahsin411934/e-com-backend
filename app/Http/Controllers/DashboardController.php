<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    /**
     * Display the dashboard with real database data.
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', [
            'kpi' => $data['kpi'],
            'monthlyRevenue' => $data['monthlyRevenue'],
            'revenueBySource' => $data['revenueBySource'],
            'deliveryOverview' => $data['deliveryOverview'],
            'recentOrders' => $data['recentOrders'],
            'topSellingProducts' => $data['topSellingProducts'],
            'lowStockProducts' => $data['lowStockProducts'],
            'recentActivities' => $data['recentActivities'],
            'inventorySummary' => $data['inventorySummary'],
        ]);
    }

    /**
     * API endpoint to get fresh dashboard data (for AJAX refresh).
     */
    public function apiData()
    {
        $data = $this->dashboardService->getDashboardData();

        return ApiResponse::success($data, 'Dashboard data retrieved successfully.');
    }
}
