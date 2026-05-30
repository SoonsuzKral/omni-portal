@if(!empty($scripts))
    <div id="ad-zone-{{ $position }}-{{ uniqid() }}" class="ad-zone-container my-4">
        @foreach($scripts as $script)
            {!! $script !!}
        @endforeach
    </div>
@endif
