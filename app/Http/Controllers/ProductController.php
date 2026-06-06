<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category_id', $category);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($request->boolean('low_stock')) {
            $query->where('type', 'producto')->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search', 'category', 'type'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'code' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:producto,servicio',
            'stock' => 'required_if:type,producto|integer|min:0',
            'min_stock' => 'integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'has_tax' => 'boolean',
            'tax_percentage' => 'numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:255',
            'compatible_brand' => 'nullable|string|max:255',
            'compatible_model' => 'nullable|string|max:255',
        ]);

        $validated['has_tax'] = $request->boolean('has_tax');

        if ($validated['type'] === 'servicio') {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'kardexMovements' => function ($q) {
            $q->with('user')->latest()->take(20);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'code' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:producto,servicio',
            'min_stock' => 'integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'has_tax' => 'boolean',
            'tax_percentage' => 'numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:255',
            'compatible_brand' => 'nullable|string|max:255',
            'compatible_model' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['has_tax'] = $request->boolean('has_tax');
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'type' => 'required|in:entrada,salida,ajuste',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product->adjustStock($validated['quantity'], $validated['type'], $validated['notes'] ?? null);

        return redirect()->route('products.show', $product)->with('success', 'Stock ajustado exitosamente.');
    }
}
