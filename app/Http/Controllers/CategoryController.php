<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    public function index_no_locale(Category $category)
    {
        return $this->index($category);
    }

    public function index_locale($locale = null, Category $category)
    {
        return $this->index($category);
    }

    private function index(Category $category)
    {
        //dd(app()->getLocale());
        $products = Product::Where('category_id', $category->id)->orderBy('id', 'desc')->get();
        return view('categories.index', compact('products', 'category'));
    }
}
