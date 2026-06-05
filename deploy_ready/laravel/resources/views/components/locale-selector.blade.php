@php
    $currentLocale = app()->getLocale();
    $supportedLocales = [
        'tr' => 'Türkçe',
        'en' => 'English',
        'ru' => 'Русский',
        'ar' => 'العربية',
    ];
@endphp
<div class="relative group">
    <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition border border-gray-200 dark:border-slate-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ strtoupper($currentLocale) }}</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div class="absolute right-0 mt-1 w-40 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-gray-200 dark:border-slate-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
        @foreach($supportedLocales as $locale => $label)
            <a href="{{ url('/lang/' . $locale) }}"
               onclick="event.preventDefault(); window.location.href='{{ url('/lang/' . $locale) }}'"
               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 first:rounded-t-lg last:rounded-b-lg {{ $currentLocale === $locale ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-medium' : '' }}">
                <span class="inline-block w-6">{{ strtoupper($locale) }}</span>
                <span class="text-xs opacity-75">{{ $label }}</span>
            </a>
        @endforeach
    </div>
</div>
