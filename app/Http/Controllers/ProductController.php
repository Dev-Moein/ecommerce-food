<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $randomproduct = Product::where('quantity', '>', 0)->where('status', 1)->get()->random(4);
        return view('products.show',compact('product','randomproduct'));
    }
}
