
(function () {
    'use strict';

    /* ── Konstantes ─────────────────────────────────────────────── */
    let GRID_SIZE, SEAT_SIZE, SEAT_PAD, CANVAS_W, CANVAS_H;

    /* ── Stāvoklis ──────────────────────────────────────────────── */
    let elements       = [];
    let history        = [];   // undo stack — snapshots of elements
    let selectedEl     = null;
    let dragEl         = null;
    let dragOffset     = { x: 0, y: 0 };
    let canvasScale    = 1;
    let dragStart      = null;
    let notifyTimer    = null;
    let onSaveCb       = null;

    /* ── DOM elementi ───────────────────────────────────────────── */
    let canvas, wrapper, canvasStage, propPanel, notifEl;
    let totalInput, colsInput, rowsInput;

    function isMobileViewport() {
        return window.matchMedia('(max-width: 768px)').matches;
    }

    function getCanvasScale() {
        return canvasScale || 1;
    }

    function updateCanvasViewport() {
        if (!canvas || !wrapper || !canvasStage) return;

        var wrapperStyles = window.getComputedStyle(wrapper);
        var paddingX = (parseFloat(wrapperStyles.paddingLeft) || 0) + (parseFloat(wrapperStyles.paddingRight) || 0);
        var availableWidth = Math.max(1, wrapper.clientWidth - paddingX);
        // Scale to fill the available width exactly (up or down)
        canvasScale = availableWidth / CANVAS_W;
        if (!isFinite(canvasScale) || canvasScale <= 0) canvasScale = 1;

        canvas.style.transformOrigin = 'top left';
        canvas.style.transform = 'scale(' + canvasScale + ')';
        canvasStage.style.width = Math.round(CANVAS_W * canvasScale) + 'px';
        canvasStage.style.height = Math.round(CANVAS_H * canvasScale) + 'px';
        wrapper.scrollLeft = 0;
        wrapper.scrollTop = 0;
    }

    /* ══════════════════════════════════════════════════════════════
       Inicializācija — iestata kanvas, ielādē elementus, piesiena notikumus
       ══════════════════════════════════════════════════════════════ */
    function init(opts) {
        GRID_SIZE = opts.gridSize  || 50;
        SEAT_SIZE = Math.max(10, GRID_SIZE - 6);
        SEAT_PAD  = (GRID_SIZE - SEAT_SIZE) / 2;
        CANVAS_W  = opts.canvasWidth  || 1200;
        CANVAS_H  = opts.canvasHeight || 840;
        elements  = Array.isArray(opts.elements) ? JSON.parse(JSON.stringify(opts.elements)) : [];
        onSaveCb  = opts.onSave || function () {};

        // Ensure courts loaded from DB have grid-unit ratios for drift-free resize
        elements.forEach(function (el) {
            if (el.type === 'court' && !el._gw) {
                el._gw = el.width  / GRID_SIZE;
                el._gh = el.height / GRID_SIZE;
            }
        });

        canvas     = document.getElementById('arena-canvas');
        wrapper    = document.querySelector('.canvas-wrapper');
        canvasStage = document.getElementById('canvas-stage');
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
        updateSeatCountLimit();
        updateCanvasViewport();
    }

    /* ══════════════════════════════════════════════════════════════
       Notikumu piesaiste — poga pievienot, saglabāt, notīrīt, mainīt režģa izmēru
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
            if (!confirm('Notīrīt visus elementus?')) return;
            snapshot();
            elements = [];
            render();
            select(null);
            updateSeatCountLimit();
            notify('Visi elementi notīrīţi', 'success');
        });

        /* grid size */
        var gridSlider = document.getElementById('grid-size-slider');
        if (gridSlider) {
            gridSlider.addEventListener('wheel', function (e) {
                e.preventDefault();
                this.blur();
            }, { passive: false });

            gridSlider.addEventListener('input', function () {
                GRID_SIZE = parseInt(this.value, 10);
                SEAT_SIZE = Math.max(10, GRID_SIZE - 6);
                SEAT_PAD  = (GRID_SIZE - SEAT_SIZE) / 2;
                canvas.style.backgroundSize = GRID_SIZE + 'px ' + GRID_SIZE + 'px';
                document.getElementById('grid-size-label').textContent = GRID_SIZE + 'px';

                // First pass: resize and reposition courts so their new bounds are known
                elements.forEach(function (el) {
                    if (el.type !== 'court') return;
                    el.width  = Math.round(el._gw * GRID_SIZE);
                    el.height = Math.round(el._gh * GRID_SIZE);
                    el.x = clamp(snap(el.x, el.width),  0, CANVAS_W - el.width);
                    el.y = clamp(snapY(el.y, el.height), 0, CANVAS_H - el.height);
                });
                var court = elements.find(function (e) { return e.type === 'court'; });

                // Second pass: resize seats, snap to grid, push off court by minimum penetration depth
                elements.forEach(function (el) {
                    if (el.type !== 'seat') return;
                    el.width  = SEAT_SIZE;
                    el.height = SEAT_SIZE;
                    el.x = clamp(snap(el.x, el.width),  0, CANVAS_W - el.width);
                    el.y = clamp(snapY(el.y, el.height), 0, CANVAS_H - el.height);
                    if (court && rectsOverlap({ x: el.x, y: el.y, width: el.width, height: el.height }, court)) {
                        // Calculate penetration depth on each side and push out the shallowest way
                        var dLeft   = (el.x + el.width)          - court.x;
                        var dRight  = (court.x + court.width)     - el.x;
                        var dTop    = (el.y + el.height)          - court.y;
                        var dBottom = (court.y + court.height)    - el.y;
                        var min = Math.min(dLeft, dRight, dTop, dBottom);
                        var nx = el.x, ny = el.y;
                        if      (min === dLeft)   nx = court.x - el.width;
                        else if (min === dRight)  nx = court.x + court.width;
                        else if (min === dTop)    ny = court.y - el.height;
                        else                      ny = court.y + court.height;
                        el.x = clamp(snap(nx, el.width),  0, CANVAS_W - el.width);
                        el.y = clamp(snapY(ny, el.height), 0, CANVAS_H - el.height);
                    }
                });

                render();
                updateSeatCountLimit();
                if (selectedEl) {
                    var sid = selectedEl.dataset && selectedEl.dataset.id;
                    var dom = sid && document.querySelector('[data-id="' + sid + '"]');
                    if (dom) select(dom);
                }
            });
        }

        if (totalInput) totalInput.addEventListener('input', recalcRows);
        if (colsInput)  colsInput.addEventListener('input', recalcRows);

        if (wrapper) {
            wrapper.addEventListener('wheel', function (e) {
                if (isMobileViewport()) return;
                e.preventDefault();
            }, { passive: false });
        }

        window.addEventListener('resize', updateCanvasViewport);

        /* canvas events */
        canvas.addEventListener('click', onCanvasClick);
        canvas.addEventListener('mousedown', onMouseDown);
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup',   onMouseUp);
        canvas.addEventListener('touchstart', onTouchStart, { passive: true });
        document.addEventListener('touchmove', onTouchMove, { passive: true });
        document.addEventListener('touchend',  onTouchEnd,  { passive: true });
        canvas.addEventListener('contextmenu', onContextMenu);
        document.addEventListener('keydown', onKeyDown);

        var undoBtn = document.getElementById('undo-btn');
        if (undoBtn) undoBtn.addEventListener('click', undo);
    }

    /* ── Kanvas klikšķi un velšana (peles un skāriena atbalsts) ─── */
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

    function onContextMenu(e) {
        e.preventDefault();
        var el = e.target.closest('.arena-element');
        if (!el) return;
        var d = dataFor(el);
        if (d && d.type === 'seat') deleteElement(d.id);
    }

    function onKeyDown(e) {
        var tag = (document.activeElement || {}).tagName || '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

        if ((e.key === 'z' || e.key === 'Z') && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            undo();
            return;
        }

        if (e.key !== 'Delete' && e.key !== 'Backspace') return;
        if (!selectedEl) return;
        var d = dataFor(selectedEl);
        if (d && d.type === 'seat') { e.preventDefault(); deleteElement(d.id); }
    }

    /* ── Sāk velšanu — saglabā sākotnējo pozīciju un nobīdi ─────── */
    function startDrag(el, cx, cy) {
        dragEl = el;
        var d = dataFor(el);
        dragStart = d ? { x: d.x, y: d.y } : null;
        var r = el.getBoundingClientRect();
        var scale = getCanvasScale();
        dragOffset.x = (cx - r.left) / scale;
        dragOffset.y = (cy - r.top) / scale;
        el.style.cursor = 'grabbing';
    }

    /* ── Pārvietojas velšanas laikā — atjaunina DOM pozīciju ─────── */
    function moveDrag(cx, cy) {
        if (!dragEl) return;
        var cr = canvas.getBoundingClientRect();
        var d = dataFor(dragEl);
        if (!d) return;
        var scale = getCanvasScale();
        var px = (cx - cr.left) / scale;
        var py = (cy - cr.top) / scale;
        var x = Math.max(0, Math.min(px - dragOffset.x, CANVAS_W - d.width));
        var y = Math.max(0, Math.min(py - dragOffset.y, CANVAS_H - d.height));
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

    /* ── Beidz velšanu — pielieto uz režģa, pārbauda pārklājumus ── */
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
                notify('Sēdvietas nedrīkst pārklāties ar laukumu', 'error');
                if (dragStart) { x = dragStart.x; y = dragStart.y; }
            }
            /* overlap with another seat? */
            var clash = elements.find(function (e) {
                return e.type === 'seat' && e.id !== d.id &&
                    rectsOverlap({ x: x, y: y, width: d.width, height: d.height }, e);
            });
            if (clash) {
                notify('Sēdvietas nedrīkst pārklāties viena ar otru', 'error');
                if (dragStart) { x = dragStart.x; y = dragStart.y; }
            }
        }

        var moved = (d.x !== clamp(x, 0, CANVAS_W - d.width) || d.y !== clamp(y, 0, CANVAS_H - d.height));
        if (moved || dragStart) snapshot();
        d.x = clamp(x, 0, CANVAS_W - d.width);
        d.y = clamp(y, 0, CANVAS_H - d.height);
        render();
    }

    /* ══════════════════════════════════════════════════════════════
       Elementu pārvaldība — pievienot, dzēst, ģenerēt sēdvietu režģi
       ══════════════════════════════════════════════════════════════ */
    /* ── Vēsture: momentuzņēmums un atsaukšana ─────────────────── */
    function snapshot() {
        history.push(JSON.stringify(elements));
        if (history.length > 60) history.shift();
        updateUndoBtn();
    }

    function undo() {
        if (!history.length) { notify('Nav ko atsaukt', 'error'); return; }
        elements = JSON.parse(history.pop());
        render();
        select(null);
        updateSeatCountLimit();
        updateUndoBtn();
        notify('Darbība atsaukta', 'success');
    }

    function updateUndoBtn() {
        var btn = document.getElementById('undo-btn');
        if (!btn) return;
        btn.disabled = history.length === 0;
        btn.classList.toggle('opacity-40', history.length === 0);
        btn.classList.toggle('cursor-not-allowed', history.length === 0);
    }

    // Pievieno jaunu sēdvietu vai laukumu kanvasam
    function addElement(type, x, y) {
        snapshot();
        if (type === 'court' && elements.some(function (e) { return e.type === 'court'; })) {
            notify('Atļauts tikai viens laukums', 'error');
            return;
        }

        var defs = defaults(type);
        var sx = snap(x, defs.width);
        var sy = snapY(y, defs.height);

        if (type === 'seat') {
            var spot = findFreeSpot(sx, sy, defs.width, defs.height);
            if (!spot) { notify('Nav brīvas vietas sēdvietai', 'error'); return; }
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
        if (type === 'court') {
            el._gw = el.width  / GRID_SIZE;
            el._gh = el.height / GRID_SIZE;
        }

        elements.push(el);
        render();
        select(document.querySelector('[data-id="' + el.id + '"]'));
        notify((type === 'seat' ? 'Sēdvieta' : 'Laukums') + ' pievienots', 'success');
    }

    // Dzēš elementu pēc ID
    function deleteElement(id) {
        snapshot();
        elements = elements.filter(function (e) { return e.id != id; });
        render();
        select(null);
        notify('Elements dzēsts', 'success');
    }

    // Automātiski ģenerē sēdvietu režģi, izvairoties no laukuma
    function generateGrid() {
        snapshot();
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
                    number: lbl, label: lbl
                });
                placed++;
            }
            col++;
            if (col >= cols) { col = 0; row++; }
        }
        render();
        select(null);
        recalcRows();
        notify(placed + ' sēdvietas ģenerētas', 'success');
    }

    /* ── Zīmē visus elementus kanvasā no nulles ─────────────────── */
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
            if (el.type === 'seat') {
                div.style.fontSize   = Math.max(8, Math.round(el.width * 0.27)) + 'px';
                div.style.borderRadius = Math.max(4, Math.round(el.width * 0.22)) + 'px';
            } else if (el.type === 'court') {
                div.style.fontSize   = Math.max(10, Math.round(el.width * 0.055)) + 'px';
            }
            div.textContent  = el.number || el.label || el.type;
            canvas.appendChild(div);
        });
    }

    /* ── Atlases un īpašību panelis ──────────────────────────────── */
    // Iestata izvēlēto elementu un rāda tā īpašības
    function select(domEl) {
        document.querySelectorAll('.arena-element.selected')
            .forEach(function (e) { e.classList.remove('selected'); });
        selectedEl = null;
        if (!domEl) { propPanel.innerHTML = 'Klikšķiniet uz elementa, lai rediģētu tā īpašības'; return; }
        domEl.classList.add('selected');
        selectedEl = domEl;
        showProps(domEl);
    }

    // Ģenerē HTML īpašību paneli izvēlētajam elementam
    function showProps(domEl) {
        var d = dataFor(domEl);
        if (!d) return;
        var h = '<strong>' + d.type.toUpperCase() + '</strong><br>';

        if (d.type === 'seat') {
            h += 'Label: <input type="text" value="' + esc(d.number || '') + '" '
               + 'onchange="ArenaBuilder.setProp(\'' + d.id + '\',\'number\',this.value)" '
               + 'class="w-full p-1 border rounded mb-1"><br>';
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
           + 'class="mt-2 w-full px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">'
           + (d.type === 'seat' ? 'Dzēst sēdvietu' : 'Dzēst elementu') + '</button>';
        if (d.type === 'seat') {
            h += '<p class="text-xs text-gray-400 mt-1">Vai ar labo pogu / Del taustiņu</p>';
        }
        propPanel.innerHTML = h;
    }

    /* ── Publiskās īpašību izmainīšanas funkcijas (izsauktas no inline handleriem) ── */
    // Maina elementa īpašību (etiķeti, orientāciju u.c.)
    function setProp(id, prop, val) {
        var el = elements.find(function (e) { return e.id == id; });
        if (!el) return;
        snapshot();
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

    // Maina laukuma izmēru par delta px un atjaunina režģa vienības koeficientus
    function courtSize(id, prop, delta) {
        var el = elements.find(function (e) { return e.id == id && e.type === 'court'; });
        if (!el) return;
        snapshot();
        el[prop] = Math.max(80, el[prop] + delta);
        // Keep grid-unit ratios in sync so future slider moves stay correct
        el._gw = el.width  / GRID_SIZE;
        el._gh = el.height / GRID_SIZE;
        el.x = clamp(snap(el.x, el.width),  0, CANVAS_W - el.width);
        el.y = clamp(snapY(el.y, el.height), 0, CANVAS_H - el.height);
        render();
        var dom = document.querySelector('[data-id="' + id + '"]');
        if (dom) select(dom);
    }

    /* ══════════════════════════════════════════════════════════════
       Palīgfunkcijas
       ══════════════════════════════════════════════════════════════ */
    // Noklusuma izmēri un etiķetes dažādiem elementu tipiem
    function defaults(type) {
        if (type === 'seat')  return { width: SEAT_SIZE, height: SEAT_SIZE, number: '', label: '' };
        if (type === 'court') return { width: Math.round(GRID_SIZE * 5.2), height: Math.round(GRID_SIZE * 3.0), label: 'Volleyball Court', orientation: 'horizontal' };
        return { width: GRID_SIZE, height: GRID_SIZE };
    }

    function maxSeats() {
        var gridCols = Math.floor(CANVAS_W / GRID_SIZE);
        var gridRows = Math.floor(CANVAS_H / GRID_SIZE);
        var total = gridCols * gridRows;
        var court = elements.find(function (e) { return e.type === 'court'; });
        if (court) {
            total -= Math.ceil(court.width / GRID_SIZE) * Math.ceil(court.height / GRID_SIZE);
        }
        return Math.max(1, total);
    }

    function updateSeatCountLimit() {
        var max = maxSeats();
        if (totalInput) {
            totalInput.max = max;
            var current = parseInt(totalInput.value, 10) || 1;
            if (current > max) {
                totalInput.value = max;
                recalcRows();
            }
        }
        var maxLabel = document.getElementById('seat-count-max');
        if (maxLabel) maxLabel.textContent = '(maks. ' + max + ')';
    }

    // Pārrēķina rindu skaitu balstoties uz kopējo sēdvietu un kolonnu skaitu
    function recalcRows() {
        if (!totalInput || !colsInput || !rowsInput) return;
        var t = Math.max(1, parseInt(totalInput.value, 10) || 1);
        var c = Math.max(1, parseInt(colsInput.value, 10)  || 1);
        rowsInput.value = Math.ceil(t / c);
    }

    // Piesaista koordinātu pie tuvākā režģa punkta
    function snap(v, size)  { var o = (GRID_SIZE - size) / 2; return Math.round((v - o) / GRID_SIZE) * GRID_SIZE + o; }
    function snapY(v, size) { var o = (GRID_SIZE - size) / 2; return Math.round((v - o) / GRID_SIZE) * GRID_SIZE + o; }

    // Pārbauda vai divi taisnstūri pārklājas
    function rectsOverlap(a, b) {
        return a.x < b.x + b.width  && a.x + a.width  > b.x &&
               a.y < b.y + b.height && a.y + a.height > b.y;
    }

    // Meklē brīvu vietu elementam, sākot no vēlamās pozīcijas
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

    // Atrod elementa datus pēc DOM elementa
    function dataFor(dom) {
        var id = dom.dataset.id;
        return elements.find(function (e) { return e.id == id; });
    }

    // Unikāls ID, ierobežo vērtību diapazonā, ekranizē HTML atribūtus
    function uid() { return '' + Date.now() + '-' + Math.random().toString(36).substr(2, 9); }
    function clamp(v, lo, hi) { return Math.max(lo, Math.min(v, hi)); }
    function esc(s) { return String(s).replace(/"/g, '&quot;'); }

    // Rāda paziņojumu kanvasa augšdaļā uz 3 sekundēm
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

    /* ── Publiskais API — pieejams kā window.ArenaBuilder ────────── */
    window.ArenaBuilder = {
        init:       init,
        setProp:    setProp,
        courtSize:  courtSize,
        deleteEl:   deleteElement,
        undo:       undo,
        getElements: getElements
    };
})();
