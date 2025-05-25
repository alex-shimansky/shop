<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1); // получаем первую часть URL

        if (!in_array($locale, config('app.locales'))) {
            $locale = config('app.locale_def');
        }
        
        app()->setLocale($locale);

        return $next($request);
    }
}
