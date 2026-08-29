<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\ProductRequest;
use Yajra\DataTables\DataTables;

class ProductRequestService
{
    public function getProductRequestDataTable(Request $request)
    {
        $query = ProductRequest::query()->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('status', function (ProductRequest $pr) {
                $colors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'fulfilled' => 'bg-blue-100 text-blue-800',
                ];
                $color = $colors[$pr->status] ?? 'bg-gray-100 text-gray-800';

                return '<span class="px-2 py-1 text-xs font-medium rounded-full '.$color.'">'.ucfirst($pr->status).'</span>';
            })
            ->editColumn('created_at', function (ProductRequest $pr) {
                return $pr->created_at->format('d M Y H:i');
            })
            ->addColumn('product_image_preview', function (ProductRequest $pr) {
                if ($pr->product_image) {
                    return '<img src="'.$pr->product_image.'" class="h-10 w-10 rounded object-cover" />';
                }

                return '-';
            })
            ->addColumn('action', function (ProductRequest $pr) {
                $viewBtn = '<button onclick="viewProductRequest('.$pr->id.')" class="bg-blue-600 text-white px-2 py-1 rounded text-sm hover:bg-blue-500 mr-1" title="View"><i class="fa fa-eye"></i></button>';
                $editBtn = '<button onclick="productRequestEdit('.$pr->id.')" class="bg-amber-500 text-white px-2 py-1 rounded text-sm hover:bg-amber-400 mr-1" title="Edit"><i class="fa fa-edit"></i></button>';
                $deleteBtn = '<button onclick="productRequestDelete('.$pr->id.')" class="bg-red-600 text-white px-2 py-1 rounded text-sm hover:bg-red-500 mr-1" title="Delete"><i class="fa fa-trash"></i></button>';
                $statusBtn = '<button onclick="changeProductRequestStatus('.$pr->id.')" class="bg-green-600 text-white px-2 py-1 rounded text-sm hover:bg-green-500" title="Change Status"><i class="fa fa-check-circle"></i></button>';
                $approveBtn = '';
                if ($pr->status !== 'approved' && $pr->status !== 'fulfilled') {
                    $approveBtn = '<button onclick="approveProductRequest('.$pr->id.')" class="bg-emerald-600 text-white px-2 py-1 rounded text-sm hover:bg-emerald-500 mr-1" title="Approve"><i class="fa fa-thumbs-up"></i></button>';
                }

                return $approveBtn.$viewBtn.$editBtn.$deleteBtn.$statusBtn;
            })
            ->rawColumns(['status', 'product_image_preview', 'action'])
            ->make(true);
    }

    public function getProductRequestById(int $id): JsonResponse
    {
        try {
            $productRequest = ProductRequest::with(['user', 'product'])->findOrFail($id);

            return ApiResponse::success($productRequest);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Product request not found.');
        }
    }

    public function updateStatus(int $id, string $status): JsonResponse
    {
        try {
            $productRequest = ProductRequest::findOrFail($id);
            $productRequest->update(['status' => $status]);

            return ApiResponse::success($productRequest->fresh(), 'Status updated successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error updating status: '.$e->getMessage(), 500);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                if (isset($data['product_image']) && $data['product_image'] instanceof UploadedFile) {
                    $fileName = 'product-request-'.time().'.'.$data['product_image']->getClientOriginalExtension();
                    $path = $data['product_image']->storeAs('product-requests', $fileName, 'public');
                    $data['product_image'] = $path;
                }

                $productRequest = ProductRequest::create($data);

                return ApiResponse::success($productRequest, 'Product request created successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error creating request: '.$e->getMessage(), 500);
        }
    }

    public function update(int $id, array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $productRequest = ProductRequest::findOrFail($id);

                if (isset($data['product_image']) && $data['product_image'] instanceof UploadedFile) {
                    // Delete old image
                    if ($productRequest->product_image) {
                        Storage::disk('public')->delete($productRequest->product_image);
                    }
                    $fileName = 'product-request-'.time().'.'.$data['product_image']->getClientOriginalExtension();
                    $path = $data['product_image']->storeAs('product-requests', $fileName, 'public');
                    $data['product_image'] = $path;
                } else {
                    unset($data['product_image']);
                }

                $productRequest->update($data);

                return ApiResponse::success($productRequest->fresh(), 'Product request updated successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error updating request: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $productRequest = ProductRequest::findOrFail($id);
            if ($productRequest->product_image) {
                Storage::disk('public')->delete($productRequest->product_image);
            }
            $productRequest->delete();

            return ApiResponse::success(null, 'Product request deleted successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting request: '.$e->getMessage(), 500);
        }
    }

    public function storeFromFrontend(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                // Handle image upload
                if (isset($data['product_image']) && $data['product_image'] instanceof UploadedFile) {
                    $fileName = 'product-request-'.time().'.'.$data['product_image']->getClientOriginalExtension();
                    $path = $data['product_image']->storeAs('product-requests', $fileName, 'public');
                    $data['product_image'] = $path;
                }

                $productRequest = ProductRequest::create($data);

                return ApiResponse::success($productRequest, 'Your product request has been submitted successfully. We will review it shortly.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error submitting request: '.$e->getMessage(), 500);
        }
    }
}
