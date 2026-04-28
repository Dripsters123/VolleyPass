@extends('layouts.app')

@section('title', 'Izveidot jaunu arēnu')

@section('content')
<div class="max-w-[2000px] mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Izveidot jaunu arēnu</h1>
        <a href="{{ route('arenas.index') }}" class="text-blue-600 hover:text-blue-800">Atpakaļ uz arēnām</a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @include('arenas._builder', ['mode' => 'create'])

    <form id="arena-form" action="{{ route('arenas.store') }}" method="POST" style="display:none">
        @csrf
        <input type="hidden" name="name"        id="form-name">
        <input type="hidden" name="description"  id="form-description">
        <input type="hidden" name="layout"       id="form-layout">
        <input type="hidden" name="elements"     id="form-elements">
        <input type="hidden" name="width"        id="form-width"  value="1200">
        <input type="hidden" name="height"       id="form-height" value="840">
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    ArenaBuilder.init({
        canvasWidth:  1200,
        canvasHeight: 840,
        gridSize:     50,
        elements:     [],
        onSave: function (elements) {
            var name = document.getElementById('arena-name').value.trim();
            if (!name) { alert('Lūdzu, ievadiet arēnas nosaukumu'); return; }

            document.getElementById('form-name').value        = name;
            document.getElementById('form-description').value  = document.getElementById('arena-description').value.trim();
            document.getElementById('form-layout').value       = JSON.stringify({ width: 1200, height: 840 });
            document.getElementById('form-elements').value     = JSON.stringify(elements);
            document.getElementById('arena-form').submit();
        }
    });
});
</script>
@endpush
@endsection
