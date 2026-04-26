@extends('layouts.app')

@section('title', 'Rediģēt arēnu: ' . $arena->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Rediģēt arēnu: {{ $arena->name }}</h1>
        <a href="{{ route('arenas.index') }}" class="text-blue-600 hover:text-blue-800">Atpakaļ uz arēnām</a>
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
