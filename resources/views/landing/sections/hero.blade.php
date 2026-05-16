<div class="relative bg-blue-600 text-white min-h-[70vh] flex items-center justify-center bg-cover bg-center" 
    @if($section->image) style="background-image: url('{{ Voyager::image($section->image) }}');" @endif>
    
    <!-- Overlay si hay imagen -->
    @if($section->image)
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
    @endif

    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto py-20">
        @if($section->title)
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6">{{ $section->title }}</h1>
        @endif
        @if($section->content)
            <div class="text-xl md:text-2xl font-light mb-10 rich-text">
                {!! $section->content !!}
            </div>
        @endif
        <a href="#section-{{ $section->id + 1 }}" class="inline-block bg-white text-blue-600 font-semibold px-8 py-4 rounded-full shadow-lg hover:bg-gray-50 transition-colors">
            Descubrir Más
        </a>
    </div>
</div>
