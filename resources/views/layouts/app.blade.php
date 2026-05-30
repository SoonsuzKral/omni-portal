<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9882423372138514" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', '')">
    <link rel="canonical" href="@yield('canonical', request()->url())">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @if(config('app.gsc_verification'))
    <meta name="google-site-verification" content="{{ config('app.gsc_verification') }}" />
    @endif

    <meta property="og:title" content="@yield('og_title', config('app.name'))" />
    <meta property="og:description" content="@yield('og_description', '')" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="@yield('og_image', asset('og-default.svg'))" />
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}_{{ strtoupper(app()->getLocale()) }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />

    @foreach(config('app.supported_locales', ['tr', 'en', 'ru', 'ar']) as $locale)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ url('/') }}/lang/{{ $locale }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        };

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('theme-toggle');
            if (toggle) {
                toggle.addEventListener('click', function () {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                    }
                });
            }
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .ad-sidebar .ad-zone-container {
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ad-sidebar ins.adsbygoogle {
            min-width: 120px;
            min-height: 240px;
        }
        [dir="rtl"] .ad-sidebar-left { order: 2; }
        [dir="rtl"] .ad-sidebar-right { order: 0; }
        .ad-layout-wrapper {
            flex-direction: row;
        }
        @media (max-width: 1023px) {
            .ad-layout-wrapper {
                flex-direction: column;
            }
        }
    </style>

    @stack('head')

    @php
        $adClient = config('services.adsense.ad_client');
        $adsenseEnabled = config('services.adsense.enabled') && !empty($adClient);

        $adsenseVerificationEnabled = \Illuminate\Support\Facades\Cache::remember('adsense_verification_enabled', 3600, function () {
            $vault = \App\Models\LiveDataVault::where('key', 'adsense_verification_enabled')->first();
            return $vault ? (bool) $vault->value : false;
        });

        $gaId = config('services.google_analytics.measurement_id');
    @endphp

    @if($adsenseEnabled || $adsenseVerificationEnabled)
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adClient ?? 'ca-pub-9882423372138514' }}" crossorigin="anonymous"></script>
    @endif

    @if($adsenseEnabled)
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "{{ $adClient }}",
            enable_page_level_ads: true
        });
    </script>
    @endif

    @php
        $trackingCodes = \Illuminate\Support\Facades\Cache::remember('global_tracking_codes', 3600, function () {
            return \App\Models\GlobalAdBlock::where('active', 1)
                ->whereIn('position', ['ga4_tracking', 'clarity_tracking'])
                ->pluck('script', 'position')
                ->toArray();
        });
    @endphp

    @if(!empty($trackingCodes['ga4_tracking']))
        {!! $trackingCodes['ga4_tracking'] !!}
    @elseif(!empty($gaId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif

    @if(!empty($trackingCodes['clarity_tracking']))
        {!! $trackingCodes['clarity_tracking'] !!}
    @endif

    @php
        $headScripts = \Illuminate\Support\Facades\Cache::remember('global_head_scripts', 3600, function () {
            return \App\Models\GlobalAdBlock::where('active', 1)
                ->where('position', 'head_script')
                ->pluck('script')
                ->toArray();
        });
    @endphp

    @foreach($headScripts as $headScript)
        {!! $headScript !!}
    @endforeach
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 font-['Poppins',_sans-serif] antialiased transition-colors duration-300">

    <x-ad-renderer position="global_header" />

    <nav class="bg-white dark:bg-slate-800 shadow-md dark:shadow-slate-900/50 sticky top-0 z-40 border-b border-gray-100 dark:border-slate-700">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="text-xl md:text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        {{ config('app.name') }}
                    </a>
                </div>

                <div class="hidden md:flex flex-1 max-w-lg mx-4 relative">
                    <form action="{{ route('search') }}" method="GET" class="flex w-full" autocomplete="off">
                        <div class="relative flex-1">
                            <input type="text" name="q" id="navbar-search" placeholder="{{ __('common.search_placeholder') }}"
                                class="search-input-field w-full px-4 py-2 pl-10 border border-gray-200 dark:border-slate-600 rounded-l-lg bg-gray-50 dark:bg-slate-700 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-400 text-gray-900 dark:text-white"
                                autocomplete="off">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="submit" class="bg-indigo-600 dark:bg-indigo-500 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="flex items-center space-x-3">
                    <x-locale-selector />

                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition" title="{{ __('common.toggle_theme') }}">
                        <svg id="sun-icon" class="w-5 h-5 text-yellow-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg id="moon-icon" class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>

                    <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700" id="mobile-menu-btn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ url('/categories') }}" class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            {{ __('common.categories') }}
                        </a>
                        <a href="{{ url('/locations') }}" class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ __('common.locations') }}
                        </a>
                        <a href="{{ route('sitemap') }}" class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            {{ __('common.sitemap') }}
                        </a>
                        <span class="text-gray-400 dark:text-gray-500">|</span>
                        <a href="{{ route('about') }}" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition text-sm">{{ __('common.about') }}</a>
                        <a href="{{ route('privacy-policy') }}" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition text-sm">{{ __('common.privacy') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3">
            <form action="{{ route('search') }}" method="GET" class="flex mb-3" autocomplete="off">
                <input type="text" name="q" id="mobile-search-input" placeholder="{{ __('common.search_placeholder') }}"
                    class="search-input-field flex-1 px-3 py-2 border border-gray-200 dark:border-slate-600 rounded-l-lg bg-gray-50 dark:bg-slate-700 text-gray-900 dark:text-white"
                    autocomplete="off">
                <button type="submit" class="bg-indigo-600 text-white px-4 rounded-r-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
            <div class="flex flex-col space-y-2">
                <a href="{{ url('/categories') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-2 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    {{ __('common.categories') }}
                </a>
                <a href="{{ url('/locations') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-2 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ __('common.locations') }}
                </a>
                <a href="{{ route('sitemap') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-2 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    {{ __('common.sitemap') }}
                </a>
                <a href="{{ route('about') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-2 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('common.about') }}
                </a>
                <a href="{{ route('privacy-policy') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-2 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    {{ __('common.privacy') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex gap-4 lg:gap-6 ad-layout-wrapper">
            <aside class="ad-sidebar ad-sidebar-left hidden xl:block w-[160px] shrink-0">
                <div class="sticky top-20 space-y-6">
                    <x-ad-renderer position="left_sidebar_top" />
                    <x-ad-renderer position="left_sidebar_mid" />
                    <x-ad-renderer position="left_sidebar_bottom" />
                </div>
            </aside>

            <main class="flex-1 min-w-0">
                <x-ad-renderer position="above_content" />
                @yield('content')
                <x-ad-renderer position="below_content" />
            </main>

            <aside class="ad-sidebar ad-sidebar-right hidden lg:block w-[160px] xl:w-[200px] shrink-0">
                <div class="sticky top-20 space-y-6">
                    <x-ad-renderer position="right_sidebar_top" />
                    <x-ad-renderer position="right_sidebar_mid" />
                    <x-ad-renderer position="right_sidebar_bottom" />
                </div>
            </aside>
        </div>
    </div>

    @php $footerSlot = config('services.adsense.footer_slot'); @endphp
    @if($adsenseEnabled && !empty($footerSlot))
    <div class="text-center my-4 adsense-ad-unit" style="min-height:90px;">
        <ins class="adsbygoogle"
             style="display:block; text-align:center;"
             data-ad-client="{{ $adClient }}"
             data-ad-slot="{{ $footerSlot }}"
             data-ad-format="horizontal"
             data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    @endif

    <x-ad-renderer position="above_footer" />
    <x-ad-renderer position="global_footer" />

    <footer class="bg-gray-900 dark:bg-slate-950 text-gray-300 dark:text-gray-400 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">{{ config('app.name') }}</h3>
                    <p class="text-sm">{{ config('app.name') }} - {{ __('common.discover_your_city') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('common.quick_links') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/') }}" class="flex items-center gap-1.5 hover:text-indigo-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            {{ __('common.home') }}</a></li>
                        <li><a href="{{ url('/categories') }}" class="flex items-center gap-1.5 hover:text-indigo-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            {{ __('common.categories') }}</a></li>
                        <li><a href="{{ url('/locations') }}" class="flex items-center gap-1.5 hover:text-indigo-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ __('common.locations') }}</a></li>
                        <li><a href="{{ route('sitemap') }}" class="flex items-center gap-1.5 hover:text-indigo-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            {{ __('common.sitemap') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('common.api') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/api/health') }}" class="hover:text-indigo-400 transition">API Health</a></li>
                        <li><span class="text-gray-500">Ingest: /api/v1/ingest</span></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('common.tools') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('search') }}" class="hover:text-indigo-400 transition">{{ __('common.search') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('common.legal') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('privacy-policy') }}" class="hover:text-indigo-400 transition">{{ __('common.privacy') }}</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-indigo-400 transition">{{ __('common.terms') }}</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-indigo-400 transition">{{ __('common.about') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-indigo-400 transition">{{ __('common.contact') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('common.all_rights_reserved') }}
                <span class="mx-2">|</span>
                <span>{{ __('common.built_with') }}</span>
            </div>
        </div>
    </footer>

    @stack('scripts')

    @vite(['resources/js/app.js'])
    <x-adblock-modal />

</body>
</html>
