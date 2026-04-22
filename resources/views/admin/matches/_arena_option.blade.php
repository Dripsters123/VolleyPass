<button type="button"
        data-arena-id="{{ $arena->id }}"
        class="arena-selection-card group rounded-3xl border border-gray-200 p-4 text-left transition hover:border-blue-400 hover:shadow-lg">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h4 class="text-base font-semibold text-slate-900">{{ $arena->name }}</h4>
            <p class="text-sm text-gray-500 mt-1">{{ $arena->width }} × {{ $arena->height }} px</p>
        </div>
        <span class="inline-flex rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white group-hover:bg-blue-700 transition-colors">
            Izvēlēties
        </span>
    </div>
    @if($arena->description)
        <p class="mt-3 text-sm text-gray-500">{{ Str::limit($arena->description, 80) }}</p>
    @endif
</button>
