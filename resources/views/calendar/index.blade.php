<x-app-layout>
  <div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Matches Calendar</h1>
    <div id="calendar"></div>
  </div>

  {{-- FullCalendar CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar')

      if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          events: '/api/volleyball/calendar',
          eventClick: function (info) {
            info.jsEvent.preventDefault()
            if (info.event.url) {
              window.location.href = info.event.url
            }
          },
        })

        calendar.render()
      }
    })
  </script>
</x-app-layout>
