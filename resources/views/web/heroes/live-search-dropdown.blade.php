<!-- LIVE SEARCH RESULTS DROPDOWN (Reusable across all hero variants) -->
<div x-show="open" 
     x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-2 scale-98"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-2 scale-98"
     class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] rounded-2xl shadow-[0_20px_45px_rgba(0,0,0,0.2)] overflow-hidden z-50 text-left">
    
    <!-- Top header banner in dropdown -->
    <div class="px-4 py-2.5 bg-[#FAF7F2] dark:bg-[#202025] border-b border-[#E5E0D8] dark:border-[#2E2E35] flex items-center justify-between text-xs font-mono">
        <span class="font-bold text-[#18181B] dark:text-[#F4F4F5] flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-pulse"></span>
            <span x-text="loading ? 'Buscando en la bóveda...' : (results.length > 0 ? 'Resultados instantáneos (' + results.length + ')' : 'Sin resultados')"></span>
        </span>
        <span class="text-gray-500 dark:text-gray-400 text-[11px] font-sans" x-show="results.length > 0">Presiona Enter para ver todos</span>
    </div>

    <!-- Results list -->
    <div class="max-h-[380px] overflow-y-auto divide-y divide-[#E5E0D8] dark:divide-[#2E2E35]">
        <template x-for="item in results" :key="item.id">
            <a :href="item.url" 
               class="flex items-center gap-3.5 p-3 hover:bg-[#FAF7F2] dark:hover:bg-[#202025] transition-colors group">
                <!-- Cover thumb -->
                <div class="w-11 h-14 bg-[#EDE7DE] dark:bg-[#27272A] rounded-lg border border-[#DDD6CB] dark:border-[#3F3F46] overflow-hidden shrink-0 flex items-center justify-center">
                    <img :src="item.cover_url" :alt="item.title" onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#F5EFE6] dark:bg-[#27272A] text-[#CE2D2D] border border-[#DDD6CB] dark:border-[#3F3F46]" x-text="item.console"></span>
                        <template x-if="item.region">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-gray-100 dark:bg-[#2E2E35] text-gray-600 dark:text-gray-300 uppercase" x-text="item.region"></span>
                        </template>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-[#18181B] dark:text-[#F4F4F5] group-hover:text-[#CE2D2D] truncate transition-colors font-sans" x-text="item.title"></h4>
                    <div class="flex items-center gap-3 text-[11px] font-mono text-gray-500 dark:text-gray-400 mt-1">
                        <span class="flex items-center gap-1">
                            <i data-lucide="hard-drive" class="w-3 h-3 text-gray-400"></i>
                            <span x-text="item.formatted_size"></span>
                        </span>
                        <template x-if="item.rating">
                            <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-bold">
                                <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i>
                                <span x-text="item.rating"></span>
                            </span>
                        </template>
                        <template x-if="item.downloads && item.downloads !== '0'">
                            <span class="hidden sm:flex items-center gap-1">
                                <i data-lucide="download" class="w-3 h-3 text-gray-400"></i>
                                <span x-text="item.downloads"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Action button icon -->
                <div class="w-8 h-8 rounded-lg bg-[#FAF7F2] dark:bg-[#27272A] group-hover:bg-[#CE2D2D] group-hover:text-white border border-[#DDD6CB] dark:border-[#3F3F46] group-hover:border-[#CE2D2D] flex items-center justify-center text-gray-500 transition-all shrink-0">
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>
        </template>

        <!-- Empty state -->
        <template x-if="!loading && results.length === 0 && query.trim().length >= 2">
            <div class="p-6 text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#27272A] text-gray-400 flex items-center justify-center mx-auto">
                    <i data-lucide="search-x" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-[#18181B] dark:text-[#F4F4F5] font-sans">No encontramos títulos para "<span x-text="query"></span>"</p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 font-sans">Prueba con otra palabra clave o pulsa Enter para buscar en todo el catálogo.</p>
            </div>
        </template>
    </div>

    <!-- Footer with Explore full link -->
    <div class="p-2.5 bg-[#FAF7F2] dark:bg-[#202025] border-t border-[#E5E0D8] dark:border-[#2E2E35] text-center">
        <a :href="'{{ route('search') }}?q=' + encodeURIComponent(query)" 
           class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-[#CE2D2D] hover:underline">
            <span>Ver todos los resultados en el Explorador</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

</div>
