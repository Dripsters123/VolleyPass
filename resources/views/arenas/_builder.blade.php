{{--
    Shared arena-builder partial.
    Variables:
      $mode        – 'create' | 'edit'
      $arena       – Arena model (only in edit mode)
--}}

@push('styles')
<link rel="stylesheet" href="{{ asset('css/arena-builder.css') }}">
@endpush

{{-- ═══  TOOLS  +  CANVAS  ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- ── Tools panel ─────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">

            @if($mode === 'create')
                <div>
                    <label class="text-sm font-medium text-slate-700">Arēnas nosaukums *</label>
                    <input type="text" id="arena-name" value="{{ old('name') }}" required
                           class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Apraksts</label>
                    <input type="text" id="arena-description" value="{{ old('description') }}"
                           class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                </div>
            @else
                <div>
                    <label class="text-sm font-medium text-slate-700">Arēnas nosaukums *</label>
                    <input type="text" id="arena-name" value="{{ $arena->name }}" required
                           class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Apraksts</label>
                    <input type="text" id="arena-description" value="{{ $arena->description }}"
                           class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                </div>
            @endif

            <div>
                <h4 class="font-medium mb-2">Pievienot elementus</h4>
                <div id="builder-notification" class="builder-notification hidden"></div>
                <div class="element-palette">
                    <button type="button" class="palette-item" data-type="seat">🪑 Sēdvieta</button>
                    <button type="button" class="palette-item" data-type="court">🏐 Volejbola laukums</button>
                </div>
            </div>

            <div class="bg-slate-50 rounded-3xl border border-slate-200 p-4">
                <h4 class="font-medium mb-3">Ģenerēt sēdvietu režģi</h4>
                <div class="grid grid-cols-2 gap-3">
                    <label class="text-sm text-slate-700">Kopējais sēdvietu skaits
                        <span id="seat-count-max" class="text-xs text-gray-400 font-normal ml-1"></span>
                        <input id="seat-count" type="number" min="1" value="48" class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                    </label>
                    <label class="text-sm text-slate-700">Kolonnas
                        <input id="seat-columns" type="number" min="1" value="8" class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                    </label>
                </div>
                <label class="text-sm text-slate-700 mt-3 block">Rindas
                    <input id="seat-rows" type="number" readonly value="6" class="mt-1 w-full rounded-2xl border border-slate-300 bg-slate-100 px-3 py-2">
                </label>
                <button id="generate-grid" type="button" class="mt-3 w-full rounded-2xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">&#x26a1; Ģenerēt sēdvietas</button>
                <p class="text-xs text-gray-400 mt-1.5">Lai dzēstu konkrētu sēdvietu &mdash; klikšķini uz tās un nospied <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-300 rounded text-[10px]">Del</kbd>, vai ar labo peles pogu.</p>
            </div>

            <div>
                <h4 class="font-medium mb-2">Režģa izmērs</h4>
                <input id="grid-size-slider" type="range" min="30" max="80" step="5" value="50" class="w-full">
                <div class="text-xs text-center text-slate-500" id="grid-size-label">50px</div>
            </div>

            <div>
                <h4 class="font-medium mb-2">Izvēlētais elements</h4>
                <div id="element-properties" class="text-sm text-gray-600">
                    Klikšķiniet uz elementa, lai rediģētu tā īpašības   
                </div>
            </div>

            <div class="space-y-2">
                <button id="save-arena" type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl font-medium">
                    {{ $mode === 'create' ? 'Izveidot arēnu' : 'Saglabāt arēnu' }}
                </button>
                <button id="undo-btn" type="button" disabled
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-2xl font-medium opacity-40 cursor-not-allowed transition">
                    &#x21b6; Atsaukt (Ctrl+Z)
                </button>
                <button id="clear-canvas" type="button" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl font-medium">
                    Notīrīt visu
                </button>
            </div>
        </div>
    </div>

    {{-- ── Canvas ──────────────────────────────────── ─--}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Arēnas izkārtojums</h3>
                <div class="text-sm text-gray-500" id="canvas-size">1200 × 840 px</div>
            </div>
            <div class="canvas-wrapper">
                <div class="canvas-scroll-content">
                    <div id="canvas-stage" class="canvas-stage">
                        <div id="arena-canvas" class="arena-canvas" style="width:1200px;height:840px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/arena/arena-builder.js') }}?v={{ filemtime(public_path('js/arena/arena-builder.js')) }}"></script>
