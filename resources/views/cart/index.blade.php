<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shopping Cart
            </h2>
            <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-900">
                Continue Shopping &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($cart && $cart->items->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Cart Items -->
                            <div class="md:w-2/3">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Your Items ({{ $cart->items->count() }})</h3>
                                <div class="space-y-6">
                                    @foreach($cart->items as $item)
                                        <div class="flex flex-col sm:flex-row border-b border-gray-200 pb-6">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $item->product->primaryImage ? asset('storage/' . $item->product->primaryImage->path) : 'https://via.placeholder.com/150' }}" 
                                                     alt="{{ $item->product->name }}" 
                                                     class="w-full h-32 object-cover rounded-md sm:w-32">
                                            </div>
                                            <div class="mt-4 sm:mt-0 sm:ml-6 flex-1">
                                                <div class="flex justify-between">
                                                    <h4 class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('products.show', $item->product->id) }}" class="hover:text-indigo-600">
                                                            {{ $item->product->name }}
                                                        </a>
                                                    </h4>
                                                    <form action="{{ route('cart.remove', $item) }}" method="POST" class="remove-item-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-500">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500">{{ $item->product->category->name ?? 'N/A' }}</p>
                                                <div class="mt-2">
                                                    <span class="font-medium text-gray-900">{{ number_format($item->product->price, 2) }} €</span>
                                                    @if($item->product->compare_at_price > $item->product->price)
                                                        <span class="ml-2 text-sm text-gray-500 line-through">{{ number_format($item->product->compare_at_price, 2) }} €</span>
                                                    @endif
                                                </div>
                                                <div class="mt-4 flex items-center">
                                                    <form action="{{ route('cart.update', $item) }}" method="POST" class="update-quantity-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <label for="quantity-{{ $item->id }}" class="mr-2 text-sm text-gray-700">Quantity:</label>
                                                        <div class="flex items-center border border-gray-300 rounded-md">
                                                            <button type="button" class="px-2 py-1 text-gray-600 hover:bg-gray-100" 
                                                                    onclick="updateQuantity('{{ $item->id }}', -1)">-</button>
                                                            <input type="number" 
                                                                   id="quantity-{{ $item->id }}" 
                                                                   name="quantity" 
                                                                   value="{{ $item->quantity }}" 
                                                                   min="1" 
                                                                   max="{{ $item->product->quantity_available }}" 
                                                                   class="w-16 text-center border-0 focus:ring-0">
                                                            <button type="button" class="px-2 py-1 text-gray-600 hover:bg-gray-100" 
                                                                    onclick="updateQuantity('{{ $item->id }}', 1)">+</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6">
                                    <form action="{{ route('cart.clear') }}" method="POST" class="clear-cart-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500">
                                            Clear shopping cart
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="md:w-1/3 mt-8 md:mt-0">
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Subtotal</span>
                                            <span class="font-medium">{{ number_format($cart->subtotal, 2) }} €</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Shipping</span>
                                            <span class="font-medium">
                                                @if($cart->subtotal > 50)
                                                    <span class="text-green-600">Free</span>
                                                @else
                                                    5.00 €
                                                @endif
                                            </span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4 flex justify-between">
                                            <span class="text-base font-medium text-gray-900">Total</span>
                                            <span class="text-base font-medium text-gray-900">
                                                {{ number_format($cart->subtotal + ($cart->subtotal > 50 ? 0 : 5), 2) }} €
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('checkout') }}" 
                                           class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                            Proceed to Checkout
                                        </a>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <p class="text-sm text-gray-500">
                                            or 
                                            <a href="{{ route('products.index') }}" class="text-indigo-600 font-medium hover:text-indigo-500">
                                                Continue Shopping<span aria-hidden="true"> &rarr;</span>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Your cart is empty</h3>
                    <p class="mt-1 text-gray-500">Start adding some items to your cart.</p>
                    <div class="mt-6">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Ažuriranje količine
        function updateQuantity(itemId, change) {
            const input = document.getElementById(`quantity-${itemId}`);
            let newValue = parseInt(input.value) + change;
            
            // Provjeri minimalnu i maksimalnu količinu
            if (newValue < 1) newValue = 1;
            if (newValue > parseInt(input.max)) newValue = parseInt(input.max);
            
            input.value = newValue;
            
            // Automatski pošalji formu nakon promjene
            if (newValue !== parseInt(input.defaultValue)) {
                document.querySelector(`#quantity-${itemId}`).defaultValue = newValue;
                document.querySelector(`#quantity-${itemId}`).form.requestSubmit();
            }
        }

        // AJAX za ažuriranje količine
        document.querySelectorAll('.update-quantity-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                fetch(this.action, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(this))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Osvježi stranicu da se prikažu ažurirani podaci
                        window.location.reload();
                    }
                });
            });
        });

        // AJAX za uklanjanje stavke
        document.querySelectorAll('.remove-item-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    fetch(this.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Osvježi stranicu da se prikažu ažurirani podaci
                            window.location.reload();
                        }
                    });
                }
            });
        });

        // AJAX za čišćenje košarice
        document.querySelector('.clear-cart-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear your cart?')) {
                fetch(this.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Osvježi stranicu da se prikažu ažurirani podaci
                        window.location.reload();
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
