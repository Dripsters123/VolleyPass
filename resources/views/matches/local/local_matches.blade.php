<x-app-layout>

  {{-- ── Match hero header ── --}}
  <section class="bg-gray-950 border-b border-white/10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <a href="{{ route('local.matches.index') }}"
         class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400 hover:text-white transition-colors mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Atpakaļ uz mačiem
      </a>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          @if($match->match_state === 'completed')
            <span class="inline-block mb-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/15 text-red-400 border border-red-500/30">Pabeigts</span>
          @else
            <span class="inline-block mb-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Gaidāms</span>
          @endif
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            {{ $match->home_team_name }}
            <span class="text-gray-500 font-normal mx-2">vs</span>
            {{ $match->away_team_name }}
          </h1>
          <p class="mt-1 text-sm text-gray-400">
            {{ $match->start_time->translatedFormat('d. M Y, H:i') }}
            @if($match->end_time) — {{ $match->end_time->format('H:i') }} @endif
          </p>
        </div>

        @if($match->match_state === 'completed')
          <div class="flex items-center gap-4 bg-white/8 rounded-2xl px-6 py-3">
            <div class="text-center">
              <p class="text-xs text-gray-400 mb-0.5">Mājas</p>
              <p class="text-3xl font-extrabold text-white">{{ $match->home_score ?? '–' }}</p>
            </div>
            <div class="text-gray-600 text-xl font-bold">:</div>
            <div class="text-center">
              <p class="text-xs text-gray-400 mb-0.5">Viesi</p>
              <p class="text-3xl font-extrabold text-white">{{ $match->away_score ?? '–' }}</p>
            </div>
          </div>
        @elseif($minSeatPrice || $match->ticket_price)
          <div class="text-center sm:text-right">
            <p class="text-xs text-gray-400 mb-0.5">Biļetes cena</p>
            @if($minSeatPrice == $maxSeatPrice)
              <p class="text-3xl font-extrabold text-emerald-400">€{{ number_format($minSeatPrice ?: $match->ticket_price, 2) }}</p>
            @else
              <p class="text-2xl font-extrabold text-emerald-400">€{{ number_format($minSeatPrice, 2) }}–€{{ number_format($maxSeatPrice, 2) }}</p>
            @endif
          </div>
        @endif
      </div>

      @if($match->match_state === 'completed' && $match->sets->isNotEmpty())
        <div class="mt-6 bg-white/5 border border-white/10 rounded-2xl p-4">
          <h2 class="text-sm font-semibold text-white mb-3">Setu rezultāti</h2>
          <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($match->sets as $set)
              <div class="bg-black/20 border border-white/10 rounded-xl px-3 py-2 flex items-center justify-between">
                <span class="text-xs text-gray-300">Sets {{ $set->set_number }}</span>
                <span class="text-sm font-bold text-white">{{ $set->home_score }} : {{ $set->away_score }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">

      {{-- ── Main content ── --}}
      <div class="flex-1 space-y-8">

        {{-- Arena layout – prominent, full-width --}}
        @if($arena && is_array($arena->elements) && count($arena->elements))
          <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h2 class="font-bold text-gray-900">Arēnas izkārtojums</h2>
                <p class="text-xs text-gray-500 mt-0.5">Noklikšķini uz brīvas vietas, lai izvēlētos</p>
              </div>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded bg-blue-500 inline-block"></span>Brīvs
                </span>
                <span class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded bg-red-400 inline-block"></span>Aizņemts
                </span>
              </div>
            </div>
            <div class="p-5 bg-gray-50 overflow-x-auto">
              <div id="arena-preview"
                   style="position:relative;width:{{ $arena->width ?? 600 }}px;height:{{ $arena->height ?? 420 }}px;background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;background-image:linear-gradient(rgba(148,163,184,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.1) 1px,transparent 1px);background-size:50px 50px;margin:0 auto;">
                @foreach($arena->elements as $el)
                  @if(($el['type'] ?? '') === 'court')
                    <div style="position:absolute;left:{{ $el['x'] }}px;top:{{ $el['y'] }}px;width:{{ $el['width'] }}px;height:{{ $el['height'] }}px;background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#78350f;border:2px solid #d97706;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;letter-spacing:.5px;box-shadow:0 2px 8px rgba(245,158,11,.25);">
                      {{ $el['label'] ?? 'Court' }}
                    </div>
                  @elseif(($el['type'] ?? '') === 'seat')
                    @php
                      $seatNum = $el['number'] ?? $el['label'] ?? 'S';
                      $seatId  = $el['id'] ?? (string)$seatNum;
                      $isTaken = in_array($seatId, $takenSeats ?? []);
                    @endphp
                    <div style="position:absolute;left:{{ $el['x'] }}px;top:{{ $el['y'] }}px;width:{{ $el['width'] ?? 40 }}px;height:{{ $el['height'] ?? 40 }}px;background:{{ $isTaken ? '#f87171' : '#3b82f6' }};color:#fff;border:2px solid {{ $isTaken ? '#ef4444' : '#2563eb' }};border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;cursor:{{ $isTaken ? 'not-allowed' : 'pointer' }};box-shadow:0 1px 4px rgba(0,0,0,.15);transition:transform .1s;"
                         title="{{ $seatNum }}{{ $isTaken ? ' (aizņemts)' : ' – klikšķini lai izvēlētos' }}"
                         onmouseover="{{ $isTaken ? '' : "this.style.transform='scale(1.12)'" }}"
                         onmouseout="this.style.transform='scale(1)'">
                      {{ $seatNum }}
                    </div>
                  @endif
                @endforeach
              </div>
            </div>
            @auth
              @if($match->match_state !== 'completed' && $match->is_local)
                <div class="px-5 py-4 bg-white border-t border-gray-100 flex items-center justify-between">
                  <p class="text-sm text-gray-600">Cena:
                    @if($minSeatPrice == $maxSeatPrice)
                      <span class="font-bold text-gray-900">€{{ number_format($minSeatPrice ?: ($match->ticket_price ?? 0), 2) }}</span>
                    @else
                      <span class="font-bold text-gray-900">€{{ number_format($minSeatPrice, 2) }}–€{{ number_format($maxSeatPrice, 2) }}</span>
                    @endif
                    / biļete
                  </p>
                  <button id="buyTicketBtn"
                          data-match-id="{{ $match->id }}"
                          data-ticket-price="{{ $match->ticket_price ?? 10 }}"
                          data-taken-seats="{{ json_encode($takenSeats) }}"
                          data-taken-seat-ids="{{ json_encode($takenSeatIds) }}"
                          data-seat-prices="{{ json_encode($seatPrices) }}"
                          data-seat-ids="{{ json_encode($seatIdMap) }}"
                          data-new-user-promo="{{ $newUserPromoPercent }}"
                          @if($arena) data-arena-width="{{ $arena->width ?? 600 }}" data-arena-height="{{ $arena->height ?? 420 }}" @endif
                          @if($customElements) data-custom-elements="{{ json_encode($customElements) }}" @endif
                          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity shadow-lg shadow-blue-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Pirkt biļeti
                  </button>
                </div>
              @endif
            @endauth
          </div>
        @elseif($match->match_state !== 'completed' && $match->is_local)
          {{-- No arena configured – show simple buy button --}}
          @auth
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center justify-between">
              <p class="text-sm text-gray-600">Biļetes cena:
                @if($minSeatPrice == $maxSeatPrice)
                  <span class="font-bold text-gray-900">€{{ number_format($minSeatPrice ?: ($match->ticket_price ?? 0), 2) }}</span>
                @else
                  <span class="font-bold text-gray-900">€{{ number_format($minSeatPrice, 2) }}–€{{ number_format($maxSeatPrice, 2) }}</span>
                @endif
              </p>
              <button id="buyTicketBtn"
                      data-match-id="{{ $match->id }}"
                      data-ticket-price="{{ $match->ticket_price ?? 10 }}"
                      data-taken-seats="{{ json_encode($takenSeats) }}"
                      data-taken-seat-ids="{{ json_encode($takenSeatIds) }}"
                      data-seat-prices="{{ json_encode($seatPrices) }}"
                      data-seat-ids="{{ json_encode($seatIdMap) }}"
                      data-new-user-promo="{{ $newUserPromoPercent }}"
                      @if($arena) data-arena-width="{{ $arena->width ?? 600 }}" data-arena-height="{{ $arena->height ?? 420 }}" @endif
                      @if($customElements) data-custom-elements="{{ json_encode($customElements) }}" @endif
                      class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Pirkt biļeti
              </button>
            </div>
          @endauth
        @endif

        {{-- Match details --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h2 class="font-bold text-gray-900 mb-4">Mača informācija</h2>
          <div class="grid sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div class="flex items-start gap-2">
              <span class="text-gray-400 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </span>
              <div>
                <p class="text-xs text-gray-400">Vieta</p>
                <p class="font-medium text-gray-800">{{ $match->location ?? ($match->arena['name'] ?? '–') }}</p>
              </div>
            </div>
            <div class="flex items-start gap-2">
              <span class="text-gray-400 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </span>
              <div>
                <p class="text-xs text-gray-400">Formāts</p>
                <p class="font-medium text-gray-800">{{ $match->players_per_team }} pret {{ $match->players_per_team }}</p>
              </div>
            </div>
            <div class="flex items-start gap-2">
              <span class="text-gray-400 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </span>
              <div>
                <p class="text-xs text-gray-400">Treneri</p>
                <p class="font-medium text-gray-800">{{ $match->home_coach ?? '–' }} / {{ $match->away_coach ?? '–' }}</p>
              </div>
            </div>
            <div class="flex items-start gap-2">
              <span class="text-gray-400 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </span>
              <div>
                <p class="text-xs text-gray-400">Tiesneši</p>
                <p class="font-medium text-gray-800">
                  @if(is_array($match->judges) && count($match->judges))
                    {{ implode(', ', $match->judges) }}
                  @else –
                  @endif
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Players --}}
        <div class="grid sm:grid-cols-2 gap-5">
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>
              Mājas komanda
            </h3>
            <ul class="space-y-1.5">
              @forelse($match->home_players ?? [] as $p)
                <li class="text-sm text-gray-700 flex items-center gap-2">
                  <span class="w-5 h-5 rounded-full bg-orange-100 flex items-center justify-center text-[10px] font-bold text-orange-600 shrink-0">
                    {{ strtoupper(substr($p['first_name'] ?? 'P', 0, 1)) }}
                  </span>
                  {{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}
                </li>
              @empty
                <li class="text-sm text-gray-400 italic">Nav norādīti spēlētāji</li>
              @endforelse
            </ul>
          </div>
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
              Viesu komanda
            </h3>
            <ul class="space-y-1.5">
              @forelse($match->away_players ?? [] as $p)
                <li class="text-sm text-gray-700 flex items-center gap-2">
                  <span class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-600 shrink-0">
                    {{ strtoupper(substr($p['first_name'] ?? 'P', 0, 1)) }}
                  </span>
                  {{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}
                </li>
              @empty
                <li class="text-sm text-gray-400 italic">Nav norādīti spēlētāji</li>
              @endforelse
            </ul>
          </div>
        </div>

        {{-- Photo gallery --}}
        @php $photos = $match->media->where('type','photo')->take(8); @endphp
        @if($photos->isNotEmpty())
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-bold text-gray-900">Foto no spēles</h3>
              <span class="text-xs text-gray-400">Klikšķini lai atvērtu galeriju</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              @foreach($photos as $idx => $m)
                <button type="button" class="gallery-thumb group rounded-xl overflow-hidden border border-gray-100 hover:border-blue-300 transition-colors focus:outline-none" data-index="{{ $idx }}">
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}"
                       alt="{{ $m->caption ?? 'Foto' }}"
                       loading="lazy"
                       class="w-full h-28 object-cover group-hover:opacity-90 transition-opacity">
                  @if($m->caption)
                    <div class="px-2 py-1.5 text-[11px] text-gray-500 text-left bg-white">{{ $m->caption }}</div>
                  @endif
                </button>
              @endforeach
            </div>
          </div>
        @endif

      </div>

      {{-- ── Right sidebar ── --}}
      <div class="w-full md:w-72 shrink-0">
        <div id="rightPanel" class="space-y-4">

          @auth
            @if($match->match_state !== 'completed' && auth()->id() === $match->created_by && auth()->user()->role !== 'admin')
              <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h4 class="font-bold text-gray-900 mb-3">Saglabāt rezultātu</h4>
                <form method="POST" action="{{ route('matches.score.request', $match->id) }}" id="setScoreForm" class="space-y-3">
                  @csrf
                  <div id="setsContainer" class="space-y-2">
                    @for ($i = 1; $i <= 3; $i++)
                      <div class="grid grid-cols-3 gap-2 items-center set-row">
                        <label class="text-xs text-gray-500 text-center">Sets {{ $i }}</label>
                        <input type="number" name="sets[{{ $i }}][home]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)"
                               class="px-2 py-1.5 border border-gray-200 rounded-lg text-center text-sm" placeholder="Māj." required>
                        <input type="number" name="sets[{{ $i }}][away]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)"
                               class="px-2 py-1.5 border border-gray-200 rounded-lg text-center text-sm" placeholder="Vies." required>
                      </div>
                    @endfor
                  </div>
                  <p class="text-xs text-gray-400">
                    Sāciet ar 3 setiem. Ja pēc 4 setiem ir 2:2, pievienojiet 5. setu ar "+ Setu". Deuce ir atļauts, tāpēc ievadiet faktiskos punktus, piemēram, 28:26 vai 17:15.
                  </p>
                  <div class="flex gap-2">
                    <button type="button" id="addSetBtn" class="flex-1 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">+ Setu</button>
                    <button type="button" id="removeSetBtn" class="flex-1 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">− Setu</button>
                  </div>
                  <button class="w-full py-2.5 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Saglabāt rezultātu
                  </button>
                </form>
              </div>
            @endif

            @if(auth()->id() === $match->created_by || auth()->user()->role === 'admin')
              <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h4 class="font-bold text-gray-900 mb-3">Augšupielādēt foto</h4>
                <form method="POST" action="{{ route('matches.media.upload', $match->id) }}" enctype="multipart/form-data" class="space-y-3">
                  @csrf
                  <input type="file" name="file" accept="image/*" required class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                  <input type="text" name="caption" placeholder="Apraksts (neobligāts)"
                         class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                  <p class="text-xs text-gray-400">Maks. 8 bildes uz maču.</p>
                  <button class="w-full py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    Augšupielādēt
                  </button>
                </form>
              </div>
            @endif
          @endauth

          @if(auth()->user()?->role === 'admin')
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
              <h4 class="font-bold text-gray-900 mb-3">Admin – apstiprināt rezultātu</h4>
              <form method="POST" action="{{ route('matches.score.finalize', $match->id) }}" class="space-y-3">
                @csrf
                <div id="adminSetsContainer" class="space-y-2">
                  @for ($i = 1; $i <= 3; $i++)
                    <div class="grid grid-cols-3 gap-2 items-center set-row">
                      <label class="text-xs text-gray-500 text-center">Sets {{ $i }}</label>
                      <input type="number" name="sets[{{ $i }}][home]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)"
                             class="px-2 py-1.5 border border-gray-200 rounded-lg text-center text-sm" placeholder="Māj." required>
                      <input type="number" name="sets[{{ $i }}][away]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)"
                             class="px-2 py-1.5 border border-gray-200 rounded-lg text-center text-sm" placeholder="Vies." required>
                    </div>
                  @endfor
                </div>
                <p class="text-xs text-gray-400">
                  Sāciet ar 3 setiem. Ja pēc 4 setiem ir 2:2, pievienojiet 5. setu ar "+ Setu". Deuce ir atļauts, tāpēc ievadiet faktiskos punktus, piemēram, 28:26 vai 17:15.
                </p>
                <div class="flex gap-2">
                  <button type="button" id="adminAddSetBtn" class="flex-1 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">+ Setu</button>
                  <button type="button" id="adminRemoveSetBtn" class="flex-1 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">− Setu</button>
                </div>
                <button class="w-full py-2.5 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                  Apstiprināt un pabeigt
                </button>
              </form>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>

  <div id="galleryModal" class="hidden fixed inset-0 z-60 bg-black bg-opacity-80 flex items-center justify-center p-4">
    <div class="relative max-w-3xl w-full">
      <button id="galleryClose" class="absolute top-2 right-2 bg-white rounded px-2 py-1">Aizvērt</button>
      <img id="galleryImg" src="" alt="gallery" class="w-full h-[60vh] object-contain bg-black"/>
      <div class="mt-2 flex items-center justify-between text-white">
        <div id="galleryCounter"></div>
        <div>
          <button id="galleryPrev" class="px-3 py-1 bg-white text-black rounded mr-1">◀</button>
          <button id="galleryNext" class="px-3 py-1 bg-white text-black rounded">▶</button>
        </div>
      </div>
    </div>
  </div>

  <div id="seatSelectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="max-w-6xl mx-auto my-6 bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-bold">Izvēlies vietas</h2>
        <div class="flex items-center gap-2">
          <button id="summaryToggleBtn" class="md:hidden px-2 py-1 bg-gray-200 rounded">Kopsavilkums</button>
          <button id="modalCloseBtn" class="px-3 py-1 bg-gray-300 rounded">Aizvērt</button>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:h-[70vh] h-auto">
        <div class="seat-map-viewport flex-1 bg-gray-50 p-4 overflow-auto">
          <div id="seatMap" class="w-full h-full"></div>
        </div>
        <aside id="summaryPanel" class="w-full md:w-96 bg-white border-t md:border-t-0 md:border-l md:border-gray-200 p-4 flex flex-col">
          <h3 id="summaryHeader" class="font-semibold mb-3">Izvēlētās vietas</h3>
          <div id="selectedSeatsList" class="flex-1 overflow-y-auto space-y-2 pr-1">
            <div class="text-gray-500 text-sm italic">Nav izvēlētu vietu</div>
          </div>
          <div class="mt-3">
            <label class="text-sm font-medium mb-1">Atlaides kods</label>
            <div class="flex gap-2">
              <input id="discountCodeInput" type="text" placeholder="Ievadi kodu" class="flex-1 border rounded px-3 py-2 text-sm">
              <button id="applyDiscountBtn" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Pielietot</button>
              <button id="clearDiscountBtn" class="px-3 py-2 bg-gray-200 rounded text-sm">Notīrīt</button>
            </div>
            <div id="discountInfo" class="mt-2 text-sm text-gray-600"></div>
          </div>
          <div id="totalPrice" class="mt-4 text-right font-semibold text-gray-700 hidden"></div>
          <button id="finalizePurchaseBtn" class="mt-4 bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
            Apstiprināt pirkumu
          </button>
        </aside>
      </div>
    </div>
  </div>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/seatMap.css') }}">
  <script src="{{ asset('js/seats/seatMap.js') }}"></script>
  <script src="{{ asset('js/seats/seatModalHandlers.js') }}"></script>
  <script src="{{ asset('js/purchases/matchPurchase.js') }}"></script>

  <script>
    function limitScoreInput(el) {
      const sanitized = (el.value || '').replace(/\D/g, '').slice(0, 3);
      if (sanitized === '') {
        el.value = '';
        return;
      }
      const value = Math.min(parseInt(sanitized, 10), 100);
      el.value = Number.isNaN(value) ? '' : String(value);
    }

    (function(){
      const thumbs = Array.from(document.querySelectorAll('.gallery-thumb img'));
      const srcs = thumbs.map(i => i.src);
      const modal = document.getElementById('galleryModal');
      const galleryImg = document.getElementById('galleryImg');
      const counter = document.getElementById('galleryCounter');
      let idx = 0;
      let autoplay = null;
      function openGallery(i){ if(!srcs.length) return; idx=i||0; galleryImg.src=srcs[idx]; counter.textContent=(idx+1)+' / '+srcs.length; modal.classList.remove('hidden'); startAutoplay(); }
      function closeGallery(){ modal.classList.add('hidden'); stopAutoplay(); }
      function next(){ idx=(idx+1)%srcs.length; galleryImg.src=srcs[idx]; counter.textContent=(idx+1)+' / '+srcs.length; }
      function prev(){ idx=(idx-1+srcs.length)%srcs.length; galleryImg.src=srcs[idx]; counter.textContent=(idx+1)+' / '+srcs.length; }
      function startAutoplay(){ stopAutoplay(); autoplay=setInterval(next,3000); }
      function stopAutoplay(){ if(autoplay){clearInterval(autoplay); autoplay=null;} }
      document.querySelectorAll('.gallery-thumb').forEach((btn,i)=>{ btn.addEventListener('click',()=>openGallery(i)); });
      document.getElementById('galleryClose')?.addEventListener('click',closeGallery);
      document.getElementById('galleryNext')?.addEventListener('click',()=>{ next(); startAutoplay(); });
      document.getElementById('galleryPrev')?.addEventListener('click',()=>{ prev(); startAutoplay(); });
      document.getElementById('galleryModal')?.addEventListener('click',(ev)=>{ if(ev.target.id==='galleryModal') closeGallery(); });
      const rightPanel = document.getElementById('rightPanel');
      if(rightPanel && window.innerWidth<768) rightPanel.style.display='none';
      const modalCloseBtn=document.getElementById('modalCloseBtn');
      const seatModal=document.getElementById('seatSelectionModal');
      if(modalCloseBtn && seatModal){ modalCloseBtn.addEventListener('click',()=>seatModal.classList.add('hidden')); }
      const addSetBtn=document.getElementById('addSetBtn');
      const removeSetBtn=document.getElementById('removeSetBtn');
      const container=document.getElementById('setsContainer');
      if(addSetBtn && removeSetBtn && container){
        let setCount=container.querySelectorAll('.set-row').length;
        addSetBtn.addEventListener('click',()=>{ if(setCount>=5) return alert('Maksimālais setu skaits ir 5!'); setCount++; const div=document.createElement('div'); div.classList.add('grid','grid-cols-3','gap-2','items-center','set-row'); div.innerHTML=`<label class="text-xs text-gray-600 text-center">Sets ${setCount}</label><input type="number" name="sets[${setCount}][home]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Mājas" required><input type="number" name="sets[${setCount}][away]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Viesu" required>`; container.appendChild(div); });
        removeSetBtn.addEventListener('click',()=>{ if(container.querySelectorAll('.set-row').length>3){ container.lastElementChild.remove(); setCount--; }});
      }
      const adminAddSetBtn=document.getElementById('adminAddSetBtn');
      const adminRemoveSetBtn=document.getElementById('adminRemoveSetBtn');
      const adminContainer=document.getElementById('adminSetsContainer');
      if(adminAddSetBtn && adminRemoveSetBtn && adminContainer){
        let adminSetCount=adminContainer.querySelectorAll('.set-row').length;
        adminAddSetBtn.addEventListener('click',()=>{ if(adminSetCount>=5) return alert('Maksimālais setu skaits ir 5!'); adminSetCount++; const div=document.createElement('div'); div.classList.add('grid','grid-cols-3','gap-2','items-center','set-row'); div.innerHTML=`<label class="text-xs text-gray-600 text-center">Sets ${adminSetCount}</label><input type="number" name="sets[${adminSetCount}][home]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Mājas" required><input type="number" name="sets[${adminSetCount}][away]" min="0" max="100" inputmode="numeric" oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Viesu" required>`; adminContainer.appendChild(div); });
        adminRemoveSetBtn.addEventListener('click',()=>{ if(adminContainer.querySelectorAll('.set-row').length>3){ adminContainer.lastElementChild.remove(); adminSetCount--; }});
      }
    })();
  </script>
</x-app-layout>
