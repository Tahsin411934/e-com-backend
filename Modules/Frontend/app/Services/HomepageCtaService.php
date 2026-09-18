<?php

namespace Modules\Frontend\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\HomepageCta;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class HomepageCtaService
{
    public function getCtaDataTable(Request $request)
    {
        $query = HomepageCta::query()->with('store')->orderByDesc('created_at');

        // Store Owner → only their own store's CTAs. Platform staff → all CTAs,
        // optionally filtered by a specific store (or "global").
        $ownedStoreId = $this->resolveStoreScope($request->user());

        if ($ownedStoreId !== null) {
            $query->where('store_id', $ownedStoreId);
        } elseif ($request->filled('store_id')) {
            $filter = $request->input('store_id');

            if ($filter === 'global') {
                $query->whereNull('store_id');
            } elseif (is_numeric($filter)) {
                $query->where('store_id', (int) $filter);
            }
        }

        return DataTables::of($query)
            ->addColumn('store_name', function (HomepageCta $cta) {
                return $cta->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('image', function (HomepageCta $cta) {
                if ($cta->image) {
                    $url = asset('storage/'.$cta->image);

                    return '<img src="'.$url.'" alt="CTA Image" class="w-16 h-10 object-cover rounded" />';
                }

                return '-';
            })
            ->editColumn('status', function (HomepageCta $cta) {
                return ucfirst($cta->status);
            })
            ->editColumn('created_at', function (HomepageCta $cta) {
                return $cta->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (HomepageCta $cta) {
                $editBtn = '<button onclick="ctaEdit('.$cta->id.')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="ctaDelete('.$cta->id.')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';

                return '<div class="flex space-x-2 justify-center">'.$editBtn.$deleteBtn.'</div>';
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    /**
     * Resolve the CTA scope for the acting user:
     *  - Store Owner (with a store)  → their owned store id
     *  - Store Owner (without store) → 0 (matches nothing)
     *  - Everyone else (platform)    → null (unrestricted)
     */
    private function resolveStoreScope(?User $user): ?int
    {
        if (! $user || ! $user->isStoreOwner()) {
            return null;
        }

        $store = $user->ownedStore()->first(['id']);

        return $store ? (int) $store->id : 0;
    }

    /**
     * Ownership check for a single CTA.
     */
    private function accessDenied(HomepageCta $cta): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        return $ownedStoreId !== null && (int) $cta->store_id !== $ownedStoreId;
    }

    public function saveCta(array $data): JsonResponse
    {
        try {
            $ownedStoreId = $this->resolveStoreScope(auth()->user());

            if ($ownedStoreId === 0) {
                return ApiResponse::error('No store is associated with your account, so CTAs cannot be managed.', 403);
            }

            return DB::transaction(function () use ($data, $ownedStoreId) {
                $ctaId = $data['cta_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['cta_id']);

                // Store assignment is always resolved server-side.
                if ($ownedStoreId !== null) {
                    // Store Owner: CTA always belongs to their own store.
                    $data['store_id'] = $ownedStoreId;
                } else {
                    // Platform staff: explicit store selection, or null for a global CTA.
                    $data['store_id'] = (isset($data['store_id']) && $data['store_id'] !== '' && $data['store_id'] !== null)
                        ? (int) $data['store_id']
                        : null;
                }

                $cta = null;

                if ($ctaId) {
                    $cta = HomepageCta::find($ctaId);

                    if (! $cta) {
                        return ApiResponse::notFound('CTA not found.');
                    }

                    if ($this->accessDenied($cta)) {
                        return ApiResponse::error('You are not allowed to modify this CTA.', 403);
                    }
                }

                // Handle image upload
                if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                    if ($cta && $cta->image) {
                        Storage::disk('public')->delete($cta->image);
                    }
                    $data['image'] = $data['image']->store('homepage-ctas', 'public');
                } else {
                    unset($data['image']);
                }

                // Handle banner_image upload
                if (isset($data['banner_image']) && $data['banner_image'] instanceof UploadedFile) {
                    if ($cta && $cta->banner_image) {
                        Storage::disk('public')->delete($cta->banner_image);
                    }
                    $data['banner_image'] = $data['banner_image']->store('homepage-ctas/banners', 'public');
                } else {
                    unset($data['banner_image']);
                }

                if ($cta) {
                    $cta->update($data);
                    $message = 'CTA updated successfully.';
                } else {
                    $cta = HomepageCta::create($data);
                    $message = 'CTA created successfully.';
                }

                return ApiResponse::success($cta->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving CTA: '.$e->getMessage(), 500);
        }
    }

    public function getCtaById(int $id): JsonResponse
    {
        try {
            $cta = HomepageCta::findOrFail($id);

            if ($this->accessDenied($cta)) {
                return ApiResponse::error('You are not allowed to view this CTA.', 403);
            }

            $ctaArray = $cta->toArray();
            if ($cta->image) {
                $ctaArray['image_url'] = asset('storage/'.$cta->image);
            }
            if ($cta->banner_image) {
                $ctaArray['banner_image_url'] = asset('storage/'.$cta->banner_image);
            }

            return ApiResponse::success($ctaArray);
        } catch (\Exception $e) {
            return ApiResponse::notFound('CTA not found.');
        }
    }

    public function deleteCta(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $cta = HomepageCta::findOrFail($id);

                if ($this->accessDenied($cta)) {
                    return ApiResponse::error('You are not allowed to delete this CTA.', 403);
                }

                if ($cta->image) {
                    Storage::disk('public')->delete($cta->image);
                }
                if ($cta->banner_image) {
                    Storage::disk('public')->delete($cta->banner_image);
                }

                $cta->delete();

                return ApiResponse::success(null, 'CTA deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting CTA: '.$e->getMessage(), 500);
        }
    }
}
