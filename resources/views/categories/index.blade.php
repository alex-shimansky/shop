@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
    <main class="flex-1 py-10 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @isset($category)
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                {{ $category->name }}
            </h2>
        @endisset

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <div class="bg-white border rounded-lg shadow p-4 flex flex-col">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="" class="rounded mb-4">
                    <h3 class="text-lg font-semibold mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-700 font-bold mb-4">{{ $product->price }} {{ __('messages.hrn') }}</p>

                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="mt-auto bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                            {{ __('messages.buy') }}
                        </button>
                    </form>
                </div>
            @endforeach
            </div>
        </div>
    </main>
</div>
@endsection