<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Reviews\Http\Requests\NotificationRequest;
use Modules\Reviews\Models\Notification;
use Modules\Reviews\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function index()
    {
        // Keep Eloquent models so the view can use $user->id / $user->name accessor.
        $users = User::query()
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $types = Notification::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        return view('reviews::notifications.index', compact('users', 'types'));
    }

    public function dataTable(Request $request)
    {
        return $this->service->getNotificationDataTable($request);
    }

    public function store(NotificationRequest $request)
    {
        return $this->service->saveNotification($request->validated());
    }

    public function show($id)
    {
        return $this->service->getNotificationById((int) $id);
    }

    public function update(NotificationRequest $request, $id)
    {
        return $this->service->saveNotification($request->validated() + ['notification_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->deleteNotification((int) $id);
    }

    public function markAsRead($id)
    {
        return $this->service->markAsRead((int) $id);
    }

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
        return $this->service->markAllReadForUser((int) auth()->id());
    }
}
