{{--
    Shared arena-builder partial.
    Variables:
      $mode        – 'create' | 'edit'
      $arena       – Arena model (only in edit mode)
--}}

@push('styles')
<style>
/* ── Canvas ────────────────────────────────── */
.canvas-wrapper{overflow:hidden;width:100%;padding:1.5rem;display:flex;justify-content:center;background:#f3f4f6;border-radius:12px}
.canvas-scroll-content{display:flex;justify-content:center;width:100%;min-width:0}
.canvas-stage{position:relative;flex:0 0 auto}
.arena-canvas{position:relative;border:3px solid #e5e7eb;border-radius:16px;background:#fff url() repeat;background-image:linear-gradient(rgba(148,163,184,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.3) 1px,transparent 1px);background-size:50px 50px;box-shadow:0 10px 25px rgba(0,0,0,.08);overflow:hidden;transform-origin:top left}

/* ── Elements ──────────────────────────────── */
.arena-element{position:absolute;cursor:grab;user-select:none;display:flex;align-items:center;justify-content:center;font-weight:700;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);transition:box-shadow .2s,transform .2s;z-index:10}
.arena-element:hover{box-shadow:0 4px 8px rgba(0,0,0,.2)}
.arena-element.selected{box-shadow:0 0 0 3px #3b82f6}
.seat-element{width:44px;height:44px;background:#0284c7;color:#fff;font-size:12px;border:2px solid #0369a1;border-radius:10px}
.court-element{background:#f59e0b;color:#92400e;font-size:14px;border:2px solid #b45309;border-radius:14px;z-index:5}

/* ── Palette ───────────────────────────────── */
.element-palette{display:grid;gap:12px}
.palette-item{display:flex;align-items:center;justify-content:center;gap:10px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:18px;cursor:pointer;background:#fff;color:#0f172a;font-weight:600;transition:transform .2s,border-color .2s,background .2s;box-shadow:0 12px 24px rgba(15,23,42,.05);width:100%;min-height:48px;text-align:center}
.palette-item:hover{transform:translateY(-1px);border-color:#2563eb;background:#eff6ff}
.palette-item:active{cursor:grabbing;transform:translateY(0)}

/* ── Notification ──────────────────────────── */
.builder-notification{display:none;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:600;margin-bottom:16px}
.builder-notification.error{display:block;background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
.builder-notification.success{display:block;background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.builder-notification.hidden{display:none}

@media(max-width:768px){.canvas-wrapper{overflow-x:auto;overflow-y:hidden;display:block;padding:1rem 0;-webkit-overflow-scrolling:touch;overscroll-behavior-x:contain}.canvas-scroll-content{display:block;width:max-content;min-width:max-content;padding:0 .75rem}.canvas-stage{width:1200px;height:840px}.arena-canvas{display:block;margin:0;transform:none !important}}
</style>
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
                        <input id="seat-count" type="number" min="1" value="48" class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                    </label>
                    <label class="text-sm text-slate-700">Kolonnas
                        <input id="seat-columns" type="number" min="1" value="8" class="mt-1 w-full rounded-2xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-200 focus:ring-2">
                    </label>
                </div>
                <label class="text-sm text-slate-700 mt-3 block">Rindas
                    <input id="seat-rows" type="number" readonly value="6" class="mt-1 w-full rounded-2xl border border-slate-300 bg-slate-100 px-3 py-2">
                </label>
                <button id="generate-grid" type="button" class="mt-3 w-full rounded-2xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Ģenerēt sēdvietas</button>
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

<script src="{{ asset('js/arena-builder.js') }}?v={{ filemtime(public_path('js/arena-builder.js')) }}"></script>
