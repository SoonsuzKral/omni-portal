@props(['name' => ''])

@php
    if (empty($name)) return;

    $testMode = \Illuminate\Support\Facades\Cache::remember('ads_test_mode', 60, function () {
        return (bool) \App\Models\LiveDataVault::where('key', 'ads_test_mode')->value('value');
    });

    if ($testMode) { echo '<div style="background:red;color:white;padding:10px;margin:4px 0;font-weight:bold;">DEBUG: Test Modu Aktif (Slot: ' . e($name) . ')</div>'; }
@endphp

@php
    $positionMap = [
        'sidebar-right-top'     => 'right_sidebar_top',
        'sidebar-right-mid'     => 'right_sidebar_mid',
        'sidebar-right-bottom'  => 'right_sidebar_bottom',
        'sidebar-left-top'      => 'left_sidebar_top',
        'sidebar-left-mid'      => 'left_sidebar_mid',
        'sidebar-left-bottom'   => 'left_sidebar_bottom',
        'content-top'           => 'above_content',
        'content-bottom'        => 'below_content',
        'after-h1'              => 'under_h1',
        'after-breadcrumb'      => 'after_breadcrumb',
        'above-footer'          => 'above_footer',
        'below-footer'          => 'below_footer',
        'header-left'           => 'header_left',
        'header-right'          => 'header_right',
        'footer-left'           => 'footer_left',
        'footer-right'          => 'footer_right',
        'sticky-bottom'         => 'sticky_bottom',
        'sticky-left'           => 'sticky_left',
        'sticky-right'          => 'sticky_right',
        'mid-content-1'         => 'mid_content_1',
        'mid-content-2'         => 'mid_content_2',
        'mid-content-3'         => 'mid_content_3',
    ];

    $position = $positionMap[$name] ?? $name;

    $isSidebar = in_array($position, [
        'left_sidebar_top', 'left_sidebar_mid', 'left_sidebar_bottom',
        'right_sidebar_top', 'right_sidebar_mid', 'right_sidebar_bottom',
    ]);

    $isSticky = in_array($position, ['sticky_bottom', 'sticky_left', 'sticky_right']);

    $containerClass = 'ad-slot my-4';
    if ($isSidebar) $containerClass .= ' ad-slot-vertical';
    if ($isSticky) $containerClass .= ' ad-slot-sticky';
    if ($position === 'sticky_bottom') $containerClass .= ' fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-900 shadow-lg p-2';
    if ($position === 'sticky_left') $containerClass .= ' fixed left-0 top-1/2 -translate-y-1/2 z-50 hidden lg:block';
    if ($position === 'sticky_right') $containerClass .= ' fixed right-0 top-1/2 -translate-y-1/2 z-50 hidden lg:block';

    if ($testMode) {
        $containerClass .= ' ad-test-box';
    }

    $scripts = [];
    if (!$testMode) {
        $scripts = \Illuminate\Support\Facades\Cache::remember('ad_slot_' . $position, 3600, function () use ($position) {
            return \App\Models\GlobalAdBlock::where('active', 1)
                ->where('position', $position)
                ->pluck('script')
                ->toArray();
        });
    }
@endphp

@if($testMode)
    <div id="ad-slot-{{ str_replace('_', '-', $name) }}" class="{{ $containerClass }}">
        <span class="ad-test-box-label">TEST REKLAMI: {{ $name }}</span>
    </div>
@elseif(!empty($scripts))
    <div id="ad-slot-{{ str_replace('_', '-', $name) }}" class="{{ $containerClass }}">
        @foreach($scripts as $script)
            {!! $script !!}
        @endforeach
    </div>
@endif
