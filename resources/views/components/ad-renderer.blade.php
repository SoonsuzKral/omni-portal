@if(!empty($scripts))
    @php
        $isSidebar = in_array($position, [
            'left_sidebar_top', 'left_sidebar_mid', 'left_sidebar_bottom',
            'right_sidebar_top', 'right_sidebar_mid', 'right_sidebar_bottom',
            'sidebar', 'sticky_left', 'sticky_right',
        ]);
        $isSticky = in_array($position, ['sticky_bottom', 'sticky_left', 'sticky_right']);
        $containerClass = 'ad-zone-container my-4';
        if ($isSidebar) $containerClass .= ' ad-zone-vertical';
        if ($isSticky) $containerClass .= ' ad-zone-sticky';
        if ($position === 'sticky_bottom') $containerClass .= ' fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-900 shadow-lg border-t p-2';
        if ($position === 'sticky_left') $containerClass .= ' fixed left-0 top-1/2 -translate-y-1/2 z-50 hidden lg:block';
        if ($position === 'sticky_right') $containerClass .= ' fixed right-0 top-1/2 -translate-y-1/2 z-50 hidden lg:block';
    @endphp
    <div id="ad-zone-{{ str_replace('_', '-', $position) }}-{{ uniqid() }}" class="{{ $containerClass }}">
        @foreach($scripts as $script)
            {!! $script !!}
        @endforeach
    </div>
@endif
