<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\ProductStoreRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('admin.dashboard', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.product.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {

        $product = new Product();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $image->store('', 'public');
            $filePath = 'uploads/' . $fileName;
            $product->image = $filePath;

        }


        $product->name = $request->name;
        $product->price = $request->price;
        $product->short_description = $request->short_description;
        $product->qty = $request->qty;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();

        // Insert colors
        if ($request->has('colors') && $request->filled('colors')) {
            foreach ($request->colors as $key => $color) {
                ProductColor::create([
                    'product_id' => $product->id,
                    // $request->route('id'),
                    'name' => $color,
                ]);
            }
        }


        // insert images

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // store image
                $fileName = $image->store('', 'public');
                $filePath = 'uploads/' . $fileName;
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $filePath,
                ]);
            }
        }
        notyf('product create successfully.');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with('colors', 'images')->findOrFail($id);
        $colors = $product->colors->pluck('name')->toArray();
        return view('admin.product.edit', compact('product', 'colors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $product = Product::findOrFail($id);


    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'colors' => 'nullable|array',
        'colors.*' => 'string|max:50',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

    ]);

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $fileName = $image->store('', 'public');
        $filePath = 'uploads/' . $fileName;
        $product->image = $filePath;
    }

    $product->name = $request->name;
    $product->price = $request->price;
    $product->short_description = $request->short_description;
    $product->qty = $request->qty;
    $product->sku = $request->sku;
    $product->description = $request->description;
    $product->save();

    // Delete color and Insert new color
    ProductColor::where('product_id', $product->id)->delete();
    if ($request->has('colors') && $request->filled('colors')) {
        foreach ($request->colors as $color) {
            ProductColor::create([
                'product_id' => $product->id,
                'name' => $color,
            ]);
        }
    }

    // Delete image
    ProductImage::where('product_id', $product->id)->delete();
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $fileName = $image->store('', 'public');
            $filePath = 'uploads/' . $fileName;
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $filePath,
            ]);
        }
    }
        notyf('product update successfully');
    return redirect()->route('product.edit', $product->id)->with('success', 'Product updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $product->colors()->delete();

        File::delete(public_path($product->image));

        foreach ($product->images as $image) {
            File::delete(public_path($image->path));

        }
        $product->delete();

        notyf('product deleted successfully.');
        return redirect()->back();
    }

}
