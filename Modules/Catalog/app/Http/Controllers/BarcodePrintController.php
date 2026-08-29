<?php

namespace Modules\Catalog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Services\BarcodePrintService;

class BarcodePrintController extends Controller
{
    public function __construct(private BarcodePrintService $barcodeService) {}

    public function index()
    {
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('catalog::barcode-print.index', compact('brands', 'categories'));
    }

    public function search(Request $request)
    {
        return $this->barcodeService->searchProducts($request);
    }

    public function autocomplete(Request $request)
    {
        return $this->barcodeService->autocomplete((string) $request->get('q', ''));
    }

    public function variants(Request $request, $productId)
    {
        return $this->barcodeService->getProductVariants((int) $productId);
    }

    public function print(Request $request)
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.variant_id' => 'required|exists:product_variants,id',
            'variants.*.copies' => 'required|integer|min:1|max:100',
            'paper_size' => 'required|in:letter,a4',
            'label_size' => 'required|in:1x1,1x2,2x2,2x3,3x4',
        ]);

        $barcodes = $this->barcodeService->generateLabels($request->variants);
        $paperSize = $request->paper_size;
        $labelSize = $request->label_size;

        return view('catalog::barcode-print.print', compact('barcodes', 'paperSize', 'labelSize'));
    }
}
