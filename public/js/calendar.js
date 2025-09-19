import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar')

  if (calendarEl) {
    const calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',

      // Prevent duplicate/extra fetches
      lazyFetching: true,

      // Fetch events from your Laravel API
      events: function(fetchInfo, successCallback, failureCallback) {
  fetch('/api/volleyball/calendar')
    .then(response => response.json())
    .then(events => successCallback(events))
    .catch(error => failureCallback(error))
},


      // Event appearance
      eventDisplay: 'block', // cleaner display for month view
      eventTextColor: '#fff', // white text for readability
      eventColor: '#2563eb', // default blue (overridden by API if provided)

      // Handle event clicks
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
