<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Favorites
            </h2>
            <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-900">
                Continue Shopping &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($favorites->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($favorites as $favorite)
                        @php $product = $favorite->product; @endphp
                        @if($product)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <a href="{{ route('products.show', $product->id) }}" class="block">
                                @if($product->primaryImage && $product->primaryImage->image_path)
                                    <img 
                                        src="{{ asset('storage/' . $product->primaryImage->image_path) }}" 
                                        alt="{{ $product->name }}"
                                        class="w-full h-48 object-cover"
                                    >
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400">No image</span>
                                    </div>
                                @endif
                            </a>
                            
                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg">
                                            <a href="{{ route('products.show', $product->id) }}" class="hover:text-indigo-600">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        @if($product->category && $product->category->is_active)
                                            <p class="text-gray-600 text-sm mt-1">
                                                {{ $product->category->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-lg">{{ number_format($product->price, 2) }} €</span>
                                        @if(isset($product->sale_price) && $product->sale_price > 0 && $product->sale_price < $product->price)
                                            <div class="text-sm text-gray-500 line-through">
                                                {{ number_format($product->price, 2) }} €
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-between items-center">
                                    <form action="{{ route('favorites.toggle', $product) }}" method="POST" class="favorite-form">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 focus:outline-none">
                                            <svg class="h-6 w-6" fill="red" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </button>
                                    </form>

                                    @if($product->quantity > 0)
                                        <form action="{{ route('cart.add', $product) }}" method="POST" class="add-to-cart-form">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 flex items-center add-to-cart-btn" 
                                                    data-product-id="{{ $product->id }}">
                                                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                <span class="btn-text">Add to Cart</span>
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-red-600 text-sm">Out of Stock</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($favorites->hasPages())
                    <div class="mt-8">
                        {{ $favorites->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Your favorites list is empty</h3>
                    <p class="mt-1 text-gray-500">Add some products to your favorites to see them here.</p>
                    <div class="mt-6">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Browse Products
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Add to cart functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle add to cart form submission
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const form = this;
                    const button = form.querySelector('button[type="submit"]');
                    const buttonText = button.querySelector('.btn-text');
                    const spinner = button.querySelector('svg.animate-spin');
                    
                    // Show loading state
                    button.disabled = true;
                    buttonText.classList.add('opacity-0');
                    spinner.classList.remove('hidden');
                    
                    // Get form data
                    const formData = new FormData(form);
                    const url = form.getAttribute('action');
                    
                    // Send AJAX request
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            const successMessage = document.createElement('div');
                            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
                            successMessage.textContent = data.message || 'Product added to cart';
                            document.body.appendChild(successMessage);
                            
                            // Update cart count in the header if exists
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount && data.cart) {
                                cartCount.textContent = data.cart.item_count;
                            }
                            
                            // Remove success message after 3 seconds
                            setTimeout(() => {
                                successMessage.remove();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while adding the product to cart');
                    })
                    .finally(() => {
                        // Reset button state
                        button.disabled = false;
                        buttonText.classList.remove('opacity-0');
                        spinner.classList.add('hidden');
                    });
                });
            });
        });
        
        // AJAX za uklanjanje iz favorita
        document.querySelectorAll('.favorite-form').forEach(form => {
            form.addEventListener('submit', function(e) {
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
                    if (data.status === 'removed') {
                        // Ukloni proizvod iz prikaza
                        this.closest('.bg-white').remove();
                        
                        // Ako nema više proizvoda, prikaži poruku
                        if (document.querySelectorAll('.bg-white').length === 0) {
                            window.location.reload();
                        }
                        
                        // Ažuriraj broj favorita u navigaciji
                        const favoritesCount = document.querySelector('.favorites-count');
                        if (favoritesCount) {
                            fetch('{{ route("favorites.count") }}')
                                .then(response => response.json())
                                .then(data => {
                                    favoritesCount.textContent = data.count;
                                });
                        }
                    }
                });
            });
        });

        // AJAX za dodavanje u košaricu
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
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
        });
    </script>
    @endpush
</x-app-layout>
