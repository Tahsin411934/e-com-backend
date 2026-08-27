<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Reviews\Models\Notification;
use Modules\Reviews\Services\NotificationService;
use Modules\Identity\Models\User;

class NotificationController extends Controller
{
    protected NotificationService $service;

    public function __construct(NotificationService $service) { $this->service = $service; }

    public function index()
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name'])->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);
        $types = Notification::query()->select('type')->distinct()->orderBy('type')->pluck('type');
        return view('reviews::notifications.index', compact('users', 'types'));
    }

    public function dataTable(Request $request) { return $this->service->getNotificationDataTable($request); }
    public function store(Request $request) { $result = $this->service->saveNotification($request->all()); return response()->json($result, $result['status'] === 'success' ? 200 : 500); }
    public function show($id) { return response()->json($this->service->getNotificationById($id)); }
    public function update(Request $request, $id) { $data = $request->all(); $data['notification_id'] = $id; $result = $this->service->saveNotification($data); return response()->json($result, $result['status'] === 'success' ? 200 : 500); }
    public function destroy($id) { $result = $this->service->deleteNotification($id); return response()->json($result, $result['status'] === 'success' ? 200 : 500); }
    public function markAsRead($id) { $result = $this->service->markAsRead($id); return response()->json($result, $result['status'] === 'success' ? 200 : 500); }

    /**
     * Data for the navbar notification bell (unread count + latest items).
     */
    public function bell()
    {
        return response()->json($this->service->bellData((int) auth()->id()));
    }

    /**
     * Mark every visible notification as read (bell "mark all").
     */
    public function markAllRead()
    {
        $count = $this->service->markAllRead((int) auth()->id());

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
            'count' => $count,
        ]);
    }
}