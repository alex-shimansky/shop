<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Shop') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        @stack('styles')
    </head>
    <body>
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="space-x-2">
                @php
                    $locales = ['uk' => '', 'en' => 'en', 'es' => 'es'];
                    $current = request()->segment(1);
                    $currentLocale = in_array($current, ['en', 'es']) ? $current : 'uk';
                    $path = request()->path();
                    $pathWithoutLocale = preg_replace('#^(en|es)(/)?#', '', $path);
                @endphp

                @foreach ($locales as $key => $prefix)
                    <a href="{{ url($prefix ? '/'.$prefix.'/'.$pathWithoutLocale : '/'.$pathWithoutLocale) }}"
                    class="{{ $currentLocale === $key ? 'font-bold text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ strtoupper($key) }}
                    </a>
                @endforeach
            </div>

                <div class="flex items-center space-x-8">
                    <a href="{{ $prefix_url }}" class="text-xl font-bold text-gray-800">{{ __('messages.site_name') }}</a>
                    <nav class="space-x-4 md:block">
                        @foreach ($categories as $category)
                        <a href="{{ localized_route('categories.index', ['category' => $category->id]) }}" class="text-gray-600 hover:text-gray-900">{{ $category->name }}</a>
                        @endforeach
                    </nav>
                </div>
                <div class="space-x-4">
                    @auth
                    <a href="{{ localized_route('orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('messages.my_orders') }}</a>
                    @else
                    <a href="{{ localized_route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Войти</a>
                    <a href="{{ localized_route('register') }}" class="text-sm text-gray-600 hover:text-gray-900">Регистрация</a>
                    @endauth
                    @php
                        $cartItemCount = session('cart') ? collect(session('cart'))->sum('quantity') : 0;
                    @endphp
                    <a href="{{ localized_route('cart.index') }}" class="text-sm hover:text-gray-900 {{ $cartItemCount > 0 ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">
                        {{ __('messages.basket') }}
                        @if ($cartItemCount > 0)
                            [{{ $cartItemCount }}]
                        @endif
                    </a>
                </div>
            </div>
        </header>

        @yield('content')

        <footer class="bg-white shadow mt-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 text-sm text-gray-600">
                @foreach ($categories as $category)
                <a href="{{ localized_route('categories.index', ['category' => $category->id]) }}" class="hover:text-gray-900">{{ $category->name }}</a>
                @endforeach
            </div>
        </footer>

        @stack('modals')

        @livewireScripts
    </body>
</html>
