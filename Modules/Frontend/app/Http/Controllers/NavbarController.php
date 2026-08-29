<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreNavbarItemRequest;
use Modules\Frontend\Http\Requests\StoreSubnavbarItemRequest;
use Modules\Frontend\Http\Requests\UpdateNavbarItemRequest;
use Modules\Frontend\Http\Requests\UpdateSubnavbarItemRequest;
use Modules\Frontend\Services\NavbarService;

class NavbarController extends Controller
{
    public function __construct(private NavbarService $navbarService) {}

    /**
     * Display the navbar items management page.
     */
    public function index()
    {
        return view('frontend::nav-items');
    }

    /**
     * Display the subnavbar items management page, optionally filtered by navbar_item_id.
     */
    public function subnavbarIndex()
    {
        return view('frontend::sub-nav-items');
    }

    // ===== Navbar Items =====

    public function navbarDataTable(Request $request)
    {
        return $this->navbarService->getNavbarDataTable($request);
    }

    public function storeNavbarItem(StoreNavbarItemRequest $request)
    {
        return $this->navbarService->saveNavbarItem($request->validated());
    }

    public function showNavbarItem($id)
    {
        return $this->navbarService->getNavbarItemById($id);
    }

    public function updateNavbarItem(UpdateNavbarItemRequest $request, $id)
    {
        $data = $request->validated();
        $data['navbar_item_id'] = $id;

        return $this->navbarService->saveNavbarItem($data);
    }

    public function destroyNavbarItem($id)
    {
        return $this->navbarService->deleteNavbarItem($id);
    }

    // ===== Subnavbar Items =====

    public function subnavbarDataTable(Request $request)
    {
        return $this->navbarService->getSubnavbarDataTable($request);
    }

    public function storeSubnavbarItem(StoreSubnavbarItemRequest $request)
    {
        return $this->navbarService->saveSubnavbarItem($request->validated());
    }

    public function showSubnavbarItem($id)
    {
        return $this->navbarService->getSubnavbarItemById($id);
    }

    public function updateSubnavbarItem(UpdateSubnavbarItemRequest $request, $id)
    {
        $data = $request->validated();
        $data['subnavbar_item_id'] = $id;

        return $this->navbarService->saveSubnavbarItem($data);
    }

    public function destroySubnavbarItem($id)
    {
        return $this->navbarService->deleteSubnavbarItem($id);
    }

    /**
     * Get all active navbar items (for select dropdowns).
     */
    public function getNavbarItemsList()
    {
        $items = $this->navbarService->getAllNavbarItems();

        return ApiResponse::fromResult([
            'status' => 'success',
            'navbar_items' => $items,
        ]);
    }
}
