<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>

    <div style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
        <div style="display: flex; gap: 30px; align-items: flex-start;">

            {{-- Slika lijevo --}}
            @if($post->image)
                <div style="width: 40%;">
                    <img src="{{ asset('storage/' . $post->image) }}" alt="Slika posta" style="width: 100%; border-radius: 8px;">
                </div>
            @endif

            {{-- Tekst desno --}}
            <div style="width: 60%;">
                <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                    {{ $post->content }}
                </p>
                <p style="font-size: 14px; color: #888;">Autor: {{ $post->user->name }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
