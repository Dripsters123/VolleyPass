<x-app-layout>
  <div class="container mx-auto py-6 px-2">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
      <h1 class="text-3xl font-extrabold text-blue-800 text-center sm:text-left mb-2 sm:mb-0">Maču kalendārs</h1>
      <div class="flex items-center justify-center sm:justify-start gap-2">
        <button id="prevBtn" class="bg-blue-600 text-white px-3 py-1 rounded">←</button>
        <button id="nextBtn" class="bg-blue-600 text-white px-3 py-1 rounded">→</button>
        <select id="viewSelect" class="border rounded px-2 py-1">
          <option value="dayGridMonth">Mēnesis</option>
          <option value="listWeek">Saraksts</option>
        </select>
      </div>
    </div>
    <div id="calendar" class="bg-white rounded-xl shadow-lg p-2 sm:p-4"></div>
  </div>

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <style>
    .fc-toolbar-title { font-size: 1.5rem; font-weight: 700; }
    .fc-event { font-size: 0.9rem; line-height: 1.3rem; padding: 0.2rem 0.4rem; border-radius: 0.35rem; }
    .fc-scroller { max-height: 450px; overflow-y: auto; }
    @media (max-width: 640px) {
      .fc-toolbar-title { font-size: 1.25rem; }
      .fc-event { font-size: 0.85rem; }
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');

      const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'lv',
        height: 'auto',
        firstDay: 1,
        dayMaxEventRows: 4,
        events: function(fetchInfo, successCallback, failureCallback) {
          fetch('{{ route('calendar.events') }}')
            .then(res => res.json())
            .then(data => successCallback(data))
            .catch(() => failureCallback());
        },
        eventClick: function(info) {
          info.jsEvent.preventDefault();
          if(info.event.url) window.location.href = info.event.url;
        },
        headerToolbar: false,
        views: {
          dayGridMonth: { dayMaxEventRows: 4 },
          listWeek: { buttonText: 'Saraksts' }
        }
      });

      calendar.render();

      const viewSelect = document.getElementById('viewSelect');
      viewSelect.addEventListener('change', function() {
        calendar.changeView(this.value);
      });

      document.getElementById('prevBtn').addEventListener('click', () => calendar.prev());
      document.getElementById('nextBtn').addEventListener('click', () => calendar.next());
    });
  </script>
</x-app-layout>
