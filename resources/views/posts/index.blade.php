<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Blog's
        </h2>
    </x-slot>
    <br>
<div class="mb-6 text-right">
    <a href="{{ route('posts.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        New post
    </a>
</div>

    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        @foreach ($posts as $post)
        @if($post->image)
    <img src="{{ asset('storage/' . $post->image) }}" alt="Slika" class="mb-4 w-full max-w-xs">
@endif

        @if(Auth::id() === $post->user_id)
    <a href="{{ route('posts.edit', $post->id) }}" class="text-yellow-600 hover:underline ml-4">
        Edit
    </a>
@endif
@if(Auth::id() === $post->user_id)
    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Da li si sigurna da želiš obrisati ovaj post?')" class="inline-block ml-4">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:underline">
            Delete
        </button>
    </form>
@endif

            <div class="bg-white shadow p-6 rounded-lg mb-6">
                <h2 class="text-2xl font-semibold mb-2">{{ $post->title }}</h2>
                <p class="text-gray-700">{{ Str::limit($post->content, 150) }}</p>
                <p class="text-sm text-gray-500 mt-2">Autor: {{ $post->user->name }}</p>
            </div>
        @endforeach
    </div>
</x-app-layout>
