(function () {
  'use strict';

  function scaleArenaPreview() {
    var wrap    = document.getElementById('arena-preview-wrap');
    var preview = document.getElementById('arena-preview');
    if (!wrap || !preview) return;
    var container = wrap.parentElement;
    var available = container ? container.clientWidth - 40 : window.innerWidth - 40;
    var previewW  = preview.offsetWidth || parseInt(preview.style.width) || 600;
    var scale     = previewW > available ? available / previewW : 1;
    preview.style.transformOrigin = 'top left';
    preview.style.transform = scale < 1 ? 'scale(' + scale + ')' : '';
    wrap.style.height = scale < 1
      ? Math.round((preview.offsetHeight || parseInt(preview.style.height) || 420) * scale) + 'px'
      : '';
  }

  document.addEventListener('DOMContentLoaded', scaleArenaPreview);
  window.addEventListener('resize', scaleArenaPreview);
})();
