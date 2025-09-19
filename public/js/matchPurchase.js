document.addEventListener('DOMContentLoaded', () => {
    const buyBtn = document.getElementById('buyTicketBtn');
    const seatModal = document.getElementById('seatSelectionModal');
    let selectedSeat = null;

    if (!buyBtn) return;

    const confirmBtn = document.getElementById('confirmSeatBtn');  // seat modal confirm
    const cancelBtn = document.getElementById('cancelSeatBtn');
    const purchaseConfirmBox = document.getElementById('purchaseConfirmBox');
    const confirmSeatText = document.getElementById('confirmSeatText');
    const finalizeBtn = document.getElementById('finalizePurchaseBtn'); // popup "Jā, pirkt"
    const cancelPurchaseBtn = document.getElementById('cancelPurchaseBtn');

    // ❌ Do not redirect here
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (!selectedSeat) {
                alert('Lūdzu, izvēlieties vietu vispirms.');
                return;
            }
            // Show confirmation popup
            confirmSeatText.textContent =
                `Rinda ${selectedSeat.row}, Vietas ${selectedSeat.number}, Cena: €${selectedSeat.price ?? buyBtn.dataset.ticketPrice}`;
            purchaseConfirmBox.classList.remove('hidden');
        });
    }

    if (cancelPurchaseBtn) {
        cancelPurchaseBtn.addEventListener('click', () => {
            purchaseConfirmBox.classList.add('hidden');
        });
    }

    // ✅ Redirect only here
    if (finalizeBtn) {
        finalizeBtn.addEventListener('click', async () => {
            finalizeBtn.disabled = true;
            finalizeBtn.textContent = 'Pāradresē uz Stripe...';

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        match_id: buyBtn.dataset.matchId,
                        seat: selectedSeat,
                        price: selectedSeat.price ?? parseFloat(buyBtn.dataset.ticketPrice || '10')
                    })
                });

                const data = await response.json();
                if (response.ok && data.url) {
                    window.location.href = data.url;
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert('Checkout failed. Please try again.');
                }
            } catch (err) {
                console.error('Checkout error:', err);
                alert('An error occurred while processing the payment.');
            } finally {
                finalizeBtn.disabled = false;
                finalizeBtn.textContent = 'Jā, pirkt';
            }
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            seatModal.classList.add('hidden');
            selectedSeat = null;
        });
    }

    document.addEventListener('seatSelected', e => {
        selectedSeat = e.detail;
    });
});
