<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Uredi post
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        <form action="{{ route('posts.update', $post->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Naslov</label>
                <input type="text" name="title" id="title" value="{{ $post->title }}" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="content" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Sadržaj</label>
                <textarea name="content" id="content" rows="5" class="w-full border rounded px-3 py-2" required>{{ $post->content }}</textarea>
            </div>

            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
    Save
</button>

        </form>
    </div>
</x-app-layout>
