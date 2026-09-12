<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Print</title>
    <style>
        @page {
            margin: 0.5in;
            @if($paperSize == 'letter')
            size: letter;
            @else
            size: A4;
            @endif
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }

        .barcode-grid {
            display: grid;
            @switch($labelSize)
                @case('1x1')
                    grid-template-columns: repeat(8, 1fr);
                    gap: 2px;
                    @break
                @case('1x2')
                    grid-template-columns: repeat(4, 1fr);
                    gap: 4px;
                    @break
                @case('2x2')
                    grid-template-columns: repeat(4, 1fr);
                    gap: 6px;
                    @break
                @case('2x3')
                    grid-template-columns: repeat(3, 1fr);
                    gap: 8px;
                    @break
                @case('3x4')
                    grid-template-columns: repeat(2, 1fr);
                    gap: 10px;
                    @break
                @default
                    grid-template-columns: repeat(4, 1fr);
                    gap: 6px;
            @endswitch
            padding: 10px;
        }

        .barcode-item {
            text-align: center;
            border: 1px dashed #ccc;
            padding: 8px;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80px;
        }

        .barcode-item svg {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .product-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .barcode-number {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
            letter-spacing: 1px;
        }

        .sku-text {
            font-size: 7px;
            color: #888;
            margin-top: 1px;
        }

        @media print {
            @page { margin: 0.3in; }
            .barcode-item { border-color: #999; }
        }
    </style>
</head>
<body>
    <div class="barcode-grid">
        @forelse($barcodes as $barcode)
            <div class="barcode-item">
                <div class="product-name">{{ $barcode['name'] }}</div>
                {!! $barcode['barcode_svg'] !!}
                <div class="barcode-number">{{ $barcode['barcode_value'] }}</div>
                @if(!empty($barcode['sku']))
                    <div class="sku-text">SKU: {{ $barcode['sku'] }}</div>
                @endif
            </div>
        @empty
            <p style="grid-column: 1/-1; text-align: center; padding: 20px; color: #999;">No barcodes to print.</p>
        @endforelse
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
