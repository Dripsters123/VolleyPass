// Must be global — used by oninput="limitScoreInput(this)" in inline HTML
window.limitScoreInput = function limitScoreInput(el) {
  var sanitized = (el.value || '').replace(/\D/g, '').slice(0, 3);
  if (sanitized === '') { el.value = ''; return; }
  var value = Math.min(parseInt(sanitized, 10), 100);
  el.value = Number.isNaN(value) ? '' : String(value);
};

(function () {
  'use strict';

  function wireSetButtons(addBtnId, removeBtnId, containerId) {
    var addBtn    = document.getElementById(addBtnId);
    var removeBtn = document.getElementById(removeBtnId);
    var container = document.getElementById(containerId);
    if (!addBtn || !removeBtn || !container) return;

    var setCount = container.querySelectorAll('.set-row').length;

    addBtn.addEventListener('click', function () {
      if (setCount >= 5) { alert('Maksimālais setu skaits ir 5!'); return; }
      setCount++;
      var div = document.createElement('div');
      div.classList.add('grid', 'grid-cols-3', 'gap-2', 'items-center', 'set-row');
      div.innerHTML =
        '<label class="text-xs text-gray-600 text-center">Sets ' + setCount + '</label>' +
        '<input type="number" name="sets[' + setCount + '][home]" min="0" max="100" inputmode="numeric"' +
          ' oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Mājas" required>' +
        '<input type="number" name="sets[' + setCount + '][away]" min="0" max="100" inputmode="numeric"' +
          ' oninput="limitScoreInput(this)" class="p-2 border rounded text-center" placeholder="Viesu" required>';
      container.appendChild(div);
    });

    removeBtn.addEventListener('click', function () {
      if (container.querySelectorAll('.set-row').length > 3) {
        container.lastElementChild.remove();
        setCount--;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    wireSetButtons('addSetBtn', 'removeSetBtn', 'setsContainer');
    wireSetButtons('adminAddSetBtn', 'adminRemoveSetBtn', 'adminSetsContainer');

    // Hide right sidebar on mobile (panel is exposed via other UI)
    var rightPanel = document.getElementById('rightPanel');
    if (rightPanel && window.innerWidth < 768) rightPanel.style.display = 'none';
  });
})();
