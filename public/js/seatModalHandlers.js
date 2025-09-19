(function () {
    const buyBtn = document.getElementById('buyTicketBtn');
    const seatModal = document.getElementById('seatSelectionModal');
    const cancelSeatBtn = document.getElementById('cancelSeatBtn');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const confirmSeatBtn = document.getElementById('confirmSeatBtn');
    const purchaseConfirmBox = document.getElementById('purchaseConfirmBox');
    const finalizeBtn = document.getElementById('finalizePurchaseBtn');
    const cancelPurchaseBtn = document.getElementById('cancelPurchaseBtn');
    const selectedSeatInfo = document.getElementById('selectedSeatInfo');
    const confirmSeatText = document.getElementById('confirmSeatText');
    const seatMapContainer = document.getElementById('seatMap');

const takenSeats = JSON.parse(buyBtn.dataset.takenSeats || '[]');
const seatPrices = JSON.parse(buyBtn.dataset.seatPrices || '{}');
const matchId = buyBtn.dataset.matchId;
const ticketPrice = parseFloat(buyBtn.dataset.ticketPrice || '10');

    let selectedSeat = null;

    function initMap() {
        renderSeatMap(seatMapContainer,{
            rows:6,
            cols:12,
            takenSeats:takenSeats,
            seatPrices:seatPrices,
            matchId:matchId,
            onSeatSelect:(seat)=>{
                selectedSeat=seat;
                if(seat) selectedSeatInfo.textContent=`Izvēlētā vieta: ${seat.sideLabel} — Rinda ${seat.row}, Sēdeklis ${seat.number} | Cena: €${seat.price}`;
                else selectedSeatInfo.textContent='Izvēlētā vieta: Nav izvēlēta';
            }
        });
    }

    buyBtn.addEventListener('click',()=>{ seatModal.classList.remove('hidden'); initMap(); });
    cancelSeatBtn.addEventListener('click',()=>{ seatModal.classList.add('hidden'); selectedSeat=null; selectedSeatInfo.textContent='Izvēlētā vieta: Nav izvēlēta'; });
    modalCloseBtn.addEventListener('click',()=>{ seatModal.classList.add('hidden'); selectedSeat=null; selectedSeatInfo.textContent='Izvēlētā vieta: Nav izvēlēta'; });

    confirmSeatBtn.addEventListener('click',()=>{
        if(!selectedSeat){ alert('Lūdzu, vispirms izvēlieties vietu.'); return; }
        confirmSeatText.textContent=`${selectedSeat.sideLabel} — Rinda ${selectedSeat.row}, Sēdeklis ${selectedSeat.number}. Cena: €${selectedSeat.price}`;
        purchaseConfirmBox.classList.remove('hidden');
    });

    cancelPurchaseBtn.addEventListener('click',()=>{ purchaseConfirmBox.classList.add('hidden'); });
    finalizeBtn.addEventListener('click',()=>{
        if(!selectedSeat){ alert('Kļūda: nav izvēlētas vietas.'); return; }
        document.dispatchEvent(new CustomEvent('finalizePurchase',{detail:{matchId:matchId,seat:selectedSeat,price:selectedSeat.price ?? ticketPrice}}));
        purchaseConfirmBox.classList.add('hidden'); seatModal.classList.add('hidden');
    });
})();
