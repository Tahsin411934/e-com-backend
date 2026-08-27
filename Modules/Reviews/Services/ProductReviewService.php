<?php

namespace Modules\Reviews\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Reviews\Models\ProductReview;
use Yajra\DataTables\DataTables;

class ProductReviewService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function getReviewDataTable(Request $request)
    {
        $query = ProductReview::query()->with(['product', 'user'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', (string) $request->string('status'));
                }

                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->integer('product_id'));
                }
            })
            ->editColumn('rating', fn($r) => str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating))
            ->editColumn('status', function ($r) {
                $colors = [
                    'approved' => 'bg-green-100 text-green-700',
                    'pending' => 'bg-amber-100 text-amber-700',
                    'rejected' => 'bg-red-100 text-red-700',
                ];
                $color = $colors[$r->status] ?? 'bg-gray-100 text-gray-700';

                return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold '.$color.'">'.e(ucfirst($r->status)).'</span>';
            })
            ->addColumn('product_name', fn($r) => $r->product ? $r->product->name : '-')
            ->addColumn('user_name', fn($r) => $r->user ? $r->user->name : '-')
            ->editColumn('is_verified_purchase', fn($r) => $r->is_verified_purchase
                ? '<span class="text-green-600 text-xs font-semibold"><i class="fa fa-circle-check mr-1"></i>Verified</span>'
                : '<span class="text-gray-400 text-xs">—</span>')
            ->editColumn('created_at', fn($r) => $r->created_at?->format('d M Y H:i'))
            ->addColumn('action', function ($r) {
                $html = view('components.action-buttons', [
                    'id' => $r->id, 'edit' => 'productReviewEdit', 'delete' => 'productReviewDelete',
                ])->render();

                if ($r->status === 'pending') {
                    $html .= '<button type="button" onclick="productReviewApprove('.$r->id.')" title="Approve" '
                        .'class="bg-emerald-500 text-white px-2 py-1 rounded text-sm hover:bg-emerald-600 transition">'
                        .'<i class="fa fa-check"></i></button>';
                }

                return '<div class="flex items-center justify-center gap-1">'.$html.'</div>';
            })
            ->rawColumns(['action', 'rating', 'status', 'is_verified_purchase'])
            ->make(true);
    }

    public function saveReview(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['review_id'] ?? null; unset($data['review_id']);
                if ($id) { $item = ProductReview::findOrFail($id); $item->update($data); $msg = 'Review updated.'; }
                else {
                    $item = ProductReview::create($data); $msg = 'Review created.';

                    // In-app bell notification for every admin (user_id = null = broadcast).
                    $this->notifications->notify(
                        null,
                        'review.created',
                        'New product review received',
                        sprintf(
                            '%s left a %d★ review: %s',
                            $item->user?->name ?? 'A customer',
                            $item->rating,
                            $item->title
                        ),
                        ['review_id' => $item->id, 'product_id' => $item->product_id, 'url' => '/product-reviews'],
                    );
                }
                return ['status' => 'success', 'message' => $msg, 'review' => $item->fresh()->load(['product', 'user'])];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getReviewById(int $id): array
    {
        try { $item = ProductReview::with(['product', 'user'])->findOrFail($id); return ['status' => 'success', 'review' => $item]; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Review not found.']; }
    }

    public function deleteReview(int $id): array
    {
        try { ProductReview::findOrFail($id)->delete(); return ['status' => 'success', 'message' => 'Review deleted.']; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]; }
    }

    public function approveReview(int $id): array
    {
        try { ProductReview::findOrFail($id)->update(['status' => 'approved']); return ['status' => 'success', 'message' => 'Review approved.']; }
        catch (\Exception $e) { return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]; }
    }
}