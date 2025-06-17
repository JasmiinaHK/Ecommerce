<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->name }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Back to Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="md:flex gap-8">
                        <!-- Product Images -->
                        <div class="md:w-1/2">
                            <div class="mb-4">
                                @if($product->primaryImage)
                                    <img 
                                        src="{{ asset('storage/' . $product->primaryImage->path) }}" 
                                        alt="{{ $product->name }}"
                                        class="w-full h-auto rounded-lg shadow-md"
                                    >
                                @else
                                    <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-400">No image available</span>
                                    </div>
                                @endif
                            </div>
                            @if($product->images->count() > 0)
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($product->images as $image)
                                        <div class="border rounded overflow-hidden">
                                            <img 
                                                src="{{ asset('storage/' . $image->path) }}" 
                                                alt="{{ $product->name }}"
                                                class="w-full h-20 object-cover cursor-pointer hover:opacity-75"
                                                onclick="document.querySelector('.main-image').src = this.src"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="md:w-1/2 mt-6 md:mt-0">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                            
                            <div class="mt-4">
                                @if($product->category)
                                    <span class="text-indigo-600 text-sm font-medium">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4">
                                <div class="flex items-center">
                                    <span class="text-3xl font-bold text-gray-900">
                                        {{ number_format($product->price, 2) }} €
                                    </span>
                                    @if($product->compare_at_price > $product->price)
                                        <span class="ml-3 text-lg text-gray-500 line-through">
                                            {{ number_format($product->compare_at_price, 2) }} €
                                        </span>
                                        <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            {{ round(($product->compare_at_price - $product->price) / $product->compare_at_price * 100) }}% OFF
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6">
                                <p class="text-gray-700">
                                    {{ $product->description }}
                                </p>
                            </div>

                            <div class="mt-8">
                                <div class="flex items-center">
                                    <span class="text-gray-700 mr-4">Quantity:</span>
                                    <div class="flex items-center border border-gray-300 rounded-md">
                                        <button type="button" class="px-3 py-1 text-lg" onclick="decreaseQuantity()">-</button>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->quantity_available }}" 
                                            class="w-16 text-center border-0 focus:ring-0">
                                        <button type="button" class="px-3 py-1 text-lg" onclick="increaseQuantity({{ $product->quantity_available }})">+</button>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ $product->quantity_available }} available
                                    </span>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap gap-4">
                                <form action="{{ route('cart.add', $product) }}" method="POST" class="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="quantity" id="cart-quantity" value="1">
                                    <button type="submit" 
                                            class="flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2m13.6 0l.4-2H5.4M7 16h10m0 0a2 2 0 100 4 2 2 0 000-4z"></path>
                                        </svg>
                                        Add to Cart
                                    </button>
                                </form>

                                <form action="{{ route('favorites.toggle', $product) }}" method="POST" class="favorite-form">
                                    @csrf
                                    <button type="button" 
                                            class="favorite-btn flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-8"
                                            data-product-id="{{ $product->id }}"
                                            data-is-favorite="{{ auth()->user() && auth()->user()->favorites->contains($product->id) ? 'true' : 'false' }}">
                                        <svg class="h-5 w-5 mr-2 favorite-icon" 
                                             fill="{{ auth()->user() && auth()->user()->favorites->contains($product->id) ? 'red' : 'none' }}" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24" 
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span class="favorite-text">
                                            {{ auth()->user() && auth()->user()->favorites->contains($product->id) ? 'Remove from Favorites' : 'Add to Favorites' }}
                                        </span>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-8 border-t border-gray-200 pt-6">
                                <h3 class="text-sm font-medium text-gray-900">Product Details</h3>
                                <div class="mt-4">
                                    <ul role="list" class="pl-4 list-disc text-sm space-y-2">
                                        @if($product->sku)
                                            <li><span class="text-gray-600">SKU:</span> {{ $product->sku }}</li>
                                        @endif
                                        <li><span class="text-gray-600">Availability:</span> 
                                            @if($product->in_stock)
                                                <span class="text-green-600">In Stock ({{ $product->quantity_available }})</span>
                                            @else
                                                <span class="text-red-600">Out of Stock</span>
                                            @endif
                                        </li>
                                        <li><span class="text-gray-600">Category:</span> {{ $product->category?->name ?? 'N/A' }}</li>
                                        @if($product->weight)
                                            <li><span class="text-gray-600">Weight:</span> {{ $product->weight }} kg</li>
                                        @endif
                                        @if($product->dimensions)
                                            <li><span class="text-gray-600">Dimensions:</span> {{ $product->dimensions }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Handle favorite button click
        document.addEventListener('DOMContentLoaded', function() {
            const favoriteBtns = document.querySelectorAll('.favorite-btn');
            
            favoriteBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const form = this.closest('form');
                    const productId = this.dataset.productId;
                    const isFavorite = this.dataset.isFavorite === 'true';
                    const icon = this.querySelector('.favorite-icon');
                    const text = this.querySelector('.favorite-text');
                    
                    // Show loading state
                    this.disabled = true;
                    
                    // Send AJAX request
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ _token: '{{ csrf_token() }}' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update button state
                            this.dataset.isFavorite = (!isFavorite).toString();
                            
                            // Update icon
                            if (data.action === 'added') {
                                icon.setAttribute('fill', 'red');
                                text.textContent = 'Remove from Favorites';
                            } else {
                                icon.setAttribute('fill', 'none');
                                text.textContent = 'Add to Favorites';
                            }
                            
                            // Update favorites count in header if exists
                            const favoritesCountEl = document.querySelector('.favorites-count');
                            if (favoritesCountEl) {
                                favoritesCountEl.textContent = data.favorites_count;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
                });
            });
        });
        
        // Količina
        function updateQuantity() {
            const quantityInput = document.getElementById('quantity');
            const cartQuantityInput = document.getElementById('cart-quantity');
            cartQuantityInput.value = quantityInput.value;
        }

        function increaseQuantity(max) {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) < max) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
                updateQuantity();
            }
        }

        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
                updateQuantity();
            }
        }

        document.getElementById('quantity').addEventListener('change', updateQuantity);

        // AJAX za favorite
        document.querySelector('.favorite-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                const button = this.querySelector('button');
                const svg = this.querySelector('svg');
                const text = this.querySelector('button span:last-child');
                
                if (data.status === 'added') {
                    svg.setAttribute('fill', 'red');
                    text.textContent = 'Remove from Favorites';
                } else {
                    svg.setAttribute('fill', 'none');
                    text.textContent = 'Add to Favorites';
                }
                
                // Ažuriraj broj favorita u navigaciji ako postoji
                const favoritesCount = document.querySelector('.favorites-count');
                if (favoritesCount) {
                    fetch('{{ route("favorites.count") }}')
                        .then(response => response.json())
                        .then(data => {
                            favoritesCount.textContent = data.count;
                        });
                }
            });
        });

        // AJAX za dodavanje u košaricu
        document.querySelector('.add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ažuriraj broj proizvoda u košarici u navigaciji
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Pokaži obavijest
                    alert('Product added to cart!');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
