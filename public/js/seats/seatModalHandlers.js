(function () {
  'use strict';

  // Drošs JSON lasītājs no datu atribūta
  function parseJsonAttr(el, name, fallback) {
    try {
      const raw = el.getAttribute(name) || el.dataset[name.replace('data-', '')] || null;
      return raw ? JSON.parse(raw) : fallback;
    } catch (e) {
      console.warn('parseJsonAttr failed', name, e);
      return fallback;
    }
  }

  // Normalizē sēdvietas nosaukumu uz slugu salīdzināšanai
  function normalizeToSlug(s) {
    try {
      const base = (s || '').toString();
      const n = base.normalize ? base.normalize('NFKD').replace(/[\u0300-\u036f]/g, '') : base;
      return n.replace(/[^A-Za-z0-9]+/g, '-').toLowerCase();
    } catch (e) {
      return String(s || '').replace(/[^A-Za-z0-9]+/g, '-').toLowerCase();
    }
  }

  // Atrod DB sēdvietas ID pēc rindu/kolonnu koordinātām vai etiķetes
  function findDbIdForSeat(seatIdMap, seatId, row, number, label) {
    if (!seatIdMap || typeof seatIdMap !== 'object') return null;

    if (seatId && Object.prototype.hasOwnProperty.call(seatIdMap, seatId)) return seatIdMap[seatId];
    if (label) {
      const sl = normalizeToSlug(label);
      const canonical = `${sl}-${row}-${number}`;
      if (Object.prototype.hasOwnProperty.call(seatIdMap, canonical)) return seatIdMap[canonical];
    }
    const simple = `${row}-${number}`;
    if (Object.prototype.hasOwnProperty.call(seatIdMap, simple)) return seatIdMap[simple];

    const suffix = `-${row}-${number}`;
    for (const k in seatIdMap) {
      if (!Object.prototype.hasOwnProperty.call(seatIdMap, k)) continue;
      if (typeof k !== 'string') continue;
      if (k.endsWith(suffix)) return seatIdMap[k];
    }
    return null;
  }

  // Gaida līdz renderSeatMap ielādējas (async, ar taimauta pārbaudi)
  function waitForRenderSeatMap(timeoutMs = 3000) {
    return new Promise((resolve, reject) => {
      if (typeof window.renderSeatMap === 'function') return resolve(window.renderSeatMap);
      const start = Date.now();
      const iv = setInterval(() => {
        if (typeof window.renderSeatMap === 'function') {
          clearInterval(iv);
          return resolve(window.renderSeatMap);
        }
        if (Date.now() - start > timeoutMs) {
          clearInterval(iv);
          return reject(new Error('renderSeatMap not available'));
        }
      }, 60);
    });
  }

  function safeRenderSeatMap(container, opts) {
    return new Promise((resolve, reject) => {
      try {
        const res = window.renderSeatMap(container, opts);
        requestAnimationFrame(() => requestAnimationFrame(() => resolve(res)));
      } catch (err) {
        reject(err);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const buyBtn = document.getElementById('buyTicketBtn');
    const modal = document.getElementById('seatSelectionModal');
    const seatMapContainer = document.getElementById('seatMap');
    const modalClose = document.getElementById('modalCloseBtn');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const totalPriceEl = document.getElementById('totalPrice');
    const finalizeBtn = document.getElementById('finalizePurchaseBtn');

    const mobileOverlay = document.getElementById('mobileSummaryOverlay');
    const mobileSeatsList = document.getElementById('mobileSelectedSeatsList');
    const mobileTotalPrice = document.getElementById('mobileTotalPrice');
    const mobileFinalizeBtn = document.getElementById('mobileFinalizeBtn');

    const summaryToggleBtn = document.getElementById('summaryToggleBtn');
    const summaryPanel = document.getElementById('summaryPanel');
    const summaryHeader = document.getElementById('summaryHeader');

    if (!buyBtn || !modal || !seatMapContainer || !selectedSeatsList || !finalizeBtn) {
      console.error('seatModalHandlers: required elements missing');
      return;
    }

    try { const mb = document.getElementById('mobileSelectedBadge'); if (mb) mb.style.display = 'none'; } catch (e) {}

    let currentSeatIdMap = {};
    window.currentSelectedSeat = [];
    let lastMapRenderAt = 0;

    function ensureScaleWrapper() {
      let wrapper = seatMapContainer.querySelector('.seat-map-scale-wrapper');
      if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.className = 'seat-map-scale-wrapper';
        while (seatMapContainer.firstChild) wrapper.appendChild(seatMapContainer.firstChild);
        seatMapContainer.appendChild(wrapper);
      } else {
        wrapper.classList.add('seat-map-scale-wrapper');
      }
    }

    function updateSummaryUI(selections) {
      const seats = Array.isArray(selections) ? selections : [];
      window.currentSelectedSeat = seats;

      const isMobile = window.innerWidth <= 768;
      selectedSeatsList.style.maxHeight = isMobile ? '240px' : '300px';
      selectedSeatsList.style.overflowY = 'auto';
      selectedSeatsList.style.padding = '6px';
      selectedSeatsList.innerHTML = '';

      if (summaryHeader) {
        const count = seats.length || 0;
        summaryHeader.textContent = `Kopsavilkums — ${count} viet${count === 1 ? '' : 'as'}`;
      }

      const hasSeats = seats && seats.length > 0;
      if (!hasSeats) {
        selectedSeatsList.innerHTML = '<div class="text-gray-500 text-sm italic">Nav izvēlētu vietu</div>';
        if (totalPriceEl) totalPriceEl.classList.add('hidden');
        finalizeBtn.disabled = true;
        if (mobileOverlay && mobileSeatsList) {
          mobileSeatsList.innerHTML = '<div class="text-gray-500 text-sm italic">Nav izvēlētu vietu</div>';
          if (mobileTotalPrice) mobileTotalPrice.textContent = '';
          if (mobileFinalizeBtn) mobileFinalizeBtn.disabled = true;
        }
        return;
      }

      let total = 0;
      const previewCount = isMobile ? 1 : seats.length;

      seats.forEach((s, idx) => {
        total += (s.price || 0);
        if (idx >= previewCount && isMobile) return;
        const card = document.createElement('div');
        card.className = 'bg-white p-2 rounded-lg border flex justify-between items-center shadow-sm';
        card.style.marginBottom = '6px';

        // Handle custom seats vs grid seats
        let seatLabel, seatInfo;
        if (s.seatData) {
          // Custom seat
          seatLabel = s.seatData.number || s.seatData.id || 'Custom Seat';
          seatInfo = `Custom Seat ${seatLabel}`;
        } else {
          // Grid seat
          seatLabel = s.sideLabel || 'Nezināma tribīne';
          seatInfo = `Rinda ${s.row}, Vieta ${s.number}`;
        }

        card.innerHTML = `
  <div>
    <div class="font-semibold text-indigo-700">${seatLabel}</div>
    <div class="font-medium">${seatInfo}</div>
    <div class="text-sm text-gray-500">Cena: €${(s.price ?? 0).toFixed(2)}</div>
  </div>
  <button class="text-red-500 hover:text-red-700 text-sm font-semibold remove-seat-btn" data-key="${s.id}">✕</button>
`;
        selectedSeatsList.appendChild(card);
      });

      if (totalPriceEl) {
        totalPriceEl.textContent = `Kopā: €${total.toFixed(2)}`;
        totalPriceEl.classList.remove('hidden');
      }
      finalizeBtn.disabled = false;
      if (mobileFinalizeBtn) mobileFinalizeBtn.disabled = false;

      if (mobileOverlay && mobileSeatsList && mobileTotalPrice) {
        mobileSeatsList.innerHTML = '';
        seats.slice(0, 1).forEach(s => {
          const r = document.createElement('div');
          r.className = 'p-2 border-b flex justify-between items-center';

          let seatLabel, seatInfo;
          if (s.seatData) {
            seatLabel = s.seatData.number || s.seatData.id || 'Custom Seat';
            seatInfo = `Custom ${seatLabel}`;
          } else {
            seatLabel = s.sideLabel || 'Nezināma tribīne';
            seatInfo = `R${s.row} — V${s.number}`;
          }

          r.innerHTML = `
    <div>
      <div class="font-semibold text-indigo-700">${seatLabel}</div>
      <div>${seatInfo}</div>
    </div>
    <div>€${(s.price ?? 0).toFixed(2)}</div>
  `;
          mobileSeatsList.appendChild(r);
        });

        mobileTotalPrice.textContent = `Kopā: €${total.toFixed(2)}`;
      }

      Array.from(selectedSeatsList.querySelectorAll('.remove-seat-btn')).forEach(btn => {
        btn.addEventListener('click', () => {
          const key = btn.dataset.key;
          if (!key) return;
          try {
            if (seatMapContainer._seatMap && typeof seatMapContainer._seatMap.toggleByKey === 'function') {
              seatMapContainer._seatMap.toggleByKey(key);
            } else {
              const el = seatMapContainer.querySelector(`[data-key="${CSS && CSS.escape ? CSS.escape(key) : key}"]`) ||
                         seatMapContainer.querySelector(`[data-id="${CSS && CSS.escape ? CSS.escape(key) : key}"]`);
              if (el) el.click();
            }
          } catch (e) {
            console.error('remove-seat-btn click failed', e);
          }
        });
      });
    }
    async function refreshSeatsForMatch(matchId, { render = true } = {}) {
      if (!matchId) return;
      try {
        const res = await fetch(`/seats/${matchId}`, { credentials: 'same-origin' });
        if (!res.ok) {
          console.warn('refreshSeatsForMatch failed', res.status);
          return;
        }
        const seats = await res.json();

        const takenSeatIds = [];
        const takenSeats = [];
        const reservedSeatIds = [];
        const reservedSeats = [];
        const seatPrices = {};
        const seatIds = {};
        const nowTs = Date.now();

        seats.forEach(s => {
          const side = s.side || '';
          const humanKey = `${side}-${s.row}-${s.number}`;
          const canonical = `${normalizeToSlug(side)}-${s.row}-${s.number}`;
          seatIds[canonical] = s.id;
          seatPrices[canonical] = s.price;
          seatPrices[humanKey] = s.price;
          if (s.seat_number) seatIds[s.seat_number] = s.id;

          if (s.ticket_id !== null && s.ticket_id !== undefined) {
            takenSeatIds.push(String(s.id));
            takenSeats.push(humanKey);
            // Also push the raw seat_number so arena layout seats ("s1") match
            if (s.seat_number && s.seat_number !== humanKey) takenSeats.push(s.seat_number);
          } else {
            if (s.reserved_until) {
              const ru = Date.parse(s.reserved_until);
              if (!Number.isNaN(ru) && ru > nowTs) {
                reservedSeatIds.push(String(s.id));
                reservedSeats.push(humanKey);
                if (s.seat_number && s.seat_number !== humanKey) reservedSeats.push(s.seat_number);
              }
            }
          }
        });

        try {
          buyBtn.dataset.takenSeatIds = JSON.stringify(takenSeatIds);
          buyBtn.dataset.takenSeats = JSON.stringify(takenSeats);
          buyBtn.dataset.seatPrices = JSON.stringify(seatPrices);
          buyBtn.dataset.seatIds = JSON.stringify(seatIds);
          buyBtn.dataset.reservedSeatIds = JSON.stringify(reservedSeatIds);
          buyBtn.dataset.reservedSeats = JSON.stringify(reservedSeats);
        } catch (e) {}

        currentSeatIdMap = Object.assign({}, currentSeatIdMap, seatIds);

        if (!render) {
          return;
        }

        try {
          await waitForRenderSeatMap(2000);
          if (typeof window.renderSeatMap === 'function') {
            const now = Date.now();
            if (now - lastMapRenderAt > 200) {
              await safeRenderSeatMap(seatMapContainer, {
                rows: 6, cols: 12, sideColumns: 6, sideRows: 12,
                takenSeats, takenSeatIds,
                reservedSeats, reservedSeatIds,
                seatPrices, seatIds,
                ticketPrice: Number(buyBtn.dataset.ticketPrice || 10),
                seatIds: seatIds,
                customElements: parseJsonAttr(buyBtn, 'data-custom-elements', null),
                selectedDbIds: (window.currentSelectedSeat || []).map(s => String(s.dbId || s.id)).filter(Boolean)
              });
              lastMapRenderAt = Date.now();
            }
          }
        } catch (e) {
          console.warn('renderSeatMap not ready after refresh', e);
        }
      } catch (err) {
        console.error('refreshSeatsForMatch error', err);
      }
    }

    window.refreshSeatsForMatch = refreshSeatsForMatch;

    async function openModal() {
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      if (window.innerWidth <= 768 && summaryPanel) {
        summaryPanel.classList.add('mobile-pinned-summary');
      } else if (summaryPanel) {
        summaryPanel.classList.remove('mobile-pinned-summary');
      }

      currentSeatIdMap = parseJsonAttr(buyBtn, 'data-seat-ids', {}) || parseJsonAttr(buyBtn, 'data-seatIds', {}) || currentSeatIdMap;
      const matchId = buyBtn.dataset.matchId;
      if (matchId) {
        await refreshSeatsForMatch(matchId, { render: false });
      }

      const takenSeats = parseJsonAttr(buyBtn, 'data-taken-seats', []) || [];
      const takenSeatIds = parseJsonAttr(buyBtn, 'data-taken-seat-ids', []) || [];
      const reservedSeats = parseJsonAttr(buyBtn, 'data-reserved-seats', []) || [];
      const reservedSeatIds = parseJsonAttr(buyBtn, 'data-reserved-seat-ids', []) || [];
      const seatPrices = parseJsonAttr(buyBtn, 'data-seat-prices', {}) || {};
      const customElements = parseJsonAttr(buyBtn, 'data-custom-elements', null);
      const arenaWidth  = Number(buyBtn.dataset.arenaWidth)  || 0;
      const arenaHeight = Number(buyBtn.dataset.arenaHeight) || 0;

      try {
        await waitForRenderSeatMap(2000);
      } catch (e) {
        console.warn('renderSeatMap not available on openModal', e);
      }

      if (typeof window.renderSeatMap === 'function') {
        try {
          // single authoritative render; wait for paint frames to settle before resolving
          await safeRenderSeatMap(seatMapContainer, {
            rows: 6, cols: 12, sideColumns: 6, sideRows: 12,
            takenSeats, takenSeatIds, reservedSeats, reservedSeatIds,
            seatPrices, ticketPrice: Number(buyBtn.dataset.ticketPrice || 10),
            seatIds: currentSeatIdMap,
            customElements: customElements,
            arenaWidth, arenaHeight,
            selectedDbIds: (window.currentSelectedSeat || []).map(s => String(s.dbId || s.id)).filter(Boolean),
            onSeatSelect: (selectedSeats) => {
              if (!Array.isArray(selectedSeats)) {
                updateSummaryUI([]);
                return;
              }
              selectedSeats.forEach(s => {
                if (!s.dbId) {
                  s.dbId = findDbIdForSeat(currentSeatIdMap, s.id, s.row, s.number, s.sideLabel) || null;
                }
              });
              ensureScaleWrapper();
              updateSummaryUI(selectedSeats);
            }
          });
          requestAnimationFrame(() => ensureScaleWrapper());
          lastMapRenderAt = Date.now();
        } catch (e) {
          console.error('renderSeatMap threw in openModal', e);
        }
      } else {
        console.error('renderSeatMap not defined - check script order');
      }
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      updateSummaryUI([]);
      try {
        const sel = seatMapContainer.querySelectorAll('.selected, .seat-item.selected');
        sel.forEach(n => n.classList.remove('selected', 'bg-blue-600', 'bg-green-600', 'text-white', 'font-bold', 'font-semibold', 'shadow-md'));
      } catch (e) {}
      // Clear the internal selection Set so re-opening modal starts fresh
      try {
        if (seatMapContainer._seatMap && seatMapContainer._seatMap.selected) {
          seatMapContainer._seatMap.selected.clear();
        }
      } catch (e) {}
      window.currentSelectedSeat = [];
      if (summaryPanel) summaryPanel.classList.remove('mobile-pinned-summary');
    }

  
    buyBtn.addEventListener('click', openModal);
    if (modalClose) modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    if (summaryToggleBtn && summaryPanel) {
      summaryToggleBtn.addEventListener('click', () => {
        const wasHidden = summaryPanel.classList.contains('hidden');
        summaryPanel.classList.toggle('hidden');
        if (wasHidden && !summaryPanel.classList.contains('hidden')) {
          if (window.innerWidth <= 768) summaryPanel.classList.add('mobile-pinned-summary');
          requestAnimationFrame(() => {
            updateSummaryUI(window.currentSelectedSeat || []);
            requestAnimationFrame(() => {
              try { summaryPanel.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            });
          });
        }
      });
    }
    document.addEventListener('seatSelected', (e) => {
      const seats = e.detail || [];
      if (Array.isArray(seats)) {
        seats.forEach(s => {
          if (!s.dbId) s.dbId = findDbIdForSeat(currentSeatIdMap, s.id, s.row, s.number, s.sideLabel) || null;
        });
        updateSummaryUI(seats);
      } else {
        updateSummaryUI([]);
      }
    });

    if (mobileOverlay) {
      mobileOverlay.addEventListener('click', (e) => {
        if (e.target === mobileOverlay) mobileOverlay.classList.add('hidden');
      });
    }
    if (mobileFinalizeBtn) {
      mobileFinalizeBtn.addEventListener('click', () => {
        const finalize = document.getElementById('finalizePurchaseBtn');
        if (finalize) finalize.click();
      });
    }

    window.addEventListener('focus', () => {
      const matchId = buyBtn.dataset.matchId;
      if (matchId) refreshSeatsForMatch(matchId, { render: true });
    });
  });
})();
