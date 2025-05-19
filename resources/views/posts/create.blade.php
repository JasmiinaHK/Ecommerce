<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dodaj novi blog post
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Naslov -->
    <div class="mb-4">
        <label for="title">Naslov</label>
        <input type="text" name="title" id="title" class="w-full border rounded px-3 py-2" required>
    </div>

    <!-- Sadržaj -->
    <div class="mb-4">
        <label for="content">Sadržaj</label>
        <textarea name="content" id="content" rows="5" class="w-full border rounded px-3 py-2" required></textarea>
    </div>

    <!-- Slika -->
    <div class="mb-4">
        <label for="image">Slika (opcionalno)</label>
        <input type="file" name="image" id="image" class="w-full border rounded px-3 py-2">
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Objavi post</button>
</form>

    </div>
</x-app-layout>
