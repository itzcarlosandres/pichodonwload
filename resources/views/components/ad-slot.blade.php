@props(['position', 'title' => 'Publicidad', 'class' => ''])

@php
    $globalEnabled = \App\Models\Setting::get('ads_enabled', '0') === '1';
    $slotEnabled = \App\Models\Setting::get("ad_{$position}_enabled", '0') === '1';
    $adCode = \App\Models\Setting::get("ad_{$position}_code", '');
@endphp

@if($globalEnabled && $slotEnabled && !empty(trim((string)$adCode)))
    <div class="w-full my-5 flex flex-col items-center justify-center {{ $class }}">
        <div class="flex items-center gap-1.5 mb-1.5 select-none">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500/80"></span>
            <span class="text-[9px] font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 font-bold">
                {{ $title }}
            </span>
        </div>
        <div class="w-full flex justify-center items-center overflow-hidden rounded-2xl bg-white/70 dark:bg-[#141416]/80 border border-[#DDD6CB] dark:border-[#27272A] p-2 min-h-[90px] shadow-sm">
            {!! $adCode !!}
        </div>
    </div>
@endif
