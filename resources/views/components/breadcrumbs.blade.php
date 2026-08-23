@props([
    'items' => [], // Array of ['label' => string, 'url' => string|null]
])

<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Console</a>
        </li>

        @foreach($items as $item)
            <li class="flex items-center space-x-2">
                <svg class="h-3 w-3 text-slate-400 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                
                @if(!$loop->last && !empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-slate-800 transition">{{ $item['label'] }}</a>
                @else
                    <span class="text-slate-900 font-bold" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
