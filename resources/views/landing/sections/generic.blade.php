<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    @if($section->title)
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900">{{ $section->title }}</h2>
        </div>
    @endif
    
    @if($section->image)
        <div class="mb-10 text-center">
            <img src="{{ Voyager::image($section->image) }}" alt="{{ $section->title }}" class="mx-auto rounded-xl shadow-md max-w-full h-auto">
        </div>
    @endif

    <div class="rich-text">
        {!! $section->content !!}
    </div>
</div>
