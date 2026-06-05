let activeInput = null;
let lastResults = [];
let selectedIndex = -1;
let debounceTimer = null;
const DEBOUNCE_MS = 300;
const MIN_LENGTH = 2;
const API_URL = '/api/v1/search/suggestions';

function escapeHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function getIcon(type) {
    switch (type) {
        case 'location':
            return { path: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10' };
        case 'category':
            return { path: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', color: 'text-indigo-500', bg: 'bg-indigo-50 dark:bg-indigo-500/10' };
        default:
            return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10' };
    }
}

function positionPortal(portal) {
    if (!portal || !activeInput) return;
    const rect = activeInput.getBoundingClientRect();
    const width = Math.max(320, Math.min(rect.width, 600));
    const left = Math.max(10, Math.min(rect.left, window.innerWidth - width - 10));
    portal.style.left = left + 'px';
    portal.style.top = (rect.bottom + 4) + 'px';
    portal.style.width = width + 'px';
}

function buildPortal() {
    const p = document.createElement('div');
    p.id = 'search-portal';
    p.style.cssText = 'position:fixed;z-index:999999;display:none;width:100%;max-width:600px;';
    p.innerHTML = `
<div class="search-portal-inner bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-200 dark:border-slate-700 overflow-hidden">
  <div class="suggestions-list divide-y divide-gray-100 dark:divide-slate-700/50"></div>
  <div class="suggestions-empty px-5 py-8 text-center hidden">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <p class="text-sm text-gray-400">No results for "<span class="suggestions-empty-query font-medium text-gray-600 dark:text-gray-300"></span>"</p>
  </div>
  <div class="suggestions-loading px-5 py-6 text-center hidden">
    <div class="flex items-center justify-center gap-2"><div class="w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div><span class="text-sm text-gray-400">Searching...</span></div>
  </div>
  <a href="#" class="suggestions-see-all flex items-center justify-center gap-2 px-5 py-3.5 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 font-medium border-t border-gray-100 dark:border-slate-700/50 hidden">See all results for "<span class="suggestions-see-all-query font-semibold"></span>" →</a>
</div>`;
    document.body.appendChild(p);
    return p;
}

function getPortal() {
    let p = document.getElementById('search-portal');
    if (!p) p = buildPortal();
    return p;
}

function showPortal() {
    const p = getPortal();
    positionPortal(p);
    p.style.display = 'block';
}

function hidePortal() {
    const p = document.getElementById('search-portal');
    if (p) p.style.display = 'none';
    selectedIndex = -1;
}

function showLoading() {
    const p = getPortal();
    p.querySelector('.suggestions-list').innerHTML = '';
    p.querySelector('.suggestions-empty').classList.add('hidden');
    p.querySelector('.suggestions-loading').classList.remove('hidden');
    p.querySelector('.suggestions-see-all').classList.add('hidden');
    showPortal();
}

async function fetchSuggestions(query) {
    showLoading();
    try {
        const resp = await fetch(API_URL + '?q=' + encodeURIComponent(query));
        if (!resp.ok) { hidePortal(); return; }
        const data = await resp.json();
        lastResults = Array.isArray(data) ? data : [];
        renderResults(lastResults, query);
    } catch (err) {
        console.warn('Search error:', err);
        hidePortal();
    }
}

function highlightItem(portal) {
    const items = portal.querySelectorAll('.suggestion-item');
    items.forEach((el, i) => {
        const active = i === selectedIndex;
        el.classList.toggle('bg-gray-50', active);
        el.classList.toggle('dark:bg-slate-700/50', active);
        el.classList.toggle('bg-transparent', !active);
        el.classList.toggle('dark:bg-transparent', !active);
    });
}

function renderResults(results, query) {
    const p = getPortal();
    const listEl = p.querySelector('.suggestions-list');
    const emptyEl = p.querySelector('.suggestions-empty');
    const loadingEl = p.querySelector('.suggestions-loading');
    const seeAllEl = p.querySelector('.suggestions-see-all');

    listEl.innerHTML = '';
    selectedIndex = -1;

    if (results.length === 0) {
        emptyEl.querySelector('.suggestions-empty-query').textContent = query;
        emptyEl.classList.remove('hidden');
        loadingEl.classList.add('hidden');
        showSeeAll(p, query);
        showPortal();
        return;
    }

    emptyEl.classList.add('hidden');
    loadingEl.classList.add('hidden');

    results.forEach((item, i) => {
        const div = document.createElement('div');
        const iconData = getIcon(item.type);
        div.className = 'suggestion-item flex items-center gap-3.5 px-5 py-3.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50';
        div.innerHTML = `
<div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${iconData.bg}">
  <svg class="w-4 h-4 ${iconData.color}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconData.path}"/></svg>
</div>
<div class="flex-1 min-w-0">
  <div class="text-sm font-medium text-gray-900 dark:text-white truncate">${escapeHtml(item.label)}</div>
  <div class="text-xs text-gray-400 capitalize">${item.type}</div>
</div>
<div class="flex items-center gap-1.5 text-xs font-medium text-gray-400 flex-shrink-0">
  <span>${item.type === 'location' ? 'Go' : item.type === 'category' ? 'Browse' : 'Read'}</span>
  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
</div>`;
        div.addEventListener('mousedown', (e) => { e.preventDefault(); if (item.url) window.location.href = item.url; });
        div.addEventListener('mouseenter', () => { selectedIndex = i; highlightItem(p); });
        listEl.appendChild(div);
    });

    showSeeAll(p, query);
    showPortal();
}

function showSeeAll(portal, query) {
    const el = portal.querySelector('.suggestions-see-all');
    el.classList.remove('hidden');
    el.querySelector('.suggestions-see-all-query').textContent = query;
    el.href = '/search?q=' + encodeURIComponent(query);
}

function handleInput(e) {
    clearTimeout(debounceTimer);
    const val = e.target.value.trim();
    if (val.length < MIN_LENGTH) { hidePortal(); return; }
    debounceTimer = setTimeout(() => fetchSuggestions(val), DEBOUNCE_MS);
}

function handleKeydown(e) {
    const p = document.getElementById('search-portal');
    if (!p || p.style.display === 'none') return;
    const items = p.querySelectorAll('.suggestion-item');
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        highlightItem(p);
        if (items[selectedIndex]) items[selectedIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = Math.max(-1, selectedIndex - 1);
        highlightItem(p);
    } else if (e.key === 'Enter') {
        if (selectedIndex >= 0 && lastResults[selectedIndex]) {
            e.preventDefault();
            const item = lastResults[selectedIndex];
            if (item.url) window.location.href = item.url;
        }
    } else if (e.key === 'Escape') {
        hidePortal();
        if (activeInput) activeInput.blur();
    }
}

function handleFocus(e) {
    activeInput = e.target;
    const p = document.getElementById('search-portal');
    if (p) { positionPortal(p); if (lastResults.length > 0) showPortal(); }
}

function handleBlur() {
    setTimeout(hidePortal, 200);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.search-input-field').forEach(input => {
        input.addEventListener('input', handleInput);
        input.addEventListener('keydown', handleKeydown);
        input.addEventListener('focus', handleFocus);
        input.addEventListener('blur', handleBlur);
    });
    window.addEventListener('scroll', () => {
        const p = document.getElementById('search-portal');
        if (p && p.style.display !== 'none') positionPortal(p);
    }, true);
    window.addEventListener('resize', () => {
        const p = document.getElementById('search-portal');
        if (p && p.style.display !== 'none') positionPortal(p);
    });
});
