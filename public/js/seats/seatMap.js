(function () {
  'use strict';

  // Pārvērš tekstu vienotā formātā salīdzināšanai (piemēram, "Kreisā tribīne" -> "kreisā-tribīne")
  function slugify(s) {
    try {
      const base = (s || '').toString();
      const n = base.normalize ? base.normalize('NFKD').replace(/[\u0300-\u036f]/g, '') : base;
      return n.replace(/[^A-Za-z0-9]+/g, '-').toLowerCase().replace(/^-+|-+$/g, '');
    } catch (e) {
      return String(s || '').replace(/[^A-Za-z0-9]+/g, '-').toLowerCase();
    }
  }

  // Uzzīmē sēdvietu karti konteinerā ar pielāgotu vai noklusējuma izkārtojumu
    function renderSeatMap(container, options = {}) {
    if (!container) return;

    const rows = Number(options.rows) || 6;
    const cols = Number(options.cols) || 12;
    const sideColumns = Number(options.sideColumns ?? 6);
    const sideRows = Number(options.sideRows ?? 12);
    const takenSeats = Array.isArray(options.takenSeats) ? options.takenSeats : [];
    const takenSeatIds = Array.isArray(options.takenSeatIds) ? options.takenSeatIds.map(String) : [];
    const reservedSeats = Array.isArray(options.reservedSeats) ? options.reservedSeats : [];
    const reservedSeatIds = Array.isArray(options.reservedSeatIds) ? options.reservedSeatIds.map(String) : [];
    const seatPrices = options.seatPrices || {};
    const seatIdMap = options.seatIds || {};
    const onSeatSelect = typeof options.onSeatSelect === 'function' ? options.onSeatSelect : null;
    const defaultPrice = Number(options.defaultPrice ?? options.ticketPrice ?? 10);
    const gap = Number(options.gap ?? 6);

    const customElements = Array.isArray(options.customElements) ? options.customElements : null;
    const useCustomLayout = customElements !== null;
    const arenaWidth  = Number(options.arenaWidth)  || 0;
    const arenaHeight = Number(options.arenaHeight) || 0;

    function deriveMaxRowFromSeatIdMap(map) {
      let maxRow = 0;
      if (!map || typeof map !== 'object') return maxRow;
      const trailingRe = /-(\d+)-(\d+)$/;
      for (const k of Object.keys(map)) {
        if (typeof k !== 'string') continue;
        const m = k.match(trailingRe);
        if (m) {
          const r = parseInt(m[1], 10);
          if (!Number.isNaN(r) && r > maxRow) maxRow = r;
        }
      }
      return maxRow;
    }

    function deriveMaxRowForLabel(map, label, defaultRows) {
      try {
        if (!label || !map || typeof map !== 'object') return defaultRows;
        const slug = slugify(label);
        const trailingRe = new RegExp('^' + slug + '-(\\d+)-(\\d+)$');
        let maxRow = 0;
        for (const k of Object.keys(map)) {
          if (typeof k !== 'string') continue;
          if (k.indexOf(slug + '-') !== 0) continue;
          const m = k.match(trailingRe);
          if (!m) continue;
          const r = parseInt(m[1], 10);
          if (!Number.isNaN(r) && r > maxRow) maxRow = r;
        }
        return maxRow > 0 ? maxRow : defaultRows;
      } catch (e) {
        return defaultRows;
      }
    }

    const derivedRows = deriveMaxRowFromSeatIdMap(seatIdMap) || rows;

    if (!container._seatMap) container._seatMap = {};
    const state = container._seatMap;
    if (!state.selected) state.selected = new Set();

    function canonicalKeyFor(humanSide, r, c) {
      const sideSlug = slugify(humanSide || 'side');
      return `${sideSlug}-${r}-${c}`;
    }

    function resolveDbId(humanSide, r, c, seatNumberRaw) {
      const canonical = canonicalKeyFor(humanSide, r, c);
      if (seatIdMap && Object.prototype.hasOwnProperty.call(seatIdMap, canonical)) return seatIdMap[canonical];
      if (seatNumberRaw && Object.prototype.hasOwnProperty.call(seatIdMap, seatNumberRaw)) return seatIdMap[seatNumberRaw];
      const suffix = `-${r}-${c}`;
      for (const k in seatIdMap) {
        if (!Object.prototype.hasOwnProperty.call(seatIdMap, k)) continue;
        if (typeof k !== 'string') continue;
        if (k.endsWith(suffix)) return seatIdMap[k];
      }
      return null;
    }

    container.innerHTML = '';
    container.classList.add('seat-map-root');
    container.style.boxSizing = 'border-box';
    container.style.width = container.style.width || (options.width || '100%');

    container.style.minHeight = options.minHeight || '70vh';
 
    container.style.overflow = 'auto';
    container.style.position = 'relative';

    const detailView = document.createElement('div');
    detailView.className = 'seat-detail-view';
    detailView.style.display = 'none';
    detailView.style.width = '100%';
    detailView.style.height = '100%';
    detailView.style.overflowY = 'auto';
    detailView.style.padding = '6px';
    container.appendChild(detailView);

    const overview = document.createElement('div');

   function createSeat(humanSideLabel, r, c, seatSize = 34, displayNumber = null) {
      const seat = document.createElement('div');
      seat.className = 'seat-square seat-item flex items-center justify-center font-semibold';
      seat.style.width = seat.style.height = seatSize + 'px';
      seat.style.lineHeight = seatSize + 'px';
      seat.style.fontSize = Math.max(10, Math.floor(seatSize / 2)) + 'px';
      seat.style.boxSizing = 'border-box';
      seat.style.flex = '0 0 auto';
      seat.style.userSelect = 'none';
      seat.style.transition = 'all 0.15s ease';
      seat.textContent = displayNumber !== null ? String(displayNumber) : String(c);

      const key = canonicalKeyFor(humanSideLabel, r, c);
      seat.dataset.id = key;
      seat.dataset.row = String(r);
      seat.dataset.number = String(c);
      seat.dataset.side = humanSideLabel || '';

      const dbId = resolveDbId(humanSideLabel, r, c);
      seat.dataset.dbId = dbId ? String(dbId) : '';

      const price = seatPrices[key] ?? seatPrices[`${r}-${c}`] ?? seatPrices[`${humanSideLabel}-${r}-${c}`] ?? defaultPrice;
      seat.dataset.price = String(price);
      seat.title = `Cena: €${price}`;

      const humanKey = `${humanSideLabel}-${r}-${c}`;
      const isTaken = takenSeats.includes(humanKey) || takenSeats.includes(key) || (dbId && takenSeatIds.includes(String(dbId)));
      const isReserved = !isTaken && (reservedSeats.includes(humanKey) || reservedSeats.includes(key) || (dbId && reservedSeatIds.includes(String(dbId))));

      if (isTaken) {
        seat.classList.add('bg-red-600', 'text-white', 'cursor-not-allowed', 'line-through');
        seat.dataset.taken = '1';
      } else if (isReserved) {
        seat.classList.add('bg-yellow-400', 'cursor-not-allowed');
        seat.dataset.reserved = '1';
      } else {
        seat.classList.add('bg-gray-300', 'hover:shadow-md', 'hover:scale-105');
        seat.dataset.taken = '0';
      }

      if (state.selected.has(key) && !isTaken && !isReserved) {
        seat.classList.add('selected', 'bg-green-600', 'text-white', 'font-bold', 'shadow-md');
      }

      seat.addEventListener('mouseenter', () => {
        if (seat.dataset.taken === '0' && !seat.classList.contains('selected')) {
          seat.style.boxShadow = '0 0 6px rgba(0,0,0,0.3)';
        }
      });

      seat.addEventListener('mouseleave', () => {
        seat.style.boxShadow = '';
      });

      seat.addEventListener('click', () => {
        if (seat.dataset.taken === '1' || seat.dataset.reserved === '1') return;
        const selectedNow = seat.classList.contains('selected');
        if (!selectedNow) {
          seat.classList.add('selected', 'bg-green-600', 'text-white', 'font-bold', 'shadow-md');
          state.selected.add(key);
        } else {
          seat.classList.remove('selected', 'bg-green-600', 'text-white', 'font-bold', 'shadow-md');
          state.selected.delete(key);
        }

        const selectedSeats = Array.from(container.querySelectorAll('.seat-item.selected')).map(el => ({
          id: el.dataset.id,
          row: Number(el.dataset.row),
          number: Number(el.dataset.number),
          price: Number(el.dataset.price),
          sideLabel: el.dataset.side,
          dbId: el.dataset.dbId || null
        }));

        if (onSeatSelect) onSeatSelect(selectedSeats);
        document.dispatchEvent(new CustomEvent('seatSelected', { detail: selectedSeats }));
      });

      return seat;
    }

    function buildStandGrid(label, seatDir, seatSize, mobileDetail = false, rowsOverride = null, colsOverride = null) {
      const rowsToUse = Number(rowsOverride || derivedRows);
      const colsToUse = Number(colsOverride || cols);

      if (mobileDetail && seatDir === 'row') {
        const wrapper = document.createElement('div');
        wrapper.className = 'stand-grid-wrapper';
        wrapper.style.display = 'flex';
        wrapper.style.flexDirection = 'row';
        wrapper.style.gap = gap + 'px';
        wrapper.style.overflowX = 'auto';
        wrapper.style.overflowY = 'visible';
        wrapper.style.webkitOverflowScrolling = 'touch';
        wrapper.style.width = '100%';
        wrapper.style.boxSizing = 'border-box';
        wrapper.style.alignItems = 'flex-start';
        for (let c = 1; c <= colsToUse; c++) {
          const col = document.createElement('div');
          col.style.display = 'flex';
          col.style.flexDirection = 'column';
          col.style.gap = gap + 'px';
          col.style.flex = '0 0 auto';
          col.style.boxSizing = 'border-box';
          for (let r = 1; r <= rowsToUse; r++) {
            col.appendChild(createSeat(label, r, c, seatSize));
          }
          wrapper.appendChild(col);
        }
        return wrapper;
      }

      if (seatDir === 'col') {
        const wrapper = document.createElement('div');
        wrapper.className = 'stand-grid-vertical';
        wrapper.style.display = 'flex';
        wrapper.style.flexDirection = 'row';
        wrapper.style.gap = gap + 'px';
        wrapper.style.boxSizing = 'border-box';
        wrapper.style.alignItems = 'flex-start';
        wrapper.style.justifyContent = 'center';
        wrapper.style.flexWrap = 'nowrap';

        const colCount = Math.max(1, Math.min(sideColumns, colsToUse));
        for (let c = 1; c <= colCount; c++) {
          const col = document.createElement('div');
          col.className = 'seat-column-vertical';
          col.style.display = 'flex';
          col.style.flexDirection = 'column';
          col.style.gap = gap + 'px';
          col.style.boxSizing = 'border-box';
          col.style.flex = '0 0 auto';
          for (let seatNum = 1; seatNum <= rowsToUse; seatNum++) {
            col.appendChild(createSeat(label, seatNum, c, seatSize, seatNum));
          }
          wrapper.appendChild(col);
        }
        return wrapper;
      }

      const grid = document.createElement('div');
      grid.className = 'stand-grid';
      grid.style.display = 'flex';
      grid.style.flexDirection = 'column';
      grid.style.gap = gap + 'px';
      grid.style.boxSizing = 'border-box';
      grid.style.alignItems = 'center';
      grid.style.justifyContent = 'center';
      grid.style.width = '100%';

      for (let r = 1; r <= rowsToUse; r++) {
        const line = document.createElement('div');
        line.className = 'stand-line';
        line.style.display = 'flex';
        line.style.flexDirection = 'row';
        line.style.gap = gap + 'px';
        line.style.flexWrap = 'nowrap';
        line.style.boxSizing = 'border-box';
        line.style.justifyContent = 'center';
        line.style.alignItems = 'center';
        line.style.overflow = 'visible';
        for (let c = 1; c <= colsToUse; c++) {
          line.appendChild(createSeat(label, r, c, seatSize));
        }
        grid.appendChild(line);
      }
      return grid;
    }

    function applyCustomLayout() {
      container.innerHTML = '';
      container.appendChild(detailView);

      // Pielāgotas arēnas izkārtojums ar definējamiem elementiem (sēdvietas, laukums, u.c.) un to stāvokli (aizņemts, rezervēts, brīvs)
      const canvasContainer = document.createElement('div');
      canvasContainer.style.position = 'relative';
      canvasContainer.style.overflow = 'auto';

      if (arenaWidth && arenaHeight) {
        canvasContainer.style.width  = arenaWidth  + 'px';
        canvasContainer.style.height = arenaHeight + 'px';
        canvasContainer.style.minWidth  = arenaWidth  + 'px';
        canvasContainer.style.minHeight = arenaHeight + 'px';
      } else {
        // Automātiski pielāgo izmēru, lai ietilptu visi definētie elementi, ar nelielu rezervi
        let maxX = 600, maxY = 400;
        customElements.forEach(el => {
          const right  = (Number(el.x) || 0) + (Number(el.width)  || 44);
          const bottom = (Number(el.y) || 0) + (Number(el.height) || 44);
          if (right  > maxX) maxX = right  + 20;
          if (bottom > maxY) maxY = bottom + 20;
        });
        canvasContainer.style.width     = maxX + 'px';
        canvasContainer.style.height    = maxY + 'px';
        canvasContainer.style.minWidth  = maxX + 'px';
        canvasContainer.style.minHeight = maxY + 'px';
      }

      canvasContainer.style.background = '#f9fafb';
      canvasContainer.style.border = '1.5px solid #e5e7eb';
      canvasContainer.style.borderRadius = '12px';
      canvasContainer.style.backgroundImage =
        'linear-gradient(rgba(148,163,184,.12) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.12) 1px,transparent 1px)';
      canvasContainer.style.backgroundSize = '50px 50px';
      container.appendChild(canvasContainer);

      // Uzzīmē pielāgotus elementus (sēdvietas, laukums, u.c.) atbilstoši definīcijai un to stāvoklim (aizņemts, rezervēts, brīvs)
      customElements.forEach(element => {
        if (element.type === 'seat') {
          const seatElement = createCustomSeat(element);
          canvasContainer.appendChild(seatElement);
        } else if (element.type === 'court') {
          const courtElement = createCustomCourt(element);
          canvasContainer.appendChild(courtElement);
        }
      });
    }

    function createCustomSeat(seatData) {
      const seat = document.createElement('div');
      seat.className = 'custom-seat seat-item flex items-center justify-center font-semibold';
      seat.style.position = 'absolute';
      seat.style.left = seatData.x + 'px';
      seat.style.top = seatData.y + 'px';
      seat.style.width = (seatData.width || 32) + 'px';
      seat.style.height = (seatData.height || 32) + 'px';
      seat.style.lineHeight = (seatData.height || 32) + 'px';
      seat.style.fontSize = Math.max(10, Math.floor((seatData.height || 32) / 2)) + 'px';
      seat.style.boxSizing = 'border-box';
      seat.style.userSelect = 'none';
      seat.style.transition = 'all 0.15s ease';
      seat.textContent = seatData.number || seatData.id || 'S';

      const key = `custom-${seatData.id}`;
      seat.dataset.id = key;
      seat.dataset.customId = seatData.id;
      seat.dataset.dbId = seatData.dbId || seatIdMap[seatData.id] || '';

      const price = seatData.price || defaultPrice;
      seat.dataset.price = String(price);
      seat.title = `Seat ${seatData.number || seatData.id} - €${price}`;

      const isTaken = takenSeats.includes(key) || takenSeats.includes(seatData.id) ||
                     (seatData.dbId && takenSeatIds.includes(String(seatData.dbId)));
      const isReserved = !isTaken && (reservedSeats.includes(key) || reservedSeats.includes(seatData.id) ||
                      (seatData.dbId && reservedSeatIds.includes(String(seatData.dbId))));

      if (isTaken) {
        seat.classList.add('bg-red-600', 'text-white', 'cursor-not-allowed', 'line-through');
        seat.dataset.taken = '1';
      } else if (isReserved) {
        seat.classList.add('bg-yellow-400', 'cursor-not-allowed');
        seat.dataset.reserved = '1';
      } else {
        seat.classList.add('bg-green-600', 'text-white', 'hover:shadow-md', 'hover:scale-105');
        seat.dataset.taken = '0';
      }

      if (state.selected.has(key) && !isTaken && !isReserved) {
        seat.classList.add('selected', 'bg-blue-600', 'text-white', 'font-bold', 'shadow-md');
      }

      seat.addEventListener('mouseenter', () => {
        if (seat.dataset.taken === '0' && !seat.classList.contains('selected')) {
          seat.style.boxShadow = '0 0 6px rgba(0,0,0,0.3)';
        }
      });

      seat.addEventListener('mouseleave', () => {
        seat.style.boxShadow = '';
      });

      seat.addEventListener('click', () => {
        if (seat.dataset.taken === '1' || seat.dataset.reserved === '1') return;
        const selectedNow = seat.classList.contains('selected');
        if (!selectedNow) {
          seat.classList.add('selected', 'bg-blue-600', 'text-white', 'font-bold', 'shadow-md');
          state.selected.add(key);
        } else {
          seat.classList.remove('selected', 'bg-blue-600', 'text-white', 'font-bold', 'shadow-md');
          state.selected.delete(key);
        }

        const selectedSeats = Array.from(container.querySelectorAll('.seat-item.selected')).map(el => ({
          id: el.dataset.id,
          customId: el.dataset.customId,
          price: Number(el.dataset.price),
          dbId: el.dataset.dbId || null,
          seatData: customElements.find(e => e.id == el.dataset.customId)
        }));

        if (onSeatSelect) onSeatSelect(selectedSeats);
        document.dispatchEvent(new CustomEvent('seatSelected', { detail: selectedSeats }));
      });

      return seat;
    }

    function createCustomCourt(courtData) {
      const court = document.createElement('div');
      court.className = 'custom-court flex items-center justify-center font-bold';
      court.style.position = 'absolute';
      court.style.left = courtData.x + 'px';
      court.style.top = courtData.y + 'px';
      court.style.width = (courtData.width || 200) + 'px';
      court.style.height = (courtData.height || 100) + 'px';
      court.style.backgroundColor = '#fbbf24';
      court.style.border = '2px solid #f59e0b';
      court.style.borderRadius = '4px';
      court.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
      court.style.color = '#92400e';
      court.textContent = courtData.label || 'Volleyball Court';

      return court;
    }

    function applyLayout() {
      const isMobile = window.innerWidth <= 768;
      container.innerHTML = '';
      container.appendChild(detailView);

      // Handle custom arena layouts
      if (useCustomLayout) {
        return applyCustomLayout();
      }

      if (isMobile) {
        overview.innerHTML = '';
        overview.style.display = 'grid';
        overview.style.gridTemplateColumns = '1fr auto 1fr';
        overview.style.gridTemplateRows = 'auto auto auto';
        overview.style.gap = '12px';
        overview.style.width = '100%';
        overview.style.boxSizing = 'border-box';
        overview.style.alignItems = 'center';
        overview.style.justifyItems = 'center';

        let lastDetailOpen = 0;

        function createPreview(label, seatDir) {
          const wrapper = document.createElement('div');
          wrapper.className = 'stand-preview-wrapper';
          wrapper.style.display = 'flex';
          wrapper.style.flexDirection = 'column';
          wrapper.style.alignItems = 'center';
          wrapper.style.justifyContent = 'center';
          wrapper.style.gap = '6px';
          wrapper.style.width = '100%';
          const title = document.createElement('div');
          title.className = 'font-semibold text-gray-700 cursor-pointer';
          title.textContent = label;
          wrapper.appendChild(title);

          const preview = document.createElement('div');
          preview.className = 'stand-preview p-2 text-center border rounded bg-gray-100 cursor-pointer';
          preview.textContent = label.split(' ')[0];
          preview.title = label;
          preview.style.display = 'flex';
          preview.style.alignItems = 'center';
          preview.style.justifyContent = 'center';
          wrapper.appendChild(preview);

          preview.addEventListener('pointerdown', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const now = Date.now();
            if (now - lastDetailOpen < 300) return;
            lastDetailOpen = now;
            showDetailView(label, seatDir);
          }, { passive: false });

          title.addEventListener('pointerdown', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const now = Date.now();
            if (now - lastDetailOpen < 300) return;
            lastDetailOpen = now;
            showDetailView(label, seatDir);
          }, { passive: false });

          return wrapper;
        }

        const topPreview = createPreview('Augšējā tribīne', 'row');
        const leftPreview = createPreview('Kreisā tribīne', 'col');
        const rightPreview = createPreview('Labā tribīne', 'col');
        const bottomPreview = createPreview('Apakšējā tribīne', 'row');

        const courtBlock = document.createElement('div');
        courtBlock.className = 'bg-yellow-200 rounded shadow-inner flex items-center justify-center';
        courtBlock.style.minWidth = '120px';
        courtBlock.style.minHeight = '80px';
        courtBlock.style.fontWeight = '700';
        courtBlock.textContent = 'VOLEJBOLA LAUKUMS';
        courtBlock.style.justifySelf = 'center';
        courtBlock.style.alignSelf = 'center';

        const place = (el, r, c) => { el.style.gridRow = String(r); el.style.gridColumn = String(c); overview.appendChild(el); };
        place(document.createElement('div'), 1, 1);
        place(topPreview, 1, 2);
        place(document.createElement('div'), 1, 3);

        place(leftPreview, 2, 1);
        place(courtBlock, 2, 2);
        place(rightPreview, 2, 3);

        place(document.createElement('div'), 3, 1);
        place(bottomPreview, 3, 2);
        place(document.createElement('div'), 3, 3);

        container.appendChild(overview);
        return;
      }

      const contRect = container.getBoundingClientRect();
      let contW = contRect.width || container.clientWidth || window.innerWidth;
      let contH = contRect.height || container.clientHeight || window.innerHeight * 0.8;
      if (contH < 200) {
        contH = Math.max(contH, window.innerHeight * 0.6);
        container.style.minHeight = (contH | 0) + 'px';
      }

      const courtH = Number(options.courtHeight ?? 120);
      const courtW = Number(options.courtWidth ?? 180);
      const titleH = 24;
      const verticalPadding = 20;
      const widthPadding = 8;
      const minSeat = Number(options.minSeat ?? 16);
      const maxSeat = Number(options.maxSeat ?? 42);

    
      let seatSize = Math.min(maxSeat, Math.floor(Math.min(contW / (cols + sideColumns * 2), contH / (sideRows + derivedRows + 3)))); 
      if (seatSize < minSeat) seatSize = minSeat;
     
      const preferredSeatSize = Number(options.preferredSeatSize || 0);
      if (preferredSeatSize > 0) {
        seatSize = Math.max(minSeat, Math.min(maxSeat, Math.floor(preferredSeatSize)));
      }

      function computeMetrics(sz) {
        const topStandWidth = Math.max(40, (sz * cols) + ((cols - 1) * gap) + widthPadding);
        const topStandHeight = titleH + (sz * derivedRows) + ((derivedRows - 1) * gap);

        const sideColCount = Math.max(1, Math.min(sideColumns, cols));
        const sideVerticalWidth = (sz * sideColCount) + ((sideColCount - 1) * gap) + widthPadding;
        const sideVerticalHeight = titleH + (sz * sideRows) + ((sideRows - 1) * gap);

        const centerWidth = Math.max(courtW, topStandWidth);
        const requiredWidth = sideVerticalWidth + centerWidth + sideVerticalWidth + (6 * 2);
        const middleHeight = Math.max(courtH, sideVerticalHeight);
        const requiredHeight = topStandHeight + middleHeight + topStandHeight + verticalPadding;

        return {
          requiredWidth, requiredHeight,
          topStandWidth, topStandHeight,
          sideVerticalWidth, sideVerticalHeight,
          centerWidth, middleHeight
        };
      }

      let metrics = computeMetrics(seatSize);
      let loop = 0;
while ((metrics.requiredWidth > contW || metrics.requiredHeight > window.innerHeight * 0.9) && seatSize > minSeat && loop < 200) {
  seatSize--;
  metrics = computeMetrics(seatSize);
  loop++;
}


      const allowScrollFallback = (metrics.requiredWidth > contW || metrics.requiredHeight > contH);

      const gridRoot = document.createElement('div');
      gridRoot.style.display = 'grid';
      gridRoot.style.gridTemplateColumns = `${metrics.sideVerticalWidth}px ${metrics.centerWidth}px ${metrics.sideVerticalWidth}px`;
      gridRoot.style.gridTemplateRows = `${metrics.topStandHeight}px ${metrics.middleHeight}px ${metrics.topStandHeight}px`;
      gridRoot.style.columnGap = '6px';
      gridRoot.style.rowGap = '6px';
      gridRoot.style.width = metrics.requiredWidth + 'px';
      gridRoot.style.boxSizing = 'border-box';
      gridRoot.style.justifyItems = 'center';
      gridRoot.style.alignItems = 'center';

      function makeStand(label, seatDir) {
        const standWrapper = document.createElement('div');
        standWrapper.className = 'stand-wrapper';
        standWrapper.style.display = 'flex';
        standWrapper.style.flexDirection = 'column';
        standWrapper.style.alignItems = 'center';
        standWrapper.style.boxSizing = 'border-box';
        standWrapper.style.overflow = 'visible';
        standWrapper.style.justifyContent = 'center';

        const title = document.createElement('div');
        title.className = 'font-semibold mb-1 text-gray-700';
        title.textContent = label;
        title.style.height = titleH + 'px';
        title.style.lineHeight = titleH + 'px';
        standWrapper.appendChild(title);

        const rowsForLabel = seatDir === 'col'
          ? deriveMaxRowForLabel(seatIdMap, label, sideRows)
          : deriveMaxRowForLabel(seatIdMap, label, derivedRows);
        const colsForLabel = seatDir === 'col' ? Math.max(1, Math.min(sideColumns, cols)) : cols;

        const gridNode = buildStandGrid(label, seatDir, seatSize, false, rowsForLabel, colsForLabel);

        if (seatDir === 'col') {
      
          gridNode.style.maxHeight = (metrics.sideVerticalHeight - titleH) + 'px';
          gridNode.style.overflowY = 'auto';
        }

        if (seatDir === 'col') {
          standWrapper.style.width = metrics.sideVerticalWidth + 'px';
          standWrapper.style.minWidth = metrics.sideVerticalWidth + 'px';
          standWrapper.style.maxWidth = metrics.sideVerticalWidth + 'px';
          standWrapper.style.minHeight = metrics.sideVerticalHeight + 'px';
        } else {
          standWrapper.style.width = metrics.centerWidth + 'px';
          standWrapper.style.minWidth = metrics.centerWidth + 'px';
          standWrapper.style.maxWidth = metrics.centerWidth + 'px';
          standWrapper.style.minHeight = metrics.topStandHeight + 'px';
        }

        standWrapper.appendChild(gridNode);
        return standWrapper;
      }

      const top = makeStand('Augšējā tribīne', 'row');
      const left = makeStand('Kreisā tribīne', 'col');
      const right = makeStand('Labā tribīne', 'col');
      const bottom = makeStand('Apakšējā tribīne', 'row');

      const court = document.createElement('div');
      court.className = 'bg-yellow-300 flex items-center justify-center font-bold rounded shadow-inner';
      court.textContent = 'VOLEJBOLA LAUKUMS';
      court.style.width = metrics.centerWidth + 'px';
      court.style.height = Math.max(80, courtH) + 'px';
      court.style.boxSizing = 'border-box';

      const place = (node, r, c) => { node.style.gridRow = String(r); node.style.gridColumn = String(c); gridRoot.appendChild(node); };
      place(document.createElement('div'), 1, 1);
      place(top, 1, 2);
      place(document.createElement('div'), 1, 3);

      place(left, 2, 1);
      place(court, 2, 2);
      place(right, 2, 3);

      place(document.createElement('div'), 3, 1);
      place(bottom, 3, 2);
      place(document.createElement('div'), 3, 3);

      const scaleWrapper = document.createElement('div');
      scaleWrapper.className = 'seat-map-scale-wrapper';
      scaleWrapper.style.width = '100%';
      scaleWrapper.style.display = 'flex';
      scaleWrapper.style.justifyContent = allowScrollFallback ? 'center' : 'flex-start';
      scaleWrapper.style.alignItems = 'flex-start';
     
scaleWrapper.style.overflow = 'visible';
scaleWrapper.style.boxSizing = 'border-box';
scaleWrapper.style.height = 'auto';



scaleWrapper.appendChild(gridRoot);


      container.appendChild(scaleWrapper);

      container._seatMap.gridRoot = gridRoot;
      container._seatMap.scaleWrapper = scaleWrapper;
    }

    function showDetailView(label, seatDir) {
    
      detailView.innerHTML = '';
      detailView.style.display = 'block';
      detailView.scrollTop = 0;

      const header = document.createElement('div');
      header.className = 'flex justify-between items-center mb-2';
      const title = document.createElement('div');
      title.innerHTML = `<strong>${label}</strong>`;
      const backBtn = document.createElement('button');
      backBtn.className = 'px-3 py-1 border rounded';
      backBtn.textContent = 'Atpakaļ';
      header.appendChild(title);
      header.appendChild(backBtn);
      detailView.appendChild(header);

   
      requestAnimationFrame(() => {
        const isMobile = window.innerWidth <= 768;

      
        const rowsForLabel = deriveMaxRowForLabel(seatIdMap, label, derivedRows);
        const colsForLabel = seatDir === 'col' ? Math.max(1, Math.min(sideColumns, cols)) : cols;

        const gridNode = buildStandGrid(label, seatDir, 40, isMobile, rowsForLabel, colsForLabel);


        if (seatDir === 'col') {
          gridNode.style.maxHeight = 'calc(60vh - 80px)';
          gridNode.style.overflowY = 'auto';
        }

        detailView.appendChild(gridNode);

        if (seatDir === 'col') {
          requestAnimationFrame(() => {
            try { gridNode.scrollTop = gridNode.scrollHeight; } catch (e) {}
          });
        } else {
          requestAnimationFrame(() => {
            try { gridNode.scrollLeft = 0; } catch (e) {}
          });
        }
      });

      overview.style.display = 'none';
      detailView.style.display = 'block';

      backBtn.addEventListener('click', () => {
        detailView.style.display = 'none';
        overview.style.display = 'grid';
      });
    }

    applyLayout();

    const onResize = () => { try { applyLayout(); } catch (e) { console.error('seatMap resize error', e); } };
    window.addEventListener('resize', onResize);

    container._seatMap.toggleByKey = function (key) {
      if (!key) return false;
      const el = container.querySelector(`[data-key="${CSS && CSS.escape ? CSS.escape(key) : key}"]`);
      if (!el) return false;
      if (el.dataset.taken === '1' || el.dataset.reserved === '1') return false;
      el.click();
      return true;
    };

    container._seatMap.getSelected = function () {
      return Array.from(state.selected);
    };

    container._seatMap.clearSelected = function () {
      state.selected.clear();
      Array.from(container.querySelectorAll('.seat-item.selected')).forEach(el => {
        el.classList.remove('selected', 'bg-green-600', 'text-white', 'font-bold');
      });
      document.dispatchEvent(new CustomEvent('seatSelected', { detail: [] }));
    };

    renderSeatMap._cleanup = () => { window.removeEventListener('resize', onResize); };

    return container._seatMap;
  }

  window.renderSeatMap = renderSeatMap;
})();
