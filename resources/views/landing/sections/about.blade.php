<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            @if($section->title)
                <h2 class="text-4xl font-bold text-gray-900 mb-6">{{ $section->title }}</h2>
            @endif
            <div class="prose prose-lg text-gray-600 rich-text">
                {!! $section->content !!}
            </div>
        </div>
        @if($section->image)
            <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                <img src="{{ Voyager::image($section->image) }}" alt="{{ $section->title }}" class="w-full h-auto object-cover">
            </div>
        @endif
    </div>
</div>
