@extends('layouts.app')

@section('title', 'Rediģēt arēnu: ' . $arena->name)

@section('content')
<div class="max-w-[2000px] mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-8 min-w-0">
        <a href="{{ route('arenas.index') }}"
           class="shrink-0 flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 whitespace-nowrap">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Arēnas
        </a>
        <span class="text-gray-400 shrink-0">/</span>
        <h1 class="text-xl sm:text-3xl font-bold text-gray-900 truncate min-w-0">{{ $arena->name }}</h1>
    </div>

    <div id="save-feedback" class="hidden mb-4 px-4 py-3 rounded"></div>

    @include('arenas._builder', ['mode' => 'edit', 'arena' => $arena])
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var existingElements = @json($arena->elements ?? []);
    var arenaWidth  = {{ $arena->width  ?? 1200 }};
    var arenaHeight = {{ $arena->height ?? 840 }};

    ArenaBuilder.init({
        canvasWidth:  arenaWidth,
        canvasHeight: arenaHeight,
        gridSize:     50,
        elements:     existingElements,
        onSave: function (elements) {
            var name = document.getElementById('arena-name').value.trim();
            if (!name) { alert('Lūdzu, ievadiet arēnas nosaukumu'); return; }

            var feedback = document.getElementById('save-feedback');
            function showFeedback(message, ok) {
                if (!feedback) {
                    if (!ok) { alert(message); }
                    return;
                }
                feedback.className = ok
                    ? 'mb-4 px-4 py-3 rounded bg-green-100 border border-green-400 text-green-700'
                    : 'mb-4 px-4 py-3 rounded bg-red-100 border border-red-400 text-red-700';
                feedback.textContent = message;
                if (ok) {
                    setTimeout(function () { feedback.className = 'hidden mb-4 px-4 py-3 rounded'; }, 3000);
                }
            }
            fetch("{{ route('arenas.update', $arena) }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name:        name,
                    description: document.getElementById('arena-description').value.trim(),
                    layout:      { width: arenaWidth, height: arenaHeight },
                    elements:    elements,
                    width:       arenaWidth,
                    height:      arenaHeight
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showFeedback(data.message || 'Arēna saglabāta!', true);
            })
            .catch(function () {
                showFeedback('Neizdevās saglabāt arēnu.', false);
            });
        }
    });
});
</script>
@endpush
@endsection
