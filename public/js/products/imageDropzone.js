/**
 * Shared image drop-zone preview handler.
 * Expects elements: #imageInput, #imagePreview, #imageDropLabel, #imageChosen, #imageDropZone, #imageError
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var input    = document.getElementById('imageInput');
    var preview  = document.getElementById('imagePreview');
    var dropLabel = document.getElementById('imageDropLabel');
    var chosen   = document.getElementById('imageChosen');
    var zone     = document.getElementById('imageDropZone');
    var errorMsg = document.getElementById('imageError');

    if (!input || !zone) return;

    function handleFile(file) {
      if (!file) return;
      if (file.type === 'image/gif') {
        if (errorMsg) errorMsg.classList.remove('hidden');
        input.value = '';
        return;
      }
      if (errorMsg) errorMsg.classList.add('hidden');
      if (!file.type.startsWith('image/')) return;

      var reader = new FileReader();
      reader.onload = function (e) {
        if (preview)  { preview.src = e.target.result; preview.classList.remove('hidden'); }
        if (dropLabel) dropLabel.classList.add('hidden');
        if (chosen)   { chosen.textContent = file.name; chosen.classList.remove('hidden'); }
        zone.classList.add('border-blue-400');
        zone.classList.remove('border-gray-300');
      };
      reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () { handleFile(this.files[0]); });

    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('border-blue-400'); });
    zone.addEventListener('dragleave', function () {
      if (!preview || preview.classList.contains('hidden')) zone.classList.remove('border-blue-400');
    });
    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      var file = e.dataTransfer.files[0];
      if (file) {
        try {
          var dt = new DataTransfer();
          dt.items.add(file);
          input.files = dt.files;
        } catch (_) {}
        handleFile(file);
      }
    });
  });
})();
