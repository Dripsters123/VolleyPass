// public/js/matchPurchase.js
document.addEventListener('DOMContentLoaded', () => {
    const buyBtn = document.getElementById('buyTicketBtn');
    if (!buyBtn) return;

    const confirmBtn = document.getElementById('confirmSeatBtn');  // seat modal confirm
    const purchaseConfirmBox = document.getElementById('purchaseConfirmBox');
    const confirmSeatText = document.getElementById('confirmSeatText');
    const finalizeBtn = document.getElementById('finalizePurchaseBtn'); // popup "Jā, pirkt"
    const cancelPurchaseBtn = document.getElementById('cancelPurchaseBtn');

    let selectedSeat = null;

    document.addEventListener('seatSelected', e => {
        selectedSeat = e.detail;
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (!selectedSeat) {
                alert('Lūdzu, izvēlieties vietu vispirms.');
                return;
            }
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

    if (finalizeBtn) {
        finalizeBtn.addEventListener('click', async () => {
            finalizeBtn.disabled = true;
            finalizeBtn.textContent = 'Pāradresē uz Stripe...';

            try {
                // coerce everything to strings (prevent sending objects/arrays that validation rejects)
                const payload = {
                    match_id: String(buyBtn.dataset.matchId ?? ''),
                    seat_id: selectedSeat?.id ? String(selectedSeat.id) : '',
                    seat_number: selectedSeat?.number ? String(selectedSeat.number) : '',
                    seat_row: selectedSeat?.row ? String(selectedSeat.row) : '',
                    price: selectedSeat?.price != null
                        ? String(Number(selectedSeat.price))
                        : String(parseFloat(buyBtn.dataset.ticketPrice || '10'))
                };

                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.url) {
                    window.location.href = data.url;
                    return;
                }

                // Show helpful error message (validation errors come as data.errors)
                if (data.errors) {
                    // Laravel validation errors: object with arrays
                    const flat = Object.values(data.errors).flat().join('\n');
                    alert('Validation failed:\n' + flat);
                    console.error('Validation errors:', data.errors);
                } else if (data.error || data.message) {
                    alert(data.error || data.message);
                    console.error('Error response:', data);
                } else {
                    // fallback
                    alert('Checkout failed. Please try again. See console for details.');
                    console.error('Unknown response:', response.status, data);
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
});
