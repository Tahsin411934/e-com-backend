<?php

namespace Modules\Reviews\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Reviews\Models\Notification;
use Yajra\DataTables\DataTables;

class NotificationService
{
    public function getNotificationDataTable(Request $request)
    {
        $query = Notification::query()->with('user')->orderByDesc('created_at');

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('type')) {
                    $query->where('type', (string) $request->string('type'));
                }

                if ($request->filled('channel')) {
                    $query->where('channel', (string) $request->string('channel'));
                }

                if ($request->input('read') === 'unread') {
                    $query->whereNull('read_at');
                }
            })
            ->editColumn('type', fn($n) => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">'.e($n->type).'</span>')
            ->editColumn('channel', fn($n) => '<span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">'.e(strtoupper($n->channel ?? 'in_app')).'</span>')
            ->editColumn('body', fn($n) => e(\Illuminate\Support\Str::limit($n->body ?? '', 90)) ?: '<span class="text-gray-400">—</span>')
            ->editColumn('read_at', fn($n) => $n->read_at
                ? '<span class="text-gray-400 text-xs">'.e($n->read_at->format('d M Y H:i')).'</span>'
                : '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Unread</span>')
            ->editColumn('sent_at', fn($n) => $n->sent_at ? $n->sent_at->format('d M Y H:i') : '-')
            ->addColumn('user_name', fn($n) => $n->user ? $n->user->name : 'All Users')
            ->editColumn('created_at', fn($n) => $n->created_at?->format('d M Y H:i'))
            ->addColumn('action', fn($n) => view('components.action-buttons', [
                'id' => $n->id, 'edit' => 'notificationEdit', 'delete' => 'notificationDelete',
            ])->render())
            ->rawColumns(['action', 'type', 'channel', 'body', 'read_at'])->make(true);
    }

    public function saveNotification(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['notification_id'] ?? null; unset($data['notification_id']);
                if ($id) { $item = Notification::findOrFail($id); $item->update($data); $msg = 'Notification updated.'; }
                else { $data['sent_at'] = $data['sent_at'] ?? now(); $item = Notification::create($data); $msg = 'Notification created.'; }
                return ['status' => 'success', 'message' => $msg, 'notification' => $item->fresh()->load('user')];
            });
        } catch (\Exception $e) { return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]; }
    }

    public function getNotificationById(int $id): array
    {
        try { $item = Notification::with('user')->findOrFail($id); return ['status' => 'success', 'notification' => $item]; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Notification not found.']; }
    }

    public function deleteNotification(int $id): array
    {
        try { Notification::findOrFail($id)->delete(); return ['status' => 'success', 'message' => 'Notification deleted.']; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]; }
    }

    public function markAsRead(int $id): array
    {
        try { Notification::findOrFail($id)->update(['read_at' => now()]); return ['status' => 'success', 'message' => 'Marked as read.']; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]; }
    }

    /**
     * Create an in-app notification. user_id = null broadcasts to every admin
     * (the notification bell shows broadcast rows to all logged-in users).
     */
    public function notify(?int $userId, string $type, string $subject, ?string $body = null, array $data = [], string $channel = 'in_app'): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'channel' => $channel,
            'subject' => $subject,
            'body' => $body,
            'data' => $data ?: null,
            'sent_at' => now(),
        ]);
    }

    /**
     * Data for the navbar notification bell: unread count + latest items.
     * Rows with user_id = null are broadcasts every admin can see.
     */
    public function bellData(int $userId): array
    {
        $query = Notification::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId));

        $unreadCount = (clone $query)->unread()->count();

        $items = $query->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'subject' => $n->subject,
                'body' => $n->body ? \Illuminate\Support\Str::limit($n->body, 120) : null,
                'is_read' => $n->read_at !== null,
                'url' => $n->data['url'] ?? null,
                'time' => $n->created_at?->diffForHumans(),
            ])
            ->all();

        return ['unread_count' => $unreadCount, 'items' => $items];
    }

    /**
     * Mark every visible notification (own + broadcasts) as read.
     */
    public function markAllRead(int $userId): int
    {
        return Notification::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId))
            ->unread()
            ->update(['read_at' => now()]);
    }
}