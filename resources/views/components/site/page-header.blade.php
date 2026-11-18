@props(['title', 'image' => null])

<section @class([
    'relative isolate overflow-hidden',
    'bg-stone-900' => $image,
    'border-b border-stone-200 bg-stone-50' => ! $image,
])>
    @if ($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-stone-950/80 to-stone-950/30"></div>
    @endif

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <h1 @class(['max-w-3xl text-4xl font-bold sm:text-5xl', 'text-white' => $image])>{{ $title }}</h1>
        @if ($slot->isNotEmpty())
            <p @class(['mt-5 max-w-2xl text-lg leading-relaxed', 'text-stone-200' => $image, 'text-stone-600' => ! $image])>{{ $slot }}</p>
        @endif
    </div>
</section>
