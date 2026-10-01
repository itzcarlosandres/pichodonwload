<!-- FLOATING DEMO HERO SWITCHER DOCK -->
<div class="sticky top-2 z-40 max-w-4xl mx-auto px-4 mb-2" x-data="{ collapsed: false }">
    <div class="bg-[#18181B]/95 dark:bg-[#121215]/95 backdrop-blur-md text-white border-2 border-[#CE2D2D]/60 rounded-2xl p-2 sm:p-2.5 shadow-[0_12px_36px_rgba(0,0,0,0.35)] transition-all">
        
        <div class="flex items-center justify-between gap-2 sm:gap-4">
            
            <!-- Left Label -->
            <div class="flex items-center gap-2 shrink-0">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#CE2D2D]"></span>
                </span>
                <span class="font-mono text-xs font-black tracking-wide text-white uppercase flex items-center gap-1.5">
                    <i data-lucide="palette" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                    <span class="hidden md:inline">Demo en Vivo:</span>
                    <span class="text-[#CE2D2D]">5 Diseños Hero</span>
                </span>
            </div>

            <!-- 5 Switcher Buttons -->
            <div class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto no-scrollbar py-0.5" x-show="!collapsed" x-transition>
                
                <!-- Variant 1 -->
                <button type="button" 
                        @click="setHero('1')" 
                        :class="activeHero === '1' ? 'bg-[#CE2D2D] text-white shadow-md shadow-red-500/30 font-black' : 'bg-white/10 hover:bg-white/20 text-gray-300 font-semibold'"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-mono transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span class="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">1</span>
                    <span class="hidden sm:inline">Cyber-Minimal</span>
                </button>

                <!-- Variant 2 -->
                <button type="button" 
                        @click="setHero('2')" 
                        :class="activeHero === '2' ? 'bg-[#CE2D2D] text-white shadow-md shadow-red-500/30 font-black' : 'bg-white/10 hover:bg-white/20 text-gray-300 font-semibold'"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-mono transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span class="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">2</span>
                    <span class="hidden sm:inline">Split Studio</span>
                </button>

                <!-- Variant 3 -->
                <button type="button" 
                        @click="setHero('3')" 
                        :class="activeHero === '3' ? 'bg-[#CE2D2D] text-white shadow-md shadow-red-500/30 font-black' : 'bg-white/10 hover:bg-white/20 text-gray-300 font-semibold'"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-mono transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span class="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">3</span>
                    <span class="hidden sm:inline">Swiss Catalog</span>
                </button>

                <!-- Variant 4 -->
                <button type="button" 
                        @click="setHero('4')" 
                        :class="activeHero === '4' ? 'bg-[#CE2D2D] text-white shadow-md shadow-red-500/30 font-black' : 'bg-white/10 hover:bg-white/20 text-gray-300 font-semibold'"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-mono transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span class="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">4</span>
                    <span class="hidden sm:inline">Neo-Terminal</span>
                </button>

                <!-- Variant 5 -->
                <button type="button" 
                        @click="setHero('5')" 
                        :class="activeHero === '5' ? 'bg-[#CE2D2D] text-white shadow-md shadow-red-500/30 font-black' : 'bg-white/10 hover:bg-white/20 text-gray-300 font-semibold'"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-mono transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span class="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">5</span>
                    <span class="hidden sm:inline">Spotlight Vault</span>
                </button>

            </div>

            <!-- Minimize / Expand Toggle -->
            <button type="button" 
                    @click="collapsed = !collapsed" 
                    class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-gray-400 hover:text-white transition-colors shrink-0 cursor-pointer"
                    :title="collapsed ? 'Mostrar selector de diseño' : 'Ocultar selector'">
                <i :data-lucide="collapsed ? 'chevron-down' : 'chevron-up'" class="w-3.5 h-3.5"></i>
            </button>

        </div>

    </div>
</div>
