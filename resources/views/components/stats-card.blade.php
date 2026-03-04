<!-- Stats Card Component -->
@props([
    'title' => 'Stat',
    'value' => '0',
    'icon' => 'activity',
    'trend' => null,
    'trendUp' => true,
    'color' => 'primary'
])

@php
    $colors = [
        'primary' => ['bg' => 'bg-indigo-600', 'text' => 'text-white', 'border' => 'border-indigo-600'],
        'success' => ['bg' => 'bg-emerald-500', 'text' => 'text-white', 'border' => 'border-emerald-500'],
        'warning' => ['bg' => 'bg-amber-500', 'text' => 'text-white', 'border' => 'border-amber-500'],
        'danger' => ['bg' => 'bg-red-500', 'text' => 'text-white', 'border' => 'border-red-500'],
        'info' => ['bg' => 'bg-cyan-500', 'text' => 'text-white', 'border' => 'border-cyan-500'],
        'white' => ['bg' => 'bg-white', 'text' => 'text-slate-600', 'border' => 'border-slate-100'],
    ];
    $style = $colors[$color] ?? $colors['white'];
@endphp

<div class="rounded-2xl p-6 border {{ $style['border'] }} {{ $style['bg'] }} shadow-sm hover:shadow-md transition-all group">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h3 class="text-xs font-bold uppercase tracking-wider mb-2 {{ $style['text'] }} opacity-80">{{ $title }}</h3>
            <p class="text-3xl font-bold text-slate-900">{{ $value }}</p>
            
            @if($trend)
            <div class="flex items-center mt-2">
                @if($trendUp)
                <i data-lucide="trending-up" class="w-4 h-4 text-emerald-500 mr-1"></i>
                <span class="text-sm text-emerald-600 font-medium">{{ $trend }}</span>
                @else
                <i data-lucide="trending-down" class="w-4 h-4 text-red-500 mr-1"></i>
                <span class="text-sm text-red-600 font-medium">{{ $trend }}</span>
                @endif
            </div>
            @endif
        </div>
        
        <div class="w-10 h-10 rounded-lg bg-white/50 flex items-center justify-center shadow-sm">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $style['text'] }}"></i>
        </div>
    </div>
</div>
