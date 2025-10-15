<x-app-layout>
  <div class="max-w-6xl mx-auto mt-6 bg-white shadow-md rounded-lg p-4">
    <div class="flex flex-col md:flex-row gap-6">
      <div class="flex-1">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h1 class="text-2xl font-bold text-blue-700">{{ $match->home_team_name }} vs {{ $match->away_team_name }}</h1>
            <p class="text-sm text-gray-600">
              {{ $match->start_time->format('Y-m-d H:i') }}
              @if($match->end_time)
                — {{ $match->end_time->format('H:i') }}
              @endif
            </p>
          </div>
          @auth
            <div class="md:hidden">
              <button id="rightPanelToggle" class="px-3 py-2 bg-gray-100 rounded border">Skatīt darbības</button>
            </div>
          @endauth
        </div>

        <div class="mt-3 text-sm text-gray-700 grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <p><strong>Vieta:</strong> {{ $match->location ?? ($match->arena['name'] ?? '-') }}</p>
            <p><strong>Mājas treneris:</strong> {{ $match->home_coach ?? '-' }}</p>
            <p><strong>Viesu treneris:</strong> {{ $match->away_coach ?? '-' }}</p>
            <p><strong>Tiesneši:</strong>
              @if(is_array($match->judges) && count($match->judges))
                {{ implode(', ', $match->judges) }}
              @else
                -
              @endif
            </p>
          </div>
          <div>
            <p><strong>Formāts:</strong> {{ $match->players_per_team }} pret {{ $match->players_per_team }}</p>
            <p><strong>Statuss:</strong> {{ ucfirst($match->match_state ?? $match->status_type) }}</p>
            <p><strong>Biļešu cena:</strong> €{{ number_format($match->ticket_price ?? 0, 2) }}</p>
          </div>
        </div>

        @if($match->match_state === 'completed')
          <div class="mt-4 p-3 rounded bg-green-50 border text-green-800">
            Mačs pabeigts — rezultāts: <strong>{{ $match->home_score }} – {{ $match->away_score }}</strong>
            @if($match->actual_end_time)
              (Beigu laiks: {{ $match->actual_end_time->format('Y-m-d H:i') }})
            @endif
          </div>
        @endif

        @auth
          @if($match->match_state !== 'completed' && $match->is_local)
            <div class="mt-4">
              <button id="buyTicketBtn"
                      data-match-id="{{ $match->id }}"
                      data-ticket-price="{{ $match->ticket_price ?? 10 }}"
                      class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                Pirkt biļeti
              </button>
            </div>
          @endif
        @endauth

        <div class="mt-6 grid md:grid-cols-2 gap-4">
          <div>
            <h3 class="font-semibold text-blue-600 mb-2">Mājas spēlētāji</h3>
            <ul class="list-disc list-inside text-sm">
              @foreach($match->home_players ?? [] as $p)
                <li>{{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}</li>
              @endforeach
            </ul>
          </div>
          <div>
            <h3 class="font-semibold text-blue-600 mb-2">Viesu spēlētāji</h3>
            <ul class="list-disc list-inside text-sm">
              @foreach($match->away_players ?? [] as $p)
                <li>{{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}</li>
              @endforeach
            </ul>
          </div>
        </div>

        @php $photos = $match->media->where('type','photo')->take(8); @endphp
        @if($photos->isNotEmpty())
          <div class="mt-6">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold mb-2">Foto</h3>
              <div class="text-xs text-gray-500">Rādīt līdz 8 bildēm — klikšķiniet lai atvērtu galeriju</div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              @foreach($photos as $idx => $m)
                <button type="button" class="gallery-thumb border rounded overflow-hidden bg-gray-50 focus:outline-none" data-index="{{ $idx }}">
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}"
                       alt="{{ $m->caption ?? 'Skati no spēles' }}"
                       loading="lazy"
                       class="w-full h-28 object-cover">
                  <div class="p-2 text-xs text-gray-600 text-left">{{ $m->caption ?? 'Skati no spēles' }}</div>
                </button>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <div class="w-full md:w-72">
        <div id="rightPanel" class="space-y-4">
          @auth
            @if($match->match_state !== 'completed' && auth()->id() === $match->created_by)
              <div class="border rounded p-4 mb-0">
                <h4 class="font-semibold mb-2">Iesniegt rezultātu (setos)</h4>
                <form method="POST" action="{{ route('matches.score.request', $match->id) }}" id="setScoreForm">
                  @csrf
                  <div id="setsContainer" class="space-y-2">
                    @for ($i = 1; $i <= 3; $i++)
                      <div class="grid grid-cols-3 gap-2 items-center set-row">
                        <label class="text-xs text-gray-600 text-center">Sets {{ $i }}</label>
                        <input type="number" name="sets[{{ $i }}][home]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Mājas" required>
                        <input type="number" name="sets[{{ $i }}][away]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Viesu" required>
                      </div>
                    @endfor
                  </div>
                  <div class="flex justify-between mt-2">
                    <button type="button" id="addSetBtn" class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm hover:bg-gray-300">+ Pievienot setu</button>
                    <button type="button" id="removeSetBtn" class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm hover:bg-gray-300">− Noņemt</button>
                  </div>
                  <div class="mt-3">
                    <label class="block text-xs text-gray-600">Beigu laiks (ja zināms)</label>
                    <input type="datetime-local" name="actual_end_time" class="w-full p-2 border rounded text-sm">
                  </div>
                  <div class="mt-3">
                    <button class="w-full bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">Nosūtīt pieprasījumu</button>
                  </div>
                </form>
              </div>
            @endif

            @if(auth()->id() === $match->created_by || auth()->user()->role === 'admin')
              <div class="border rounded p-4">
                <h4 class="font-semibold mb-2">Augšupielādēt foto</h4>
                <form method="POST" action="{{ route('matches.media.upload', $match->id) }}" enctype="multipart/form-data">
                  @csrf
                  <input type="file" name="file" accept="image/*" required class="mb-2 w-full">
                  <input type="text" name="caption" placeholder="Apraksts (neobligāts)" class="w-full p-2 border rounded text-sm mb-2">
                  <div class="text-xs text-gray-500 mb-2">Maks. 8 bildes uz maču.</div>
                  <button class="w-full bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">Augšupielādēt</button>
                </form>
              </div>
            @endif
          @endauth

          @if(auth()->user()?->role === 'admin')
            <div class="border rounded p-4">
              <h4 class="font-semibold mb-2">Admin darbības</h4>
              <form method="POST" action="{{ route('matches.score.finalize', $match->id) }}">
                @csrf
                <div class="space-y-2" id="adminSetsContainer">
                  @for ($i = 1; $i <= 3; $i++)
                    <div class="grid grid-cols-3 gap-2 items-center">
                      <label class="text-xs text-gray-600 text-center">Sets {{ $i }}</label>
                      <input type="number" name="sets[{{ $i }}][home]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Mājas">
                      <input type="number" name="sets[{{ $i }}][away]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Viesu">
                    </div>
                  @endfor
                </div>
                <button class="w-full bg-red-600 text-white px-3 py-2 rounded mt-3">Apstiprināt un pabeigt</button>
              </form>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="{{ route('local.matches.index') }}" class="text-sm text-gray-600 hover:underline">← Atpakaļ uz mačiem</a>
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
      const rightPanelToggle = document.getElementById('rightPanelToggle');
      const rightPanel = document.getElementById('rightPanel');
      if(rightPanel){
        const drawer=document.createElement('div'); drawer.className='fixed inset-0 z-50 hidden'; drawer.id='mobileActionsDrawer';
        drawer.innerHTML=`<div class="absolute inset-0 bg-black bg-opacity-50"></div><div class="absolute right-0 top-0 bottom-0 w-80 bg-white p-4 overflow-auto"><button id="closeMobileActions" class="px-2 py-1 bg-gray-200 rounded mb-2">Aizvērt</button><div id="drawerContent"></div></div>`;
        document.body.appendChild(drawer);
        document.getElementById('drawerContent').innerHTML = rightPanel.innerHTML;
        document.getElementById('closeMobileActions').addEventListener('click',()=>drawer.classList.add('hidden'));
        rightPanelToggle?.addEventListener('click',()=>drawer.classList.remove('hidden'));
      }
      if(rightPanel && window.innerWidth<768) rightPanel.style.display='none';
      const modalCloseBtn=document.getElementById('modalCloseBtn');
      const seatModal=document.getElementById('seatSelectionModal');
      if(modalCloseBtn && seatModal){ modalCloseBtn.addEventListener('click',()=>seatModal.classList.add('hidden')); }
      const addSetBtn=document.getElementById('addSetBtn');
      const removeSetBtn=document.getElementById('removeSetBtn');
      const container=document.getElementById('setsContainer');
      if(addSetBtn && removeSetBtn && container){
        let setCount=container.querySelectorAll('.set-row').length;
        addSetBtn.addEventListener('click',()=>{ if(setCount>=5) return alert('Maksimālais setu skaits ir 5!'); setCount++; const div=document.createElement('div'); div.classList.add('grid','grid-cols-3','gap-2','items-center','set-row'); div.innerHTML=`<label class="text-xs text-gray-600 text-center">Sets ${setCount}</label><input type="number" name="sets[${setCount}][home]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Mājas" required><input type="number" name="sets[${setCount}][away]" min="0" max="100" class="p-2 border rounded text-center" placeholder="Viesu" required>`; container.appendChild(div); });
        removeSetBtn.addEventListener('click',()=>{ if(container.querySelectorAll('.set-row').length>1){ container.lastElementChild.remove(); setCount--; }});
      }
    })();
  </script>
</x-app-layout>
