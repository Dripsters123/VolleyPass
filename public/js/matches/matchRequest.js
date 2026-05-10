/**
 * Match Request form — player fields + team presets + arena selection.
 * Expects: window.VPMatchRequestConfig = { oldHome, oldAway, savedTeams, arenas, preselectedArenaId }
 */
(function () {
  'use strict';

  var cfg = window.VPMatchRequestConfig || {};
  var oldHome = cfg.oldHome || [];
  var oldAway = cfg.oldAway || [];

  /* ── Player fields ─────────────────────────────────────── */
  (function () {
    var select = document.getElementById('players_per_team');
    var container = document.getElementById('playerFields');
    if (!select || !container) return;

    function renderPlayers(n) {
      n = Number(n) || 2;
      var html = '';

      html += '<div class="bg-gray-50 border rounded p-4">';
      html += '<h3 class="font-semibold mb-2">Mājas komanda</h3>';
      for (var i = 0; i < n; i++) {
        var hf = (oldHome[i] && oldHome[i].first_name) ? oldHome[i].first_name : '';
        var hl = (oldHome[i] && oldHome[i].last_name)  ? oldHome[i].last_name  : '';
        html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">' +
          '<div><label class="text-sm">Vārds</label>' +
          '<input name="home_players[' + i + '][first_name]" value="' + escHtml(hf) + '" class="w-full p-2 border rounded" required placeholder="Piem.: J\u0101nis"></div>' +
          '<div><label class="text-sm">Uzv\u0101rds</label>' +
          '<input name="home_players[' + i + '][last_name]"  value="' + escHtml(hl) + '" class="w-full p-2 border rounded" required placeholder="Piem.: B\u0113rzi\u0146\u0161"></div>' +
          '</div>';
      }
      html += '</div>';

      html += '<div class="bg-gray-50 border rounded p-4">';
      html += '<h3 class="font-semibold mb-2">Viesu komanda</h3>';
      for (var j = 0; j < n; j++) {
        var af = (oldAway[j] && oldAway[j].first_name) ? oldAway[j].first_name : '';
        var al = (oldAway[j] && oldAway[j].last_name)  ? oldAway[j].last_name  : '';
        html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">' +
          '<div><label class="text-sm">Vārds</label>' +
          '<input name="away_players[' + j + '][first_name]" value="' + escHtml(af) + '" class="w-full p-2 border rounded" required placeholder="Piem.: J\u0101nis"></div>' +
          '<div><label class="text-sm">Uzv\u0101rds</label>' +
          '<input name="away_players[' + j + '][last_name]"  value="' + escHtml(al) + '" class="w-full p-2 border rounded" required placeholder="Piem.: B\u0113rzi\u0146\u0161"></div>' +
          '</div>';
      }
      html += '</div>';

      container.innerHTML = html;
    }

    renderPlayers(select.value);
    select.addEventListener('change', function (e) { renderPlayers(e.target.value); });

    /* ── Team presets ──────────────────────────────────────── */
    var savedTeams = cfg.savedTeams;
    if (savedTeams) {
      function applyTeam(teamId, side) {
        var team = savedTeams[teamId];
        if (!team) return;
        select.value = team.players_per_team;
        renderPlayers(team.players_per_team);
        var nameInput  = document.querySelector('input[name="' + side + '_team"]');
        var coachInput = document.querySelector('input[name="' + side + '_coach"]');
        if (nameInput  && !nameInput.value)  nameInput.value  = team.name  || '';
        if (coachInput && !coachInput.value) coachInput.value = team.coach || '';
        (team.players || []).forEach(function (p, i) {
          var fn = document.querySelector('input[name="' + side + '_players[' + i + '][first_name]"]');
          var ln = document.querySelector('input[name="' + side + '_players[' + i + '][last_name]"]');
          if (fn) fn.value = p.first_name || '';
          if (ln) ln.value = p.last_name  || '';
        });
      }

      var homeTeamSel = document.getElementById('loadHomeTeam');
      var awayTeamSel = document.getElementById('loadAwayTeam');
      if (homeTeamSel) homeTeamSel.addEventListener('change', function (e) { if (e.target.value) applyTeam(e.target.value, 'home'); });
      if (awayTeamSel) awayTeamSel.addEventListener('change', function (e) { if (e.target.value) applyTeam(e.target.value, 'away'); });
    }
  })();

  /* ── Arena selection ────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    var arenas                = cfg.arenas || [];
    var arenaCards            = document.querySelectorAll('.arena-selection-card');
    var selectedArenaSummary  = document.getElementById('selectedArenaSummary');
    var arenaIdInput          = document.getElementById('arena_id_input');
    var arenaLayoutInput      = document.getElementById('arena-layout-input');
    var arenaElementsInput    = document.getElementById('arena-elements-input');
    var arenaWidthInput       = document.getElementById('arena_width_input');
    var arenaHeightInput      = document.getElementById('arena_height_input');
    var arenaNameInput        = document.getElementById('arena_name');
    var arenaDescriptionInput = document.getElementById('arena_description');
    var resetArenaSelection   = document.getElementById('resetArenaSelection');

    function updateSelectedArena(arena) {
      if (!arena) {
        if (selectedArenaSummary) selectedArenaSummary.innerHTML = '<div class="text-gray-500">Nav atlasīta arēna.</div>';
        if (arenaIdInput)       arenaIdInput.value       = '';
        if (arenaLayoutInput)   arenaLayoutInput.value   = '';
        if (arenaElementsInput) arenaElementsInput.value = '';
        if (arenaWidthInput)    arenaWidthInput.value    = 1000;
        if (arenaHeightInput)   arenaHeightInput.value   = 700;
        arenaCards.forEach(function (card) { card.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500'); });
        return;
      }

      if (selectedArenaSummary) {
        selectedArenaSummary.innerHTML =
          '<div class="space-y-3">' +
            '<div class="text-sm font-semibold text-slate-900">' + escHtml(arena.name) + '</div>' +
            '<div class="text-sm text-gray-600">' + escHtml(arena.description || 'Bez apraksta') + '</div>' +
            '<div class="flex flex-wrap gap-2 text-xs text-slate-500">' +
              '<span class="px-2 py-1 rounded-full bg-slate-100">' + arena.width + ' x ' + arena.height + ' px</span>' +
              '<span class="px-2 py-1 rounded-full bg-slate-100">Saglabāta ar\u0113na</span>' +
            '</div>' +
          '</div>';
      }

      if (arenaIdInput)          arenaIdInput.value          = arena.id;
      if (arenaLayoutInput)      arenaLayoutInput.value      = JSON.stringify(arena.layout   || []);
      if (arenaElementsInput)    arenaElementsInput.value    = JSON.stringify(arena.elements || []);
      if (arenaWidthInput)       arenaWidthInput.value       = arena.width  || 1000;
      if (arenaHeightInput)      arenaHeightInput.value      = arena.height || 700;
      if (arenaNameInput)        arenaNameInput.value        = arena.name;
      if (arenaDescriptionInput) arenaDescriptionInput.value = arena.description || '';

      arenaCards.forEach(function (card) {
        var isSelected = Number(card.dataset.arenaId) === arena.id;
        card.classList.toggle('ring-2',          isSelected);
        card.classList.toggle('ring-blue-500',   isSelected);
        card.classList.toggle('border-blue-500', isSelected);
      });
    }

    arenaCards.forEach(function (card) {
      card.addEventListener('click', function () {
        var arenaId = Number(this.dataset.arenaId);
        var arena = arenas.find(function (a) { return a.id === arenaId; });
        if (arena) updateSelectedArena(arena);
      });
    });

    if (resetArenaSelection) {
      resetArenaSelection.addEventListener('click', function () { updateSelectedArena(null); });
    }

    var preselectedArenaId = cfg.preselectedArenaId || 0;
    if (preselectedArenaId) {
      var arena = arenas.find(function (a) { return a.id === preselectedArenaId; });
      if (arena) updateSelectedArena(arena);
    }
  });

  /* ── Helpers ────────────────────────────────────────────── */
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
})();
