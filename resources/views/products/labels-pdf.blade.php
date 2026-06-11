<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Etiquetas - {{ $product->name }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6mm;
            page-break-inside: auto;
        }
        .label {
            border: 1px dashed #ccc;
            padding: 4mm 2mm;
            text-align: center;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 50mm;
        }
        .label .name {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2mm;
            word-break: break-all;
            line-height: 1.2;
        }
        .label .price {
            font-size: 8pt;
            color: #555;
            margin-top: 1mm;
        }
        .label .barcode-text {
            font-size: 7pt;
            margin-top: 1mm;
            letter-spacing: 1px;
        }
        .label img {
            max-width: 90%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="grid">
        @for ($i = 0; $i < $quantity; $i++)
        <div class="label">
            <div class="name">{{ $product->name }}</div>
            <img src="data:image/png;base64,{{ $barcodeBase64 }}" alt="{{ $product->barcode }}">
            <div class="barcode-text">{{ $product->barcode }}</div>
            <div class="price">${{ number_format($product->sale_price, 2) }}</div>
        </div>
        @endfor
    </div>
</body>
</html>