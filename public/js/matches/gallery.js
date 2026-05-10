(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var thumbs     = Array.from(document.querySelectorAll('.gallery-thumb img'));
    var srcs       = thumbs.map(function (i) { return i.src; });
    var modal      = document.getElementById('galleryModal');
    var galleryImg = document.getElementById('galleryImg');
    var counter    = document.getElementById('galleryCounter');
    var idx        = 0;
    var autoplay   = null;

    function openGallery(i) {
      if (!srcs.length) return;
      idx = i || 0;
      galleryImg.src = srcs[idx];
      counter.textContent = (idx + 1) + ' / ' + srcs.length;
      modal.classList.remove('hidden');
      startAutoplay();
    }
    function closeGallery() { modal.classList.add('hidden'); stopAutoplay(); }
    function next() { idx = (idx + 1) % srcs.length; galleryImg.src = srcs[idx]; counter.textContent = (idx + 1) + ' / ' + srcs.length; }
    function prev() { idx = (idx - 1 + srcs.length) % srcs.length; galleryImg.src = srcs[idx]; counter.textContent = (idx + 1) + ' / ' + srcs.length; }
    function startAutoplay() { stopAutoplay(); autoplay = setInterval(next, 3000); }
    function stopAutoplay() { if (autoplay) { clearInterval(autoplay); autoplay = null; } }

    document.querySelectorAll('.gallery-thumb').forEach(function (btn, i) {
      btn.addEventListener('click', function () { openGallery(i); });
    });

    var gc = document.getElementById('galleryClose');
    var gn = document.getElementById('galleryNext');
    var gp = document.getElementById('galleryPrev');
    var gm = document.getElementById('galleryModal');
    if (gc) gc.addEventListener('click', closeGallery);
    if (gn) gn.addEventListener('click', function () { next(); startAutoplay(); });
    if (gp) gp.addEventListener('click', function () { prev(); startAutoplay(); });
    if (gm) gm.addEventListener('click', function (ev) { if (ev.target.id === 'galleryModal') closeGallery(); });
  });
})();
