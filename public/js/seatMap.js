// public/js/seatMap.js
// Robust seat map: side stands vertical (sideColumns x sideRows), compute seatSize to fit popup.
// No reliance on transform scale; layout uses fixed left/center/right column widths to prevent overlap.

function renderSeatMap(container, options = {}) {
    if (!container) return;

    const rows = Number(options.rows) || 6;                // top/bottom rows
    const cols = Number(options.cols) || 12;               // top/bottom cols
    const sideColumns = Number(options.sideColumns ?? 6);  // left/right columns count
    const sideRows = Number(options.sideRows ?? 12);       // seats per side column (1..12)
    const takenSeats = Array.isArray(options.takenSeats) ? options.takenSeats : [];
    const seatPrices = options.seatPrices || {};
    const onSeatSelect = typeof options.onSeatSelect === 'function' ? options.onSeatSelect : null;
    const defaultPrice = Number(options.defaultPrice ?? options.ticketPrice ?? 10);
    const gap = Number(options.gap ?? 6);

    // DOM container setup
    container.innerHTML = '';
    container.classList.add('seat-map-root');
    container.style.boxSizing = 'border-box';
    container.style.overflowX = 'hidden';
    container.style.overflowY = 'auto';
    if (!container.style.minHeight) container.style.minHeight = options.minHeight || '60vh';

    // seat creation helper (displayNumber optional for vertical side stands)
    function createSeat(label, r, c, seatSize = 30, displayNumber = null) {
        const seat = document.createElement('div');
        seat.className = 'seat-square border border-gray-400 flex items-center justify-center text-sm seat-item';
        // inline size controlled here; CSS removed forced min sizes so these take effect
        seat.style.width = seat.style.height = seatSize + 'px';
        seat.style.lineHeight = seatSize + 'px';
        seat.style.fontSize = Math.max(8, Math.floor(seatSize / 2)) + 'px';
        seat.style.boxSizing = 'border-box';
        seat.style.flex = '0 0 auto';
        seat.style.userSelect = 'none';
        seat.textContent = displayNumber !== null ? String(displayNumber) : String(c);

        const seatId = `${label}-${r}-${c}`;
        seat.dataset.id = seatId;
        seat.dataset.row = String(r);
        seat.dataset.number = String(c);
        const price = seatPrices[seatId] ?? seatPrices[`${r}-${c}`] ?? defaultPrice;
        seat.dataset.price = String(price);
        seat.dataset.side = label;
        seat.title = `Cena: €${price}`;

        const isTaken = takenSeats.includes(seatId);
        if (isTaken) seat.classList.add('bg-gray-500', 'text-white', 'cursor-not-allowed');

        seat.addEventListener('click', () => {
            if (isTaken) return;
            const prev = container.querySelector('.selected');
            if (prev) prev.classList.remove('selected', 'bg-green-600', 'text-white', 'font-bold');
            if (selected && selected.id === seatId) {
                selected = null;
                if (onSeatSelect) onSeatSelect(null);
                document.dispatchEvent(new CustomEvent('seatSelected', { detail: null }));
            } else {
                seat.classList.add('selected', 'bg-green-600', 'text-white', 'font-bold');
                selected = { id: seatId, price, sideLabel: label, row: r, number: c };
                if (onSeatSelect) onSeatSelect(selected);
                document.dispatchEvent(new CustomEvent('seatSelected', { detail: selected }));
            }
        });

        return seat;
    }

    // buildStandGrid: supports 'row' (horizontal) and 'col' (vertical side stacks)
    function buildStandGrid(label, seatDir, seatSize, mobileDetail = false) {
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
            for (let c = 1; c <= cols; c++) {
                const col = document.createElement('div');
                col.style.display = 'flex';
                col.style.flexDirection = 'column';
                col.style.gap = gap + 'px';
                col.style.flex = '0 0 auto';
                col.style.boxSizing = 'border-box';
                for (let r = 1; r <= rows; r++) col.appendChild(createSeat(label, r, c, seatSize));
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

            const colCount = Math.max(1, Math.min(sideColumns, cols));
            for (let c = 1; c <= colCount; c++) {
                const col = document.createElement('div');
                col.className = 'seat-column-vertical';
                col.style.display = 'flex';
                col.style.flexDirection = 'column';
                col.style.gap = gap + 'px';
                col.style.boxSizing = 'border-box';
                col.style.flex = '0 0 auto';
                for (let seatNum = 1; seatNum <= sideRows; seatNum++) {
                    col.appendChild(createSeat(label, seatNum, c, seatSize, seatNum));
                }
                wrapper.appendChild(col);
            }
            return wrapper;
        }

        // horizontal rows
        const grid = document.createElement('div');
        grid.className = 'stand-grid';
        grid.style.display = 'flex';
        grid.style.flexDirection = 'column';
        grid.style.gap = gap + 'px';
        grid.style.boxSizing = 'border-box';
        grid.style.alignItems = 'center';
        grid.style.justifyContent = 'center';
        grid.style.width = '100%';
        for (let r = 1; r <= rows; r++) {
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
            for (let c = 1; c <= cols; c++) {
                line.appendChild(createSeat(label, r, c, seatSize));
            }
            grid.appendChild(line);
        }
        return grid;
    }

    // detail view container for mobile drilldown
    const detailView = document.createElement('div');
    detailView.className = 'seat-detail-view';
    detailView.style.display = 'none';
    detailView.style.width = '100%';
    detailView.style.height = '100%';
    detailView.style.overflowY = 'auto';
    detailView.style.padding = '6px';
    container.appendChild(detailView);

    let selected = null;
    const overview = document.createElement('div');

    function applyLayout() {
        const isMobile = window.innerWidth <= 768;

        // reset container while preserving detailView
        container.innerHTML = '';
        container.appendChild(detailView);

        if (isMobile) {
            // mobile overview (unchanged)
            overview.innerHTML = '';
            overview.style.display = 'grid';
            overview.style.gridTemplateColumns = '1fr auto 1fr';
            overview.style.gridTemplateRows = 'auto auto auto';
            overview.style.gap = '12px';
            overview.style.width = '100%';
            overview.style.boxSizing = 'border-box';
            overview.style.alignItems = 'center';
            overview.style.justifyItems = 'center';

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

                preview.addEventListener('click', () => showDetailView(label, seatDir));
                title.addEventListener('click', () => showDetailView(label, seatDir));
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

        // Desktop: measure container
        const contRect = container.getBoundingClientRect();
        let contW = contRect.width || container.clientWidth || window.innerWidth;
        let contH = contRect.height || container.clientHeight || window.innerHeight * 0.8;
        if (contH < 200) {
            contH = Math.max(contH, window.innerHeight * 0.6);
            container.style.minHeight = (contH | 0) + 'px';
        }

        // constants
        const courtH = Number(options.courtHeight ?? 120);
        const courtW = Number(options.courtWidth ?? 180);
        const titleH = 24;
        const verticalPadding = 20;
        const widthPadding = 8;

        // seat sizing bounds: allow fairly small seats to fit
        const minSeat = Number(options.minSeat ?? 6);
        const maxSeat = Number(options.maxSeat ?? 42);
        // start from a reasonable high size and reduce until fit
        let seatSize = Math.min(maxSeat, Math.floor(Math.min(contW / (cols + sideColumns * 2), contH / (sideRows + rows + 3))));

        if (seatSize < minSeat) seatSize = minSeat;

        function computeMetrics(sz) {
            const topStandWidth = Math.max(40, (sz * cols) + ((cols - 1) * gap) + widthPadding);
            const topStandHeight = titleH + (sz * rows) + ((rows - 1) * gap);

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

        // reduce seatSize until fits or until minSeat
        let metrics = computeMetrics(seatSize);
        let loop = 0;
        while ((metrics.requiredWidth > contW || metrics.requiredHeight > contH) && seatSize > minSeat && loop < 200) {
            seatSize--;
            metrics = computeMetrics(seatSize);
            loop++;
        }

        // If still doesn't fit (popup extremely small), allow scrolling fallback
        const allowScrollFallback = (metrics.requiredWidth > contW || metrics.requiredHeight > contH);

        // Build grid with fixed column widths and row heights so components don't overlap
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

            const gridNode = buildStandGrid(label, seatDir, seatSize, false);

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
        scaleWrapper.style.justifyContent = 'center';
        scaleWrapper.style.alignItems = 'flex-start';
        scaleWrapper.style.overflow = allowScrollFallback ? 'auto' : 'hidden';
        scaleWrapper.style.boxSizing = 'border-box';
        // set wrapper height so it's constrained to popup; if fallback allowed, wrapper will scroll
        scaleWrapper.style.height = (allowScrollFallback ? Math.min(metrics.requiredHeight, contH) + 'px' : contH + 'px');
        scaleWrapper.appendChild(gridRoot);
        container.appendChild(scaleWrapper);

        // final diagnostics and logging
        setTimeout(() => {
            try {
                console.log('renderSeatMap final', { seatSize, metrics, contW, contH, allowScrollFallback });
            } catch (e) { console.error(e); }
        }, 50);
    } // applyLayout

    function showDetailView(label, seatDir) {
        detailView.innerHTML = '';
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

        const isMobile = window.innerWidth <= 768;
        const gridNode = buildStandGrid(label, seatDir, 40, isMobile);
        detailView.appendChild(gridNode);

        setTimeout(() => {
            try { if (gridNode && typeof gridNode.scrollLeft !== 'undefined') gridNode.scrollLeft = 0; } catch (e) {}
        }, 20);

        overview.style.display = 'none';
        detailView.style.display = 'block';

        backBtn.addEventListener('click', () => {
            selected = null;
            if (onSeatSelect) onSeatSelect(null);
            document.dispatchEvent(new CustomEvent('seatSelected', { detail: null }));
            detailView.style.display = 'none';
            overview.style.display = 'grid';
        });
    }

    // initial render + resize
    applyLayout();
    const onResize = () => { try { applyLayout(); } catch (e) { console.error('seatMap resize error', e); } };
    window.addEventListener('resize', onResize);
    renderSeatMap._cleanup = () => { window.removeEventListener('resize', onResize); };
    window.renderSeatMap = renderSeatMap;
}
