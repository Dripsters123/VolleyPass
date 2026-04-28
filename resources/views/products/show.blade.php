<x-app-layout>
<div class="container mx-auto p-4 sm:p-6 max-w-5xl">
  <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Atpakaļ uz veikalu
  </a>

  @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div>
  @endif

  <div class="mt-4 flex flex-col md:flex-row gap-8">
    {{-- Image --}}
    <div class="w-full md:w-2/5 shrink-0">
      @if($product->image_path)
        <img src="{{ asset('storage/'.$product->image_path) }}" class="w-full aspect-square object-cover rounded-2xl shadow-sm">
      @else
        <div class="w-full aspect-square flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl">
          <svg class="w-20 h-20 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
          </svg>
        </div>
      @endif
    </div>

    <div class="flex-1 flex flex-col justify-between">
      <div>
        @if($product->category)
          @php $catLabel = config('products.categories')[$product->category] ?? ucfirst($product->category); @endphp
          <span class="inline-block px-2.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full mb-3">{{ $catLabel }}</span>
        @endif
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $product->title }}</h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300 leading-relaxed">{{ $product->description }}</p>

        <div class="mt-5 flex items-center gap-4 flex-wrap">
          <span class="text-3xl font-bold text-blue-700">€{{ number_format($product->price, 2) }}</span>
          @if($product->stock > 0)
            @if($product->stock <= 5)
              <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">Atlicis: {{ $product->stock }} gb.</span>
            @else
              <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Noliktavā: {{ $product->stock }} gb.</span>
            @endif
          @else
            <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Izpārdots</span>
          @endif
          {{-- Review summary pill --}}
          @if($likes + $dislikes > 0)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
              <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.099-5.5A2 2 0 0015.455 9H13V5a1 1 0 00-1-1 1 1 0 00-1 1v.5L8.5 10a1 1 0 01-.5.866V10.333z"/></svg>
              {{ $likes }}
              <svg class="w-3.5 h-3.5 text-red-400 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.1 5.5A2 2 0 004.545 11H7v4a1 1 0 001 1 1 1 0 001-1v-.5l2.5-5.5a1 1 0 01.5-.866V9.667z"/></svg>
              {{ $dislikes }}
            </span>
          @endif
        </div>

        @if($product->seller_full_name || $product->contact_email || $product->contact_phone || $product->contact || $product->address || $product->delivery_days)
          <div class="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2.5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pārdevēja informācija</p>
            @if($product->seller_full_name)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ $product->seller_full_name }}</span>
              </div>
            @endif
            @if($product->contact_email)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <a href="mailto:{{ $product->contact_email }}" class="hover:text-blue-600 transition">{{ $product->contact_email }}</a>
              </div>
            @endif
            @if($product->contact_phone)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <a href="tel:{{ $product->contact_phone }}" class="hover:text-blue-600 transition">{{ $product->contact_phone }}</a>
              </div>
            @elseif($product->contact)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>{{ $product->contact }}</span>
              </div>
            @endif
            @if($product->address)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ $product->address }}</span>
              </div>
            @endif
            @if($product->delivery_days)
              <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Aptuvens piegādes laiks: <strong>{{ $product->delivery_days }} {{ $product->delivery_days == 1 ? 'diena' : 'dienas' }}</strong></span>
              </div>
            @endif
          </div>
        @endif
      </div>

      @if(session('error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
      @endif

      <div class="mt-6 flex flex-col sm:flex-row gap-3">
        @auth
          @if($product->user_id === auth()->id())
            <p class="text-sm text-gray-400 italic">Jūs esat šī produkta pārdevējs.</p>
          @elseif($product->stock > 0)
            <form method="POST" action="{{ route('products.buy', $product) }}" id="buyForm">
              @csrf
              <button type="submit"
                      class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                Iegādāties
              </button>
            </form>
          @else
            <button disabled
                    class="px-6 py-2.5 bg-gray-200 text-gray-400 font-semibold rounded-xl cursor-not-allowed">
              Izpārdots
            </button>
          @endif
        @else
          <a href="{{ route('login') }}"
             class="px-6 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-xl transition text-center">
            Pieslēgties, lai iegādātos
          </a>
        @endauth
      </div>
    </div>
  </div>

  {{-- Disclaimer --}}
  <div class="mt-8 flex gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
    <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
      <p class="font-semibold mb-1">Svarīga informācija par pirkumu</p>
      <p>VolleyPass darbojas kā platforma pārdevēju un pircēju savienošanai. <strong>VolleyPass nenes atbildību</strong> par preču piegādi, kvalitāti vai saņemšanu. Ja rodas problēmas ar pasūtījumu, lūdzu sazinieties tieši ar pārdevēju, izmantojot norādīto kontaktinformāciju.</p>
    </div>
  </div>

  {{-- Reviews section --}}
  <div class="mt-10">
    <div class="flex items-center gap-4 mb-6">
      <h2 class="text-xl font-bold text-gray-900">Vērtējumi</h2>
      @if($likes + $dislikes > 0)
        <span class="text-sm text-gray-400">({{ $likes + $dislikes }})</span>
      @endif
    </div>

    {{-- Like / Dislike buttons --}}
    @auth
      @if($product->user_id !== auth()->id())
        <div class="flex items-center gap-3 mb-8">
          <form method="POST" action="{{ route('products.reviews.store', $product) }}">
            @csrf
            <input type="hidden" name="vote" value="like">
            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 transition
                      {{ $userReview?->vote === 'like'
                          ? 'border-green-400 bg-green-100 text-green-700'
                          : 'border-gray-200 bg-white text-gray-500 hover:border-green-300 hover:bg-green-50 hover:text-green-600' }}">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.099-5.5A2 2 0 0015.455 9H13V5a1 1 0 00-1-1 1 1 0 00-1 1v.5L8.5 10a1 1 0 01-.5.866V10.333z"/>
              </svg>
              Patīk
              <span class="font-bold {{ $userReview?->vote === 'like' ? 'text-green-700' : 'text-gray-600' }}">{{ $likes }}</span>
            </button>
          </form>

          <form method="POST" action="{{ route('products.reviews.store', $product) }}">
            @csrf
            <input type="hidden" name="vote" value="dislike">
            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 transition
                      {{ $userReview?->vote === 'dislike'
                          ? 'border-red-400 bg-red-100 text-red-700'
                          : 'border-gray-200 bg-white text-gray-500 hover:border-red-300 hover:bg-red-50 hover:text-red-600' }}">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.1 5.5A2 2 0 004.545 11H7v4a1 1 0 001 1 1 1 0 001-1v-.5l2.5-5.5a1 1 0 01.5-.866V9.667z"/>
              </svg>
              Nepatīk
              <span class="font-bold {{ $userReview?->vote === 'dislike' ? 'text-red-700' : 'text-gray-600' }}">{{ $dislikes }}</span>
            </button>
          </form>

          @if($userReview)
            <form method="POST" action="{{ route('products.reviews.destroy', $product) }}"
                  id="removeReviewForm">
              @csrf @method('DELETE')
              <button type="button"
                      onclick="vpConfirm('Noņemt vērtējumu?', () => document.getElementById('removeReviewForm').submit(), { danger: true, confirmText: 'Noņemt' })"
                      class="px-3 py-2.5 rounded-xl text-xs text-gray-400 hover:text-gray-600 border-2 border-transparent hover:border-gray-200 transition">
                Noņemt
              </button>
            </form>
          @endif
        </div>
      @endif
    @else
      <div class="flex items-center gap-4 mb-8">
        <div class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 border-gray-100 bg-gray-50 text-gray-400">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.099-5.5A2 2 0 0015.455 9H13V5a1 1 0 00-1-1 1 1 0 00-1 1v.5L8.5 10a1 1 0 01-.5.866V10.333z"/>
          </svg>
          Patīk <span class="font-bold text-gray-500">{{ $likes }}</span>
        </div>
        <div class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 border-gray-100 bg-gray-50 text-gray-400">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.1 5.5A2 2 0 004.545 11H7v4a1 1 0 001 1 1 1 0 001-1v-.5l2.5-5.5a1 1 0 01.5-.866V9.667z"/>
          </svg>
          Nepatīk <span class="font-bold text-gray-500">{{ $dislikes }}</span>
        </div>
        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Pieslēgties, lai vērtētu</a>
      </div>
    @endauth

</div>

@auth
<script>
document.getElementById('buyForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  vpConfirm(
    'Vai tiešām vēlies iegādāties šo produktu par €{{ number_format($product->price, 2) }}?',
    () => form.submit(),
    { confirmText: 'Iegādāties' }
  );
});
</script>
@endauth
</x-app-layout>
