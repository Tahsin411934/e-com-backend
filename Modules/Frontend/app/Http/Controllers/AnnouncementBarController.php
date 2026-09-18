<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreAnnouncementBarRequest;
use Modules\Frontend\Http\Requests\UpdateAnnouncementBarRequest;
use Modules\Frontend\Services\AnnouncementBarService;
use Modules\Store\Models\Store;

class AnnouncementBarController extends Controller
{
    public function __construct(private AnnouncementBarService $announcementBarService) {}

    public function index()
    {
        $user = auth()->user();
        $isStoreOwner = $user->isStoreOwner();

        // Store Owner only manages their own store; platform staff can pick any store.
        $stores = $isStoreOwner
            ? collect($user->ownedStore ? [$user->ownedStore] : [])
            : Store::query()->orderBy('name')->get(['id', 'name']);

        return view('frontend::announcement-bars', [
            'isStoreOwner' => $isStoreOwner,
            'stores' => $stores,
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->announcementBarService->getAnnouncementBarDataTable($request);
    }

    public function store(StoreAnnouncementBarRequest $request)
    {
        return $this->announcementBarService->saveAnnouncementBar($request->validated());
    }

    public function show($id)
    {
        return $this->announcementBarService->getAnnouncementBarById((int) $id);
    }

    public function update(UpdateAnnouncementBarRequest $request, $id)
    {
        return $this->announcementBarService->saveAnnouncementBar($request->validated() + ['announcement_bar_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->announcementBarService->deleteAnnouncementBar((int) $id);
    }
}
