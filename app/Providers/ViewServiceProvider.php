<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class ViewServiceProvider extends ServiceProvider
{
    
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $categories = Category::orderBy('id', 'desc')->get();
            $view->with('categories', $categories);

            $locale = app()->getLocale();
            if ($locale == config('app.locale_def')) $prefix_url = '/';
            else if (in_array($locale, config('app.locales'))) $prefix_url = '/'.$locale.'/';
            else $prefix_url = '/';

            $view->with('prefix_url', $prefix_url);
        });
    }
}