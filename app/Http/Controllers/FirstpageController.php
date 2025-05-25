<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class FirstpageController extends Controller
{
    public function index()
    {
        //dd(app()->getLocale());
        $products = Product::orderBy('id', 'desc')->get();
        return view('firstpage.index', compact('products'));
    }
}
