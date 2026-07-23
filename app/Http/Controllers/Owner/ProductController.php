<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('sort_order')->orderBy('name')->get();

        return view('owner.products.index', compact('products'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        // Unchecked checkboxes submit nothing, so validated() omits them. Coerce
        // explicitly so unticking VAT / Active actually persists as false.
        $data['vat_applicable'] = $request->boolean('vat_applicable');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return back()->with('success', 'Product created.');
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        // Unchecked checkboxes submit nothing, so validated() omits them. Coerce
        // explicitly so unticking VAT / Active actually persists as false.
        $data['vat_applicable'] = $request->boolean('vat_applicable');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orders()->exists()) {
            return back()->with('error', 'Cannot delete a product that has orders.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return back()->with('success', 'Product deleted.');
    }
}
