<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8">

  {{-- ── Header ─────────────────────────────────────────────────── --}}
  @php
    $statusLabels = ['pending'=>'Gaida','reviewing'=>'Tiek izskatīts','accepted'=>'Apstiprināts','rejected'=>'Noraidīts','appealed'=>'Apelācija'];
    $statusColors = [
      'pending'   => 'bg-yellow-100 text-yellow-700',
      'reviewing' => 'bg-blue-100 text-blue-700',
      'accepted'  => 'bg-green-100 text-green-700',
      'rejected'  => 'bg-red-100 text-red-700',
      'appealed'  => 'bg-purple-100 text-purple-700',
    ];
  @endphp

  <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <a href="{{ route('admin.match_requests.inbox') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Atpakaļ uz iesūtni</a>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">
        @if(($req->request_type ?? '') === 'score_update')
          Rezultātu pieprasījums #{{ $req->id }}
        @else
          Mača pieprasījums #{{ $req->id }}
        @endif
      </h1>
      <p class="text-sm text-gray-400 mt-0.5">
        Iesniedzis {{ optional($req->user)->name ?? '—' }} &mdash; {{ $req->created_at?->timezone('Europe/Riga')->format('d.m.Y H:i') }}
      </p>
    </div>
    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-700' }}">
      {{ $statusLabels[$req->status] ?? ucfirst($req->status ?? '') }}
    </span>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
  @endif

  @if(($req->request_type ?? '') !== 'score_update')

    {{-- ── Meta cards ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Sākums</div>
        <div class="text-sm font-semibold text-gray-800">{{ $req->start_time?->format('d.m.Y H:i') ?? '—' }}</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Beigas</div>
        <div class="text-sm font-semibold text-gray-800">{{ $req->end_time?->format('d.m.Y H:i') ?? '—' }}</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Formāts</div>
        <div class="text-sm font-semibold text-gray-800">{{ $req->players_per_team ?? '?' }} × {{ $req->players_per_team ?? '?' }}</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Arēna</div>
        <div class="text-sm font-semibold text-gray-800 truncate">{{ $req->arena_name ?: '—' }}</div>
      </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 col-span-2">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Adrese / Vieta</div>
        <div class="text-sm font-semibold text-gray-800">{{ $req->location ?: '—' }}</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Biļetes cena</div>
        <div class="text-sm font-semibold text-gray-800">
          @if($req->ticket_price) €{{ number_format($req->ticket_price, 2) }} @else — @endif
        </div>
      </div>
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Tiesneši</div>
        @php $judges = is_array($req->judges) ? $req->judges : (json_decode($req->judges ?? '[]', true) ?: []); @endphp
        <div class="text-sm font-semibold text-gray-800">{{ count($judges) > 0 ? implode(', ', $judges) : '—' }}</div>
      </div>
    </div>

    {{-- ── Player roster prep ────────────────────────────────── --}}
    @php
      $homePlayers = is_array($req->home_players) ? $req->home_players : (json_decode($req->home_players ?? '[]', true) ?: []);
      $awayPlayers = is_array($req->away_players) ? $req->away_players : (json_decode($req->away_players ?? '[]', true) ?: []);
    @endphp

    {{-- ── Arena layout canvas ─────────────────────────────────── --}}
    @if($req->arena_elements)
      @php $arenaElements = is_array($req->arena_elements) ? $req->arena_elements : json_decode($req->arena_elements, true); @endphp
      @if(!empty($arenaElements))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
          <h2 class="text-base font-semibold text-gray-700 mb-4">Arēnas izkārtojums — {{ $req->arena_name ?? '' }}</h2>
          <div class="overflow-auto rounded-xl border border-gray-100 bg-gray-50">
            <canvas id="arenaPreview" class="block mx-auto" style="max-width:100%;"></canvas>
          </div>
          <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 justify-center">
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 rounded bg-amber-400 border border-amber-600"></span>Laukums</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 rounded bg-sky-600 border border-sky-700"></span>Sēdvieta</span>
          </div>
        </div>
        <script>
        (function(){
          var elements = @json($arenaElements);
          var srcW = {{ (int)($req->arena_width ?? 1000) }};
          var srcH = {{ (int)($req->arena_height ?? 700) }};
          var maxW = Math.min(900, document.querySelector('#arenaPreview').parentElement.clientWidth - 32);
          var scale = Math.min(maxW / srcW, 600 / srcH, 1);
          var cw = Math.round(srcW * scale);
          var ch = Math.round(srcH * scale);
          var canvas = document.getElementById('arenaPreview');
          canvas.width  = cw;
          canvas.height = ch;
          canvas.style.width  = cw + 'px';
          canvas.style.height = ch + 'px';
          var ctx = canvas.getContext('2d');
          ctx.fillStyle = '#f8fafc';
          ctx.fillRect(0, 0, cw, ch);
          // grid dots
          ctx.strokeStyle = '#e2e8f0';
          ctx.lineWidth = 0.5;
          var gs = Math.round(50 * scale);
          for (var x = 0; x < cw; x += gs) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, ch); ctx.stroke(); }
          for (var y = 0; y < ch; y += gs) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(cw, y); ctx.stroke(); }
          elements.forEach(function(el) {
            var ex = Math.round(el.x * scale), ey = Math.round(el.y * scale);
            var ew = Math.round(el.width * scale), eh = Math.round(el.height * scale);
            if (el.type === 'court') {
              ctx.fillStyle = '#fbbf24';
              ctx.strokeStyle = '#b45309';
              ctx.lineWidth = 1.5;
              roundRect(ctx, ex, ey, ew, eh, 8);
              ctx.fill(); ctx.stroke();
              ctx.fillStyle = '#92400e';
              ctx.font = 'bold ' + Math.max(10, Math.round(14 * scale)) + 'px sans-serif';
              ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
              ctx.fillText(el.label || 'Laukums', ex + ew/2, ey + eh/2);
            } else {
              ctx.fillStyle = '#0284c7';
              ctx.strokeStyle = '#0369a1';
              ctx.lineWidth = 1;
              roundRect(ctx, ex, ey, ew, eh, 6);
              ctx.fill(); ctx.stroke();
              ctx.fillStyle = '#fff';
              ctx.font = 'bold ' + Math.max(7, Math.round(11 * scale)) + 'px sans-serif';
              ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
              ctx.fillText(el.number || el.label || '', ex + ew/2, ey + eh/2);
            }
          });
          function roundRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + w - r, y); ctx.quadraticCurveTo(x + w, y, x + w, y + r);
            ctx.lineTo(x + w, y + h - r); ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
            ctx.lineTo(x + r, y + h); ctx.quadraticCurveTo(x, y + h, x, y + h - r);
            ctx.lineTo(x, y + r); ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
          }
        })();
        </script>
      @endif
    @endif

    {{-- ── Player rosters ──────────────────────────────────────── --}}
    <div class="grid md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-4">
          @if($req->home_logo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($req->home_logo) }}" class="w-10 h-10 rounded-full object-cover border" alt="">
          @else
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($req->home_team ?? 'H', 0, 1)) }}</span>
            </div>
          @endif
          <div>
            <h3 class="font-semibold text-gray-900">{{ $req->home_team ?? 'Mājas komanda' }}</h3>
            @if($req->home_coach)
              <p class="text-xs text-gray-400">Treneris: {{ $req->home_coach }}</p>
            @endif
          </div>
        </div>
        <ul class="space-y-1.5">
          @foreach($homePlayers as $i => $p)
            <li class="flex items-center gap-2.5 text-sm text-gray-700">
              <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">{{ $i+1 }}</span>
              @if(is_array($p)) {{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}
              @else {{ $p }}
              @endif
            </li>
          @endforeach
        </ul>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-4">
          @if($req->away_logo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($req->away_logo) }}" class="w-10 h-10 rounded-full object-cover border" alt="">
          @else
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <span class="text-red-700 font-bold text-sm">{{ strtoupper(substr($req->away_team ?? 'A', 0, 1)) }}</span>
            </div>
          @endif
          <div>
            <h3 class="font-semibold text-gray-900">{{ $req->away_team ?? 'Viesu komanda' }}</h3>
            @if($req->away_coach)
              <p class="text-xs text-gray-400">Treneris: {{ $req->away_coach }}</p>
            @endif
          </div>
        </div>
        <ul class="space-y-1.5">
          @foreach($awayPlayers as $i => $p)
            <li class="flex items-center gap-2.5 text-sm text-gray-700">
              <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">{{ $i+1 }}</span>
              @if(is_array($p)) {{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}
              @else {{ $p }}
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>



  @else
    {{-- ── Score update request ────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6 space-y-3 text-gray-700">
      <div><span class="font-medium text-gray-500 text-sm">Mājas komanda:</span> {{ $req->home_team ?? '—' }}</div>
      <div><span class="font-medium text-gray-500 text-sm">Viesu komanda:</span> {{ $req->away_team ?? '—' }}</div>
      <div class="pt-3 border-t">
        <div class="text-sm font-medium text-gray-500 mb-1">Priekšlikts rezultāts</div>
        <p class="text-2xl font-bold text-gray-900">{{ $req->score_home ?? '0' }} — {{ $req->score_away ?? '0' }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Saistītais mačs:
          @if($req->match)
            <a href="{{ route('local.matches.show', $req->match->id) }}" class="text-blue-600 underline">
              {{ $req->match->home_team_name }} vs {{ $req->match->away_team_name }}
            </a>
          @else
            n/a
          @endif
        </p>
      </div>
    </div>
  @endif

  {{-- ── Appeal & rejection info ─────────────────────────────── --}}
  @if($req->appeal_message)
    <div class="mb-4 p-5 bg-purple-50 border border-purple-200 rounded-2xl">
      <div class="font-semibold text-purple-800 mb-1">📢 Apelācija no lietotāja</div>
      <p class="text-sm text-purple-700">{{ $req->appeal_message }}</p>
    </div>
  @endif
  @if($req->rejection_reason)
    <div class="mb-4 p-5 bg-red-50 border border-red-200 rounded-2xl">
      <div class="font-semibold text-red-700 mb-1">Iepriekšējais noraidīšanas iemesls</div>
      <p class="text-sm text-red-600">{{ $req->rejection_reason }}</p>
    </div>
  @endif

  {{-- ── Admin action panel ─────────────────────────────────── --}}
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-data="{ rejectOpen: false }">
    <h3 class="font-semibold text-gray-700 mb-4">Darbības</h3>
    <div class="flex flex-wrap gap-3">

      @if($req->status !== 'accepted')
        <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}">
          @csrf
          <button type="button"
                  onclick="vpConfirm('Apstiprintāt pieprasījumu?', () => this.closest('form').submit(), { confirmText: 'Apstiprintāt' })"
                  class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
            ✅ Apstiprināt
          </button>
        </form>
      @endif

      @if($req->status !== 'reviewing')
        <form method="POST" action="{{ route('admin.match_requests.review', $req->id) }}">
          @csrf
          <button type="submit"
                  class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
            👁 Atzīmēt kā "Tiek izskatīts"
          </button>
        </form>
      @endif

      @if($req->status !== 'rejected')
        <button @click="rejectOpen = !rejectOpen" type="button"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
          ❌ Noraidīt
        </button>
      @endif

      @if($req->status === 'accepted')
        <a href="{{ route('matches.create', ['request_id' => $req->id]) }}"
           class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
          🏐 Izveidot maču
        </a>
      @endif
    </div>

    <div x-show="rejectOpen" x-transition class="mt-5 p-5 bg-red-50 border border-red-200 rounded-xl">
      <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}">
        @csrf
        <label class="block text-sm font-medium text-red-700 mb-2">Noraidīšanas iemesls (nosūtīts lietotājam)</label>
        <textarea name="rejection_reason" rows="3"
            class="w-full rounded-xl border-red-300 text-sm focus:ring-red-500 focus:border-red-500"
            placeholder="Piemēram: Nepilnīga informācija par spēlētājiem…">{{ $req->rejection_reason }}</textarea>
        <div class="mt-3 flex gap-2">
          <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
            Nosūtīt noraidījumu
          </button>
          <button type="button" @click="rejectOpen = false"
                  class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
            Atcelt
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Admin delete (cleanup) --}}
  <div class="mt-4 flex justify-end">
    <form method="POST" action="{{ route('admin.match_requests.admin_destroy', $req->id) }}">
      @csrf @method('DELETE')
      <button type="button"
              onclick="vpConfirm('Dzēst šo pieprasījumu pilnibā? Šī darbība ir neatgriezeniska.', () => this.closest('form').submit(), { danger: true, confirmText: 'Dzēst' })"
              class="px-4 py-2 text-xs text-gray-400 hover:text-red-600 border border-gray-200 hover:border-red-300 rounded-xl transition">
        🗑 Dzēst pieprasījumu
      </button>
    </form>
  </div>

</div>
</x-app-layout>