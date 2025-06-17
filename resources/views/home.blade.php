<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome to Our Store') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="bg-indigo-700 rounded-lg shadow-xl overflow-hidden lg:grid lg:grid-cols-2 lg:gap-4">
                <div class="pt-10 pb-12 px-6 sm:pt-16 sm:px-16 lg:py-16 lg:pr-0 xl:py-20 xl:px-20">
                    <div class="lg:self-center">
                        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                            <span class="block">New Arrivals</span>
                            <span class="block text-indigo-200">Discover our latest collection</span>
                        </h2>
                        <p class="mt-4 text-lg leading-6 text-indigo-200">
                            Explore our handpicked selection of premium products designed to elevate your lifestyle.
                        </p>
                        <a href="{{ route('products.index') }}" class="mt-8 bg-white border border-transparent rounded-md shadow px-5 py-3 inline-flex items-center text-base font-medium text-indigo-600 hover:bg-indigo-50">
                            Shop Now
                        </a>
                    </div>
                </div>
                <div class="-mt-6 aspect-w-5 aspect-h-3 md:aspect-w-2 md:aspect-h-1">
                    <img class="transform translate-x-6 translate-y-6 rounded-md object-cover object-left-top sm:translate-x-16 lg:translate-y-20" 
                         src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=1189&q=80" 
                         alt="New Arrivals">
                </div>
            </div>
        </div>

        <!-- Featured Categories -->
        @if($categories->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <h2 class="text-2xl font-extrabold text-gray-900 mb-6">Shop by Category</h2>
            <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                @foreach($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="group relative bg-white border border-gray-200 rounded-lg flex flex-col overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="aspect-w-3 aspect-h-2 bg-gray-200 group-hover:opacity-75 sm:aspect-none sm:h-64">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-center object-cover sm:w-full sm:h-full">
                    </div>
                    <div class="flex-1 p-4 space-y-2 flex flex-col">
                        <h3 class="text-sm font-medium text-gray-900">
                            <span aria-hidden="true" class="absolute inset-0"></span>
                            {{ $category->name }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} products</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Featured Products -->
        @if($featuredProducts->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-gray-900">Featured Products</h2>
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">View all<span aria-hidden="true"> &rarr;</span></a>
            </div>
            <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                @foreach($featuredProducts as $product)
                <div class="group relative">
                    <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                        @if($product && $product->primaryImage && $product->primaryImage->image_url)
                        <img src="{{ $product->primaryImage->image_url }}" 
                             alt="{{ $product->name ?? 'Product image' }}" 
                             class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                        @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No image available</span>
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 flex justify-between">
                        <div>
                            <h3 class="text-sm text-gray-700">
                                @if($product)
                                <a href="{{ route('products.show', $product->id) }}">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ $product->name ?? 'Unnamed Product' }}
                                </a>
                                @else
                                <span>Product not available</span>
                                @endif
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $product && $product->category ? $product->category->name : 'No category' }}
                            </p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $product ? $product->formatted_price : 'N/A' }}</p>
                    </div>
                    @if($product && $product->sale_price && $product->price && $product->sale_price < $product->price)
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-bl-lg">
                        SALE
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Latest Products -->
        @if($latestProducts->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-gray-900">Latest Products</h2>
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">View all<span aria-hidden="true"> &rarr;</span></a>
            </div>
            <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                @foreach($latestProducts as $product)
                <div class="group relative">
                    <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                        @if($product && $product->primaryImage && $product->primaryImage->image_url)
                        <img src="{{ $product->primaryImage->image_url }}" 
                             alt="{{ $product->name ?? 'Product image' }}" 
                             class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                        @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No image available</span>
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 flex justify-between">
                        <div>
                            <h3 class="text-sm text-gray-700">
                                @if($product)
                                <a href="{{ route('products.show', $product->id) }}">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ $product->name ?? 'Unnamed Product' }}
                                </a>
                                @else
                                <span>Product not available</span>
                                @endif
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $product && $product->category ? $product->category->name : 'No category' }}
                            </p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $product ? $product->formatted_price : 'N/A' }}</p>
                    </div>
                    @if($product && method_exists($product, 'isNew') && $product->isNew())
                    <div class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-bl-lg">
                        NEW
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Features -->
        <div class="bg-white">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
                <div class="bg-indigo-700 rounded-lg shadow-xl overflow-hidden lg:grid lg:grid-cols-2 lg:gap-4">
                    <div class="pt-10 pb-12 px-6 sm:pt-16 sm:px-16 lg:py-16 lg:pr-0 xl:py-20 xl:px-20">
                        <div class="lg:self-center">
                            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                                <span class="block">Ready to shop?</span>
                                <span class="block text-indigo-200">Create an account and start shopping today.</span>
                            </h2>
                            <p class="mt-4 text-lg leading-6 text-indigo-200">
                                Join thousands of satisfied customers who trust us for quality products and exceptional service.
                            </p>
                            <div class="mt-8 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                                <a href="{{ route('register') }}" class="flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 md:py-4 md:text-lg md:px-10">
                                    Sign up for free
                                </a>
                                <a href="{{ route('products.index') }}" class="flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-500 bg-opacity-60 hover:bg-opacity-70 md:py-4 md:text-lg md:px-10">
                                    Browse products
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="-mt-6 aspect-w-5 aspect-h-3 md:aspect-w-2 md:aspect-h-1">
                        <img class="transform translate-x-6 translate-y-6 rounded-md object-cover object-left-top sm:translate-x-16 lg:translate-y-20" 
                             src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1189&q=80" 
                             alt="App screenshot">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

