// public/js/seatModalHandlers.js
document.addEventListener('DOMContentLoaded', () => {
    const buyBtn = document.getElementById('buyTicketBtn');
    const modal = document.getElementById('seatSelectionModal');
    const seatMapContainer = document.getElementById('seatMap');
    const modalClose = document.getElementById('modalCloseBtn');
    const selectedSeatInfo = document.getElementById('selectedSeatInfo');

    if (!buyBtn) return;

    function openModal() {
        modal.classList.remove('hidden');

        // parse data attributes
        const takenSeats = JSON.parse(buyBtn.dataset.takenSeats || '[]');
        const seatPrices = JSON.parse(buyBtn.dataset.seatPrices || '{}');
        const defaultPrice = parseFloat(buyBtn.dataset.ticketPrice || '10');

        // render seat map
        if (typeof window.renderSeatMap === 'function') {
            // cleanup previous render if exists
            if (window.renderSeatMap._cleanup) {
                try { window.renderSeatMap._cleanup(); } catch(e) {}
            }
            window.renderSeatMap(seatMapContainer, {
                rows: 6,
                cols: 12,
                sideColumns: 6,
                sideRows: 12,
                takenSeats: takenSeats,
                seatPrices: seatPrices,
                ticketPrice: defaultPrice,
                minSeat: 8,
                maxSeat: 36,
                onSeatSelect: (selection) => {
                    updateSelectedSeat(selection);
                }
            });
        }
    }

    function updateSelectedSeat(selection) {
        if (!selection) {
            selectedSeatInfo.textContent = 'Izvēlētā vieta: Nav izvēlēta';
            return;
        }
        selectedSeatInfo.textContent = `Rinda ${selection.row}, Vieta ${selection.number}, Cena: €${selection.price}`;
        // store selected for other scripts via a custom event (existing matchPurchase.js listens)
        document.dispatchEvent(new CustomEvent('seatSelected', { detail: selection }));
    }

    buyBtn.addEventListener('click', openModal);
    modalClose.addEventListener('click', () => {
        modal.classList.add('hidden');
        updateSelectedSeat(null);
    });

    // close by clicking outside content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            updateSelectedSeat(null);
        }
    });

});
