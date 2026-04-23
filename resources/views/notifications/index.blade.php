<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paziņojumi</h1>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:underline">Atzīmēt visus kā lasītus</button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-14 h-14 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <div class="font-medium text-gray-500">Nav paziņojumu</div>
        </div>
    @else
        <div class="space-y-2">
            @foreach($notifications as $n)
                @php $data = $n->data; $read = !is_null($n->read_at); @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $read ? 'border-gray-100' : 'border-blue-200 bg-blue-50/30' }} shadow-sm p-4 flex gap-4">
                    <div class="flex-shrink-0 mt-0.5">
                        @if(($data['type'] ?? '') === 'request_submitted')
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-base">📨</span>
                        @elseif(($data['new_status'] ?? '') === 'accepted')
                            <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-base">✅</span>
                        @elseif(($data['new_status'] ?? '') === 'rejected')
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-base">❌</span>
                        @elseif(($data['new_status'] ?? '') === 'reviewing')
                            <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-base">👁</span>
                        @else
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-base">🔔</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 dark:text-gray-200 {{ $read ? '' : 'font-semibold' }}">{{ $data['message'] ?? '—' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                        @if(!empty($data['link']))
                            <a href="{{ $data['link'] }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">Skatīt pieprasījumu →</a>
                        @endif
                    </div>
                    @if(!$read)
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 mt-0.5" title="Atzīmēt kā lasītu">✓</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif

</div>
</x-app-layout>
