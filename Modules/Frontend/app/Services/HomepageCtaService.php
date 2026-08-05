<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\HomepageCta;
use Yajra\DataTables\DataTables;

class HomepageCtaService
{
    public function getCtaDataTable(Request $request)
    {
        $query = HomepageCta::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('image', function (HomepageCta $cta) {
                if ($cta->image) {
                    $url = asset('storage/' . $cta->image);
                    return '<img src="' . $url . '" alt="CTA Image" class="w-16 h-10 object-cover rounded" />';
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
                $editBtn = '<button onclick="ctaEdit(' . $cta->id . ')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="ctaDelete(' . $cta->id . ')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';
                return '<div class="flex space-x-2 justify-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    public function saveCta(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $ctaId = $data['cta_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['cta_id']);

                // Handle image upload
                if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                    if ($ctaId) {
                        $oldCta = HomepageCta::find($ctaId);
                        if ($oldCta && $oldCta->image) {
                            Storage::disk('public')->delete($oldCta->image);
                        }
                    }
                    $data['image'] = $data['image']->store('homepage-ctas', 'public');
                } else {
                    unset($data['image']);
                }

                // Handle banner_image upload
                if (isset($data['banner_image']) && $data['banner_image'] instanceof UploadedFile) {
                    if ($ctaId) {
                        $oldCta = HomepageCta::find($ctaId);
                        if ($oldCta && $oldCta->banner_image) {
                            Storage::disk('public')->delete($oldCta->banner_image);
                        }
                    }
                    $data['banner_image'] = $data['banner_image']->store('homepage-ctas/banners', 'public');
                } else {
                    unset($data['banner_image']);
                }

                if ($ctaId) {
                    $cta = HomepageCta::findOrFail($ctaId);
                    $cta->update($data);
                    $message = 'CTA updated successfully.';
                } else {
                    $cta = HomepageCta::create($data);
                    $message = 'CTA created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'cta' => $cta->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving CTA: ' . $e->getMessage(),
            ];
        }
    }

    public function getCtaById(int $id): array
    {
        try {
            $cta = HomepageCta::findOrFail($id);
            $ctaArray = $cta->toArray();
            if ($cta->image) {
                $ctaArray['image_url'] = asset('storage/' . $cta->image);
            }
            if ($cta->banner_image) {
                $ctaArray['banner_image_url'] = asset('storage/' . $cta->banner_image);
            }

            return [
                'status' => 'success',
                'cta' => $ctaArray,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'CTA not found.',
            ];
        }
    }

    public function deleteCta(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $cta = HomepageCta::findOrFail($id);

                if ($cta->image) {
                    Storage::disk('public')->delete($cta->image);
                }
                if ($cta->banner_image) {
                    Storage::disk('public')->delete($cta->banner_image);
                }

                $cta->delete();

                return [
                    'status' => 'success',
                    'message' => 'CTA deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting CTA: ' . $e->getMessage(),
            ];
        }
    }
}