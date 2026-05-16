<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
    @if($section->title)
        <h2 class="text-4xl font-bold text-gray-900 mb-8">{{ $section->title }}</h2>
    @endif
    
    @if($section->image)
        <div class="mb-10 rounded-xl overflow-hidden shadow-lg inline-block">
            <img src="{{ Voyager::image($section->image) }}" alt="{{ $section->title }}" class="w-full max-w-2xl h-auto">
        </div>
    @endif

    <div class="prose prose-lg mx-auto text-gray-600 rich-text">
        {!! $section->content !!}
    </div>
</div>
