@php
    $previewScale = min(1, 200 / max($arena->width ?? 1000, $arena->height ?? 700, 1));
    $userId = auth()->id();
    $isFav = $arena->is_favorited ?? false;
    $isOwner = $arena->user_id === $userId;
@endphp

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">

    {{-- Preview canvas --}}
    <div class="relative h-36 bg-slate-50 border-b border-slate-100 overflow-hidden">
        @if(!empty($arena->elements))
            @foreach($arena->elements as $element)
                <div class="absolute rounded text-[9px] leading-none flex items-center justify-center text-white font-bold"
                     style="left:{{ $previewScale * ($element['x'] ?? 0) }}px;top:{{ $previewScale * ($element['y'] ?? 0) }}px;width:{{ max(14, $previewScale * ($element['width'] ?? 30)) }}px;height:{{ max(14, $previewScale * ($element['height'] ?? 30)) }}px;background:{{ ($element['type'] ?? '') === 'court' ? '#f59e0b' : '#2563eb' }};border:1px solid {{ ($element['type'] ?? '') === 'court' ? '#b45309' : '#1d4ed8' }};">
                    {{ ($element['type'] ?? '') === 'court' ? '' : ($element['number'] ?? '') }}
                </div>
            @endforeach
        @else
            <div class="absolute inset-0 flex items-center justify-center text-xs text-gray-400">Nav priekšskatījuma</div>
        @endif

        {{-- Favorite button (top-right) --}}
        <form action="{{ route('arenas.favorite', $arena) }}" method="POST" class="absolute top-2 right-2">
            @csrf
            <button type="submit"
                    title="{{ $isFav ? 'Noņemt no izlases' : 'Pievienot izlasei' }}"
                    class="w-7 h-7 rounded-full flex items-center justify-center bg-white/80 hover:bg-white shadow transition-colors">
                <svg class="w-4 h-4 {{ $isFav ? 'text-yellow-400 fill-yellow-400' : 'text-gray-400' }}"
                     fill="{{ $isFav ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                </svg>
            </button>
        </form>
    </div>

    {{-- Card body --}}
    <div class="p-4 flex flex-col gap-3 flex-1">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 text-sm truncate">{{ $arena->name }}</h3>
                @if($arena->description)
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $arena->description }}</p>
                @endif
            </div>
            {{-- Public/private badge --}}
            @if($isOwner)
                <form action="{{ route('arenas.togglePublic', $arena) }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit"
                            title="{{ $arena->is_public ? 'Padarīt privātu' : 'Publiskot' }}"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-colors
                                   {{ $arena->is_public ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                        @if($arena->is_public)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                            </svg>
                            Publiska
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Privāta
                        @endif
                    </button>
                </form>
            @else
                <span class="text-[10px] font-semibold text-gray-400 px-2 py-0.5 rounded-full bg-gray-100 border border-gray-200">
                    {{ $arena->user->name ?? 'Admin' }}
                </span>
            @endif
        </div>

        <div class="text-[11px] text-gray-400 font-mono">{{ $arena->width }}×{{ $arena->height }}px</div>

        {{-- Action buttons --}}
        <div class="flex gap-2 mt-auto">
            @if($canEdit)
                <a href="{{ route('arenas.edit', $arena) }}"
                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Rediģēt
                </a>
                <form action="{{ route('arenas.duplicate', $arena) }}" method="POST">
                    @csrf
                    <button type="submit" title="Duplicēt"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </form>
                <form action="{{ route('arenas.destroy', $arena) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="vpConfirm('Vai tiešām dzēst šo arēnu?', () => this.closest('form').submit(), { danger: true, confirmText: 'Dzēst' })"
                            title="Dzēst"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-500 bg-red-50 hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            @else
                {{-- Not owner: only duplicate --}}
                <form action="{{ route('arenas.duplicate', $arena) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Kopēt manās arēnās
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
