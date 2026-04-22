
(function () {
    'use strict';

    /* ── constants ─────────────────────────────────────────────── */
    let GRID_SIZE, SEAT_SIZE, SEAT_PAD, CANVAS_W, CANVAS_H;

    /* ── state ─────────────────────────────────────────────────── */
    let elements       = [];
    let selectedEl     = null;
    let dragEl         = null;
    let dragOffset     = { x: 0, y: 0 };
    let dragStart      = null;
    let notifyTimer    = null;
    let onSaveCb       = null;

    /* ── DOM refs ──────────────────────────────────────────────── */
    let canvas, wrapper, propPanel, notifEl;
    let totalInput, colsInput, rowsInput;

    /* ══════════════════════════════════════════════════════════════
       PUBLIC  init
       ══════════════════════════════════════════════════════════════ */
    function init(opts) {
        GRID_SIZE = opts.gridSize  || 50;
        SEAT_SIZE = 44;
        SEAT_PAD  = (GRID_SIZE - SEAT_SIZE) / 2;
        CANVAS_W  = opts.canvasWidth  || 1200;
        CANVAS_H  = opts.canvasHeight || 840;
        elements  = Array.isArray(opts.elements) ? JSON.parse(JSON.stringify(opts.elements)) : [];
        onSaveCb  = opts.onSave || function () {};

        canvas     = document.getElementById('arena-canvas');
        wrapper    = document.querySelector('.canvas-wrapper');
        propPanel  = document.getElementById('element-properties');
        notifEl    = document.getElementById('builder-notification');
        totalInput = document.getElementById('seat-count');
        colsInput  = document.getElementById('seat-columns');
        rowsInput  = document.getElementById('seat-rows');

        canvas.style.width  = CANVAS_W + 'px';
        canvas.style.height = CANVAS_H + 'px';
        canvas.style.backgroundSize = GRID_SIZE + 'px ' + GRID_SIZE + 'px';

        document.getElementById('canvas-size').textContent =
            CANVAS_W + ' × ' + CANVAS_H + ' px';

        bindEvents();
        render();
        recalcRows();
    }

    /* ══════════════════════════════════════════════════════════════
       EVENT  binding
       ══════════════════════════════════════════════════════════════ */
    function bindEvents() {
        /* palette buttons */
        document.querySelectorAll('.palette-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var type = this.dataset.type;
                var defs = defaults(type);
                var cx = (CANVAS_W - defs.width) / 2;
                var cy = (CANVAS_H - defs.height) / 2;
                addElement(type, cx, cy);
            });
        });

        /* grid generator */
        var genBtn = document.getElementById('generate-grid');
        if (genBtn) genBtn.addEventListener('click', generateGrid);

        /* save */
        var saveBtn = document.getElementById('save-arena');
        if (saveBtn) saveBtn.addEventListener('click', function () {
            onSaveCb(elements);
        });

        /* clear */
        var clearBtn = document.getElementById('clear-canvas');
        if (clearBtn) clearBtn.addEventListener('click', function () {
            if (!confirm('Clear all elements?')) return;
            elements = [];
            render();
            select(null);
            notify('All elements cleared', 'success');
        });

        /* grid size */
        var gridSlider = document.getElementById('grid-size-slider');
        if (gridSlider) {
            gridSlider.addEventListener('input', function () {
                var val = parseInt(this.value, 10);
                GRID_SIZE = val;
                SEAT_PAD  = (GRID_SIZE - SEAT_SIZE) / 2;
                canvas.style.backgroundSize = GRID_SIZE + 'px ' + GRID_SIZE + 'px';
                document.getElementById('grid-size-label').textContent = val + 'px';
                // Re-snap all existing elements to the new grid
                elements.forEach(function (el) {
                    el.x = clamp(snap(el.x, el.width),  0, CANVAS_W - el.width);
                    el.y = clamp(snapY(el.y, el.height), 0, CANVAS_H - el.height);
                });
                render();
            });
        }

        if (totalInput) totalInput.addEventListener('input', recalcRows);
        if (colsInput)  colsInput.addEventListener('input', recalcRows);

        /* canvas events */
        canvas.addEventListener('click', onCanvasClick);
        canvas.addEventListener('mousedown', onMouseDown);
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup',   onMouseUp);
        canvas.addEventListener('touchstart', onTouchStart, { passive: true });
        document.addEventListener('touchmove', onTouchMove, { passive: true });
        document.addEventListener('touchend',  onTouchEnd,  { passive: true });
    }

    /* ── canvas interaction ─────────────────────────────────────── */
    function onCanvasClick(e) {
        if (e.target === canvas) { select(null); return; }
        var el = e.target.closest('.arena-element');
        if (el) select(el);
    }

    function onMouseDown(e) {
        var el = e.target.closest('.arena-element');
        if (!el) return;
        startDrag(el, e.clientX, e.clientY);
    }
    function onMouseMove(e) { moveDrag(e.clientX, e.clientY); }
    function onMouseUp()    { endDrag(); }

    function onTouchStart(e) {
        var t = e.touches[0];
        var el = t.target.closest('.arena-element');
        if (!el) return;
        startDrag(el, t.clientX, t.clientY);
    }
    function onTouchMove(e) {
        if (!dragEl) return;
        var t = e.touches[0];
        moveDrag(t.clientX, t.clientY);
    }
    function onTouchEnd() { endDrag(); }

    function startDrag(el, cx, cy) {
        dragEl = el;
        var d = dataFor(el);
        dragStart = d ? { x: d.x, y: d.y } : null;
        var r = el.getBoundingClientRect();
        dragOffset.x = cx - r.left;
        dragOffset.y = cy - r.top;
        el.style.cursor = 'grabbing';
    }

    function moveDrag(cx, cy) {
        if (!dragEl) return;
        var cr = canvas.getBoundingClientRect();
        var er = dragEl.getBoundingClientRect();
        var x = Math.max(0, Math.min(cx - cr.left - dragOffset.x, cr.width  - er.width));
        var y = Math.max(0, Math.min(cy - cr.top  - dragOffset.y, cr.height - er.height));
        dragEl.style.left = x + 'px';
        dragEl.style.top  = y + 'px';
    }

    function endDrag() {
        if (!dragEl) return;
        dragEl.style.cursor = 'grab';
        commitDrag(dragEl);
        dragEl = null;
        dragStart = null;
    }

    function commitDrag(el) {
        var d = dataFor(el);
        if (!d) return;
        var x = parseFloat(el.style.left);
        var y = parseFloat(el.style.top);

        x = snap(x, d.width);
        y = snapY(y, d.height);

        if (d.type === 'seat') {
            /* overlap with court? */
            var court = elements.find(function (e) { return e.type === 'court'; });
            if (court && rectsOverlap({ x: x, y: y, width: d.width, height: d.height }, court)) {
                notify('Seats cannot overlap the court', 'error');
                if (dragStart) { x = dragStart.x; y = dragStart.y; }
            }
            /* overlap with another seat? */
            var clash = elements.find(function (e) {
                return e.type === 'seat' && e.id !== d.id &&
                    rectsOverlap({ x: x, y: y, width: d.width, height: d.height }, e);
            });
            if (clash) {
                notify('Seats cannot overlap each other', 'error');
                if (dragStart) { x = dragStart.x; y = dragStart.y; }
            }
        }

        d.x = clamp(x, 0, CANVAS_W - d.width);
        d.y = clamp(y, 0, CANVAS_H - d.height);
        render();
    }

    /* ══════════════════════════════════════════════════════════════
       ELEMENT  management
       ══════════════════════════════════════════════════════════════ */
    function addElement(type, x, y) {
        if (type === 'court' && elements.some(function (e) { return e.type === 'court'; })) {
            notify('Only one court allowed', 'error');
            return;
        }

        var defs = defaults(type);
        var sx = snap(x, defs.width);
        var sy = snapY(y, defs.height);

        if (type === 'seat') {
            var spot = findFreeSpot(sx, sy, defs.width, defs.height);
            if (!spot) { notify('No free space for seat', 'error'); return; }
            sx = spot.x; sy = spot.y;
        }

        var el = {
            id:     uid(),
            type:   type,
            x:      clamp(sx, 0, CANVAS_W - defs.width),
            y:      clamp(sy, 0, CANVAS_H - defs.height),
            width:  defs.width,
            height: defs.height,
            label:  defs.label || '',
            price:  defs.price || null,
            number: defs.number || '',
            orientation: defs.orientation || null
        };

        elements.push(el);
        render();
        select(document.querySelector('[data-id="' + el.id + '"]'));
        notify((type === 'seat' ? 'Seat' : 'Court') + ' added', 'success');
    }

    function deleteElement(id) {
        elements = elements.filter(function (e) { return e.id != id; });
        render();
        select(null);
        notify('Element deleted', 'success');
    }

    function generateGrid() {
        var total = Math.max(1, parseInt(totalInput.value, 10) || 48);
        var cols  = Math.max(1, parseInt(colsInput.value, 10)  || 8);
        var court = elements.find(function (e) { return e.type === 'court'; });
        elements = court ? [court] : [];

        var placed = 0, row = 0, col = 0;
        while (placed < total) {
            var x = col * GRID_SIZE + SEAT_PAD;
            var y = row * GRID_SIZE + SEAT_PAD;
            if (y + SEAT_SIZE > CANVAS_H) break;
            if (x + SEAT_SIZE > CANVAS_W) { col = 0; row++; continue; }

            var sr = { x: x, y: y, width: SEAT_SIZE, height: SEAT_SIZE };
            var ok = !court || !rectsOverlap(sr, court);
            if (ok) {
                var lbl = String.fromCharCode(65 + row) + (col + 1);
                elements.push({
                    id: uid(), type: 'seat',
                    x: x, y: y, width: SEAT_SIZE, height: SEAT_SIZE,
                    price: 10, number: lbl, label: lbl
                });
                placed++;
            }
            col++;
            if (col >= cols) { col = 0; row++; }
        }
        render();
        select(null);
        recalcRows();
        notify(placed + ' seats generated', 'success');
    }

    /* ── rendering ──────────────────────────────────────────────── */
    function render() {
        canvas.innerHTML = '';
        elements.forEach(function (el) {
            var div = document.createElement('div');
            div.className = 'arena-element ' + el.type + '-element';
            div.dataset.id   = el.id;
            div.dataset.type = el.type;
            div.style.left   = el.x + 'px';
            div.style.top    = el.y + 'px';
            div.style.width  = el.width  + 'px';
            div.style.height = el.height + 'px';
            div.textContent  = el.number || el.label || el.type;
            canvas.appendChild(div);
        });
    }

    /* ── selection / properties ──────────────────────────────────── */
    function select(domEl) {
        document.querySelectorAll('.arena-element.selected')
            .forEach(function (e) { e.classList.remove('selected'); });
        selectedEl = null;
        if (!domEl) { propPanel.innerHTML = 'Click an element to edit its properties'; return; }
        domEl.classList.add('selected');
        selectedEl = domEl;
        showProps(domEl);
    }

    function showProps(domEl) {
        var d = dataFor(domEl);
        if (!d) return;
        var h = '<strong>' + d.type.toUpperCase() + '</strong><br>';

        if (d.type === 'seat') {
            h += 'Label: <input type="text" value="' + esc(d.number || '') + '" '
               + 'onchange="ArenaBuilder.setProp(\'' + d.id + '\',\'number\',this.value)" '
               + 'class="w-full p-1 border rounded mb-1"><br>';
            h += 'Price: €<input type="number" value="' + (d.price || 10) + '" step="0.01" '
               + 'onchange="ArenaBuilder.setProp(\'' + d.id + '\',\'price\',this.value)" '
               + 'class="w-full p-1 border rounded mb-1">';
        } else if (d.type === 'court') {
            h += 'Label: <input type="text" value="' + esc(d.label || '') + '" '
               + 'onchange="ArenaBuilder.setProp(\'' + d.id + '\',\'label\',this.value)" '
               + 'class="w-full p-1 border rounded mb-1"><br>';
            h += 'Orientation: <select onchange="ArenaBuilder.setProp(\'' + d.id + '\',\'orientation\',this.value)" class="w-full p-1 border rounded mb-1">'
               + '<option value="horizontal"' + (d.width >= d.height ? ' selected' : '') + '>Horizontal</option>'
               + '<option value="vertical"'   + (d.width <  d.height ? ' selected' : '') + '>Vertical</option>'
               + '</select><br>';
            h += 'Width: '
               + '<button type="button" onclick="ArenaBuilder.courtSize(\'' + d.id + '\',\'width\',-10)" class="px-2 py-1 bg-slate-200 rounded text-xs">-</button> '
               + '<span>' + d.width + 'px</span> '
               + '<button type="button" onclick="ArenaBuilder.courtSize(\'' + d.id + '\',\'width\',10)" class="px-2 py-1 bg-slate-200 rounded text-xs">+</button><br>';
            h += 'Height: '
               + '<button type="button" onclick="ArenaBuilder.courtSize(\'' + d.id + '\',\'height\',-10)" class="px-2 py-1 bg-slate-200 rounded text-xs">-</button> '
               + '<span>' + d.height + 'px</span> '
               + '<button type="button" onclick="ArenaBuilder.courtSize(\'' + d.id + '\',\'height\',10)" class="px-2 py-1 bg-slate-200 rounded text-xs">+</button>';
        }

        h += '<br><button onclick="ArenaBuilder.deleteEl(\'' + d.id + '\')" '
           + 'class="mt-2 w-full px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">Delete</button>';
        propPanel.innerHTML = h;
    }

    /* ── public property setters (called from inline handlers) ──── */
    function setProp(id, prop, val) {
        var el = elements.find(function (e) { return e.id == id; });
        if (!el) return;
        if (prop === 'price') {
            el.price = parseFloat(val) || 0;
        } else if (prop === 'orientation' && el.type === 'court') {
            var wantV = val === 'vertical';
            var isV   = el.width < el.height;
            if (wantV !== isV) { var t = el.width; el.width = el.height; el.height = t; }
        } else {
            el[prop] = val;
        }
        render();
        var dom = document.querySelector('[data-id="' + id + '"]');
        if (dom) select(dom);
    }

    function courtSize(id, prop, delta) {
        var el = elements.find(function (e) { return e.id == id && e.type === 'court'; });
        if (!el) return;
        el[prop] = Math.max(80, el[prop] + delta);
        el.x = clamp(snap(el.x, el.width),  0, CANVAS_W - el.width);
        el.y = clamp(snapY(el.y, el.height), 0, CANVAS_H - el.height);
        render();
        var dom = document.querySelector('[data-id="' + id + '"]');
        if (dom) select(dom);
    }

    /* ══════════════════════════════════════════════════════════════
       HELPERS
       ══════════════════════════════════════════════════════════════ */
    function defaults(type) {
        if (type === 'seat')  return { width: SEAT_SIZE, height: SEAT_SIZE, price: 10, number: '', label: '' };
        if (type === 'court') return { width: 260, height: 150, label: 'Volleyball Court', orientation: 'horizontal' };
        return { width: 50, height: 50 };
    }

    function recalcRows() {
        if (!totalInput || !colsInput || !rowsInput) return;
        var t = Math.max(1, parseInt(totalInput.value, 10) || 1);
        var c = Math.max(1, parseInt(colsInput.value, 10)  || 1);
        rowsInput.value = Math.ceil(t / c);
    }

    function snap(v, size)  { var o = (GRID_SIZE - size) / 2; return Math.round((v - o) / GRID_SIZE) * GRID_SIZE + o; }
    function snapY(v, size) { var o = (GRID_SIZE - size) / 2; return Math.round((v - o) / GRID_SIZE) * GRID_SIZE + o; }

    function rectsOverlap(a, b) {
        return a.x < b.x + b.width  && a.x + a.width  > b.x &&
               a.y < b.y + b.height && a.y + a.height > b.y;
    }

    function findFreeSpot(x, y, w, h) {
        var r = { x: x, y: y, width: w, height: h };
        if (!isBlocked(r)) return { x: x, y: y };
        for (var d = GRID_SIZE; d < Math.max(CANVAS_W, CANVAS_H); d += GRID_SIZE) {
            var tries = [
                { x: x + d, y: y }, { x: x - d, y: y },
                { x: x, y: y + d }, { x: x, y: y - d }
            ];
            for (var i = 0; i < tries.length; i++) {
                var t = tries[i];
                if (t.x < 0 || t.y < 0 || t.x + w > CANVAS_W || t.y + h > CANVAS_H) continue;
                if (!isBlocked({ x: t.x, y: t.y, width: w, height: h })) return t;
            }
        }
        return null;
    }

    function isBlocked(r) {
        return elements.some(function (e) { return rectsOverlap(r, e); });
    }

    function dataFor(dom) {
        var id = dom.dataset.id;
        return elements.find(function (e) { return e.id == id; });
    }

    function uid() { return '' + Date.now() + '-' + Math.random().toString(36).substr(2, 9); }
    function clamp(v, lo, hi) { return Math.max(lo, Math.min(v, hi)); }
    function esc(s) { return String(s).replace(/"/g, '&quot;'); }

    function notify(msg, type) {
        if (!notifEl) return;
        notifEl.textContent = msg;
        notifEl.className = 'builder-notification ' + (type || 'error');
        clearTimeout(notifyTimer);
        notifyTimer = setTimeout(function () {
            notifEl.className = 'builder-notification hidden';
        }, 3000);
    }

    function getElements() { return elements; }

    /* ── expose ─────────────────────────────────────────────────── */
    window.ArenaBuilder = {
        init:       init,
        setProp:    setProp,
        courtSize:  courtSize,
        deleteEl:   deleteElement,
        getElements: getElements
    };
})();
