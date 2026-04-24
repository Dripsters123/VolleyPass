<x-app-layout>
  <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

      {{-- CALENDAR REWRITE: custom list view + FC grid on demand --}}
      {{-- Page header --}}
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 px-4 sm:px-0">
        <div>
          <h2 class="font-bold text-3xl text-blue-700 leading-tight">Maču kalendārs</h2>
          <p class="mt-1 text-gray-500 text-sm">Visi mači vienuviet.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button id="prevBtn" class="p-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <span id="calTitle" class="min-w-[140px] text-center text-sm font-semibold text-gray-800"></span>
          <button id="nextBtn" class="p-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
          <div class="flex gap-1 bg-gray-100 p-1 rounded-xl">
            <button id="btn-list"  class="view-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Saraksts</button>
            <button id="btn-month" class="view-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Mēnesis</button>
            <button id="btn-week"  class="view-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Nedēļa</button>
          </div>
          <button id="todayBtn" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">Šodien</button>
        </div>
      </div>

      {{-- Event detail popup --}}
      <div id="eventPopup" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md relative">
          {{-- Drag handle for mobile --}}
          <div class="flex justify-center pt-3 pb-1 sm:hidden">
            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
          </div>
          <div class="p-5 sm:p-6">
            <button id="closePopup" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition p-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div id="popupBadge" class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3"></div>
            <h3 id="popupTitle" class="text-lg font-bold text-gray-900 mb-4 pr-6 leading-snug"></h3>
            <div class="space-y-2.5 text-sm text-gray-600">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span id="popupDate" class="font-medium"></span>
              </div>
              <div id="popupLocationRow" class="hidden flex items-center gap-2.5">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span id="popupLocation"></span>
              </div>
              <div id="popupPriceRow" class="hidden flex items-center gap-2.5">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M12 8V7m0 9v1"/></svg>
                <span id="popupPrice"></span>
              </div>
            </div>
            <a id="popupLink" href="#" class="mt-5 flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
              Skatīt maču
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </div>

      {{-- Custom list view (default) --}}
      <div id="list-wrap" class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div id="list-body" class="divide-y divide-gray-100">
          <div class="py-16 text-center text-gray-400 text-sm">Ielādē...</div>
        </div>
      </div>

      {{-- FullCalendar — only for month / week grid views --}}
      <div id="fc-wrap" class="hidden bg-white rounded-2xl shadow-lg overflow-hidden">
        <div id="calendar" class="p-3 sm:p-6"></div>
      </div>

      {{-- Legend --}}
      <div class="mt-4 flex flex-wrap gap-4 justify-center text-xs text-gray-500 px-4 sm:px-0">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>Gaidāms</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>Rezultāti gaidāmi</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>Pabeigts</span>
      </div>
    </div>
  </div>

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <style>
    /* ── FullCalendar grid (month/week) overrides ── */
    .fc .fc-toolbar        { display: none; }
    .fc-scrollgrid         { border: none !important; }
    .fc-scrollgrid td,
    .fc-scrollgrid th      { border-color: #f1f5f9 !important; }
    .fc .fc-daygrid-day-number,
    .fc .fc-col-header-cell-cushion { color: #374151; text-decoration: none; font-size: 0.8rem; }
    .fc .fc-day-today      { background: #eff6ff !important; }
    .fc .fc-day-today .fc-daygrid-day-number { font-weight: 700; color: #2563eb; }
    .fc .fc-timegrid-slot  { height: 2.5em; }

    /* Grid events */
    .fc-daygrid-event,
    .fc-timegrid-event     { border: none !important; border-radius: 6px !important; padding: 3px 6px !important; margin-bottom: 2px !important; cursor: pointer; }
    .fc-daygrid-event:hover,
    .fc-timegrid-event:hover { filter: brightness(0.9); }
    .fc .fc-event-title    { font-size: 0.75rem; font-weight: 600; }
    .fc .fc-event-time     { font-size: 0.7rem; font-weight: 700; }
    .fc .fc-daygrid-more-link { font-size: 0.7rem; font-weight: 600; color: #6b7280; }
    .fc .fc-daygrid-more-link:hover { color: #2563eb; }
    .fc .fc-popover        { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 8px 30px rgba(0,0,0,.12); z-index: 40; }
    .fc .fc-popover-header { background: #f8fafc; border-radius: 12px 12px 0 0; padding: 8px 12px; }
  </style>

  <script>
  document.addEventListener('DOMContentLoaded', function () {

    /* ── Latvian labels ── */
    var LV_MONTHS     = ['Janvāris','Februāris','Marts','Aprīlis','Maijs','Jūnijs','Jūlijs','Augusts','Septembris','Oktobris','Novembris','Decembris'];
    var LV_MONTHS_GEN = ['janvāra','februāra','marta','aprīļa','maija','jūnija','jūlija','augusta','septembra','oktobra','novembra','decembra'];
    var LV_WEEKDAYS   = ['Svētdiena','Pirmdiena','Otrdiena','Trešdiena','Ceturtdiena','Piektdiena','Sestdiena'];

    /* ── State ── */
    var allEvents  = [];
    var curView    = 'list';
    var listYear   = new Date().getFullYear();
    var listMonth  = new Date().getMonth();   // 0-based
    var weekStart  = mondayOf(new Date());    // Date: start of current week
    var fcInited   = false;
    var fc         = null;
    var eventsUrl  = '{{ route("calendar.events") }}';

    /* ── Monday of a given date's week ── */
    function mondayOf(d) {
      var day = d.getDay();                       // 0=Sun … 6=Sat
      var diff = (day === 0 ? -6 : 1 - day);     // offset to Monday
      var m = new Date(d);
      m.setHours(0, 0, 0, 0);
      m.setDate(m.getDate() + diff);
      return m;
    }
    /* ── DOM refs ── */
    var listWrap = document.getElementById('list-wrap');
    var listBody = document.getElementById('list-body');
    var fcWrap   = document.getElementById('fc-wrap');
    var calTitle = document.getElementById('calTitle');
    var btnList  = document.getElementById('btn-list');
    var btnMonth = document.getElementById('btn-month');
    var btnWeek  = document.getElementById('btn-week');

    /* ── Helpers ── */
    function statusColor(s) {
      return s === 'completed' ? '#10b981' : s === 'results_pending' ? '#f59e0b' : '#3b82f6';
    }
    function statusBorderClass(s) {
      return s === 'completed' ? 'border-emerald-400' : s === 'results_pending' ? 'border-amber-400' : 'border-blue-500';
    }
    function statusBadgeClass(s) {
      return s === 'completed'
        ? 'bg-emerald-100 text-emerald-700'
        : s === 'results_pending' ? 'bg-amber-100 text-amber-700'
        : 'bg-blue-100 text-blue-700';
    }
    function statusLabel(s) {
      return s === 'completed' ? 'Pabeigts' : s === 'results_pending' ? 'Rezultāti gaidāmi' : 'Gaidāms';
    }
    function fmtTime(d) {
      if (!d) return '';
      return d.toLocaleTimeString('lv-LV', { hour: '2-digit', minute: '2-digit' });
    }
    function dateKey(d) {
      return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function escHtml(s) {
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    /* ── Fetch events once ── */
    fetch(eventsUrl)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        allEvents = data.map(function (ev) {
          var s = (ev.extendedProps || {}).status || 'upcoming';
          return Object.assign({}, ev, { _status: s, _start: ev.start ? new Date(ev.start) : null });
        });
        renderList();
      })
      .catch(function () {
        listBody.innerHTML = '<div class="py-16 text-center text-gray-400 text-sm">Neizdevās ielādēt mačus.</div>';
      });

    /* ── Shared event-row renderer ── */
    function buildRows(filtered, emptyMsg) {
      if (filtered.length === 0) {
        return '<div class="py-16 text-center">' +
          '<div class="text-4xl mb-3">📅</div>' +
          '<p class="text-gray-500 text-sm font-medium">' + emptyMsg + '</p>' +
        '</div>';
      }

      var groups = {};
      filtered.forEach(function (ev) {
        var k = dateKey(ev._start);
        if (!groups[k]) groups[k] = [];
        groups[k].push(ev);
      });
      var keys = Object.keys(groups).sort();
      var html = '';

      keys.forEach(function (k) {
        var d   = new Date(k + 'T00:00:00');
        var wd  = LV_WEEKDAYS[d.getDay()];
        var mon = LV_MONTHS_GEN[d.getMonth()];

        html += '<div class="px-4 sm:px-6 py-2.5 bg-slate-50 border-b border-slate-100">' +
          '<span class="text-xs font-bold uppercase tracking-wider text-slate-500">' +
            wd + ', ' + d.getDate() + '. ' + mon +
          '</span>' +
        '</div>';

        groups[k].forEach(function (ev) {
          var s    = ev._status;
          var time = fmtTime(ev._start);
          var bord = statusBorderClass(s);
          var loc  = ev.extendedProps && ev.extendedProps.location ? ev.extendedProps.location : '';

          html += '<button type="button" data-ev-title="' + escHtml(ev.title) + '" data-ev-date="' + k + '" ' +
            'class="ev-card w-full text-left flex items-center gap-4 px-4 sm:px-6 py-4 hover:bg-blue-50 transition-colors border-l-4 ' + bord + '">' +
            '<span class="text-sm font-extrabold text-gray-700 tabular-nums min-w-[42px] shrink-0">' + time + '</span>' +
            '<span class="flex-1 min-w-0">' +
              '<span class="block font-semibold text-gray-900 text-base leading-snug">' + escHtml(ev.title) + '</span>' +
              (loc ? '<span class="block text-xs text-gray-400 mt-0.5">' + escHtml(loc) + '</span>' : '') +
            '</span>' +
            '<svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>' +
          '</button>';
        });
      });
      return html;
    }

    function attachCardClicks() {
      listBody.querySelectorAll('.ev-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var title = btn.dataset.evTitle;
          var key   = btn.dataset.evDate;
          var ev = allEvents.find(function (e) {
            return e.title === title && e._start && dateKey(e._start) === key;
          });
          if (ev) openPopup(ev);
        });
      });
    }

    /* ── Month list renderer ── */
    function renderList() {
      calTitle.textContent = LV_MONTHS[listMonth] + ' ' + listYear;
      var filtered = allEvents.filter(function (ev) {
        return ev._start && ev._start.getFullYear() === listYear && ev._start.getMonth() === listMonth;
      });
      listBody.innerHTML = buildRows(filtered, 'Šajā mēnesī maču nav.');
      attachCardClicks();
    }

    /* ── Week list renderer ── */
    function renderWeek() {
      var end = new Date(weekStart);
      end.setDate(end.getDate() + 6);
      end.setHours(23, 59, 59, 999);

      // Title: "21. apr – 27. apr" or "28. apr – 4. maijs"
      var startDay = weekStart.getDate() + '. ' + LV_MONTHS_GEN[weekStart.getMonth()];
      var endDay   = end.getDate() + '. ' + LV_MONTHS_GEN[end.getMonth()];
      var yr       = weekStart.getFullYear() !== new Date().getFullYear() ? ' ' + weekStart.getFullYear() : '';
      calTitle.textContent = startDay + ' – ' + endDay + yr;

      var filtered = allEvents.filter(function (ev) {
        return ev._start && ev._start >= weekStart && ev._start <= end;
      });
      listBody.innerHTML = buildRows(filtered, 'Šajā nedēļā maču nav.');
      attachCardClicks();
    }

    /* ── Popup ── */
    function openPopup(ev) {
      var s     = ev._status || 'upcoming';
      var props = ev.extendedProps || {};
      var badge = document.getElementById('popupBadge');
      badge.textContent = statusLabel(s);
      badge.className   = 'inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3 ' + statusBadgeClass(s);
      document.getElementById('popupTitle').textContent = ev.title;
      document.getElementById('popupDate').textContent  = ev._start
        ? ev._start.toLocaleString('lv-LV', { weekday:'long', day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' })
        : '';
      var locRow = document.getElementById('popupLocationRow');
      if (props.location) { document.getElementById('popupLocation').textContent = props.location; locRow.classList.remove('hidden'); }
      else { locRow.classList.add('hidden'); }
      var priceRow = document.getElementById('popupPriceRow');
      if (props.price != null) { document.getElementById('popupPrice').textContent = '€' + parseFloat(props.price).toFixed(2); priceRow.classList.remove('hidden'); }
      else { priceRow.classList.add('hidden'); }
      var link = document.getElementById('popupLink');
      if (ev.url) { link.href = ev.url; link.classList.remove('hidden'); }
      else { link.classList.add('hidden'); }
      document.getElementById('eventPopup').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closePopup() {
      document.getElementById('eventPopup').classList.add('hidden');
      document.body.style.overflow = '';
    }
    document.getElementById('closePopup').addEventListener('click', closePopup);
    document.getElementById('eventPopup').addEventListener('click', function (e) { if (e.target === this) closePopup(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePopup(); });

    /* ── FullCalendar (lazy, only for month grid) ── */
    function initFC() {
      if (fcInited) return;
      fcInited = true;
      fc = new FullCalendar.Calendar(document.getElementById('calendar'), {
        locale: 'lv', height: 'auto', firstDay: 1, dayMaxEventRows: 4,
        headerToolbar: false, nowIndicator: true,
        moreLinkContent: function (a) { return '+' + a.num + ' vairāk'; },
        events: function (info, ok) {
          ok(allEvents.map(function (ev) {
            var c = statusColor(ev._status);
            return Object.assign({}, ev, { start: ev._start ? ev._start.toISOString() : null, backgroundColor: c, borderColor: c, textColor: '#fff' });
          }));
        },
        eventClick: function (info) {
          info.jsEvent.preventDefault();
          var ev = allEvents.find(function (e) { return e.title === info.event.title; });
          if (ev) openPopup(ev);
        },
        datesSet: function () { calTitle.textContent = fc.view.title; }
      });
      fc.render();
    }

    /* ── View switching ── */
    function setActiveBtn(id) {
      [btnList, btnMonth, btnWeek].forEach(function (b) {
        b.className = 'view-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all ' +
          (b.id === id ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700');
      });
    }

    btnList.addEventListener('click', function () {
      curView = 'list';
      if (fc) { var d = fc.getDate(); listYear = d.getFullYear(); listMonth = d.getMonth(); }
      listWrap.classList.remove('hidden');
      fcWrap.classList.add('hidden');
      setActiveBtn('btn-list');
      renderList();
    });
    btnMonth.addEventListener('click', function () {
      curView = 'month';
      listWrap.classList.add('hidden');
      fcWrap.classList.remove('hidden');
      setActiveBtn('btn-month');
      initFC();
      fc.gotoDate(new Date(listYear, listMonth, 1));
      fc.changeView('dayGridMonth');
    });
    btnWeek.addEventListener('click', function () {
      curView = 'week';
      // Sync weekStart to current listMonth so navigation feels natural
      weekStart = mondayOf(new Date(listYear, listMonth, 1));
      fcWrap.classList.add('hidden');
      listWrap.classList.remove('hidden');
      setActiveBtn('btn-week');
      renderWeek();
    });

    /* ── Navigation ── */
    document.getElementById('prevBtn').addEventListener('click', function () {
      if (curView === 'list') {
        if (--listMonth < 0)  { listMonth = 11; listYear--; }
        renderList();
      } else if (curView === 'week') {
        weekStart.setDate(weekStart.getDate() - 7);
        renderWeek();
      } else if (fc) { fc.prev(); }
    });
    document.getElementById('nextBtn').addEventListener('click', function () {
      if (curView === 'list') {
        if (++listMonth > 11) { listMonth = 0; listYear++; }
        renderList();
      } else if (curView === 'week') {
        weekStart.setDate(weekStart.getDate() + 7);
        renderWeek();
      } else if (fc) { fc.next(); }
    });
    document.getElementById('todayBtn').addEventListener('click', function () {
      if (curView === 'list') {
        listYear = new Date().getFullYear(); listMonth = new Date().getMonth();
        renderList();
      } else if (curView === 'week') {
        weekStart = mondayOf(new Date());
        renderWeek();
      } else if (fc) { fc.today(); }
    });

    /* ── Boot ── */
    setActiveBtn('btn-list');
  });
  </script>
</x-app-layout>
