(function () {
  'use strict';

  // Izsūta Alpine.js toast paziņojumu (izmanto globālo toast sistēmu)
  function showToast(message, type = 'error') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
  }

  // Iegūst CSRF tokenu no meta taga
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const token = meta ? meta.getAttribute('content') : null;
    if (!token) console.error('[matchPurchase] CSRF token not found in meta tag.');
    return token;
  }

  // Veic fetch pieprasījumu ar automātisku CSRF galveni
  async function safeFetch(url, options = {}) {
    const csrf = getCsrfToken();
    const headers = {
      'Accept': 'application/json',
      ...(options.headers || {}),
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    };

    const opts = {
      ...options,
      headers,
      credentials: 'same-origin',
    };

    const res = await fetch(url, opts);
    return res;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const finalizeBtn = document.getElementById('finalizePurchaseBtn');
    const buyBtn = document.getElementById('buyTicketBtn');

    // Atlaides koda poga un informatīvais teksts
    const discountInput = document.getElementById('discountCodeInput');
    const applyBtn = document.getElementById('applyDiscountBtn');
    const clearBtn = document.getElementById('clearDiscountBtn');
    const discountInfo = document.getElementById('discountInfo');

    if (!finalizeBtn || !buyBtn) {
      console.error('[matchPurchase] Required buttons not found in DOM.');
      return;
    }

    let selectedSeat = [];
    let appliedDiscountCode = null;
    let appliedDiscountPercent = 0;
    const newUserPromo = parseInt(buyBtn.dataset.newUserPromo || '0');

    document.addEventListener('seatSelected', e => {
      selectedSeat = e.detail || [];
      finalizeBtn.disabled = !(selectedSeat && selectedSeat.length > 0);
  
      if (appliedDiscountCode) {
        discountInfo.style.color = '';
        discountInfo.textContent = `Kods "${appliedDiscountCode}" pielietots — pārbaudiet summu pirms maksājuma. Ja mainīji vietas, klikšķini "Pielietot" vēlreiz.`;
      } else if (newUserPromo > 0 && selectedSeat.length > 0) {
        discountInfo.style.color = '#16a34a';
        discountInfo.textContent = `🎉 Jaunā lietotāja atlaide: ${newUserPromo}% — automātiski pielietota jūsu pirmajiem 3 pirkumiem!`;
      } else if (newUserPromo === 0 && !appliedDiscountCode) {
        discountInfo.style.color = '';
        discountInfo.textContent = '';
      }
    });

    // Atlaides koda validācija pret serveri
    if (applyBtn && discountInput) {
      applyBtn.addEventListener('click', async () => {
        const code = (discountInput.value || '').trim();
        if (!code) {
          discountInfo.textContent = 'Ievadi derīgu atlaides kodu.';
          return;
        }

        const seatsForPreview = (selectedSeat || []).map(s => ({ seat_id: s.dbId || s.id, price: s.price || 0 }));
        try {
          applyBtn.disabled = true;
          discountInfo.textContent = 'Pārbauda kodu...';

          const res = await safeFetch('/payment/validate-discount', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ discount_code: code, seats: seatsForPreview }),
          });

          const data = await res.json().catch(() => ({}));

          if (!res.ok) {
            const err = (data && (data.error || data.message)) ? (data.error || data.message) : ('Server error ' + res.status);
            discountInfo.style.color = '#dc2626';
            discountInfo.textContent = `Kļūda: ${err}`;
            appliedDiscountCode = null;
            appliedDiscountPercent = 0;
          } else {
            appliedDiscountCode = code;
            appliedDiscountPercent = data.discount_percent || 0;
            discountInfo.style.color = '';
            if (data.discounted_total !== undefined) {
              discountInfo.textContent = `Atlaide: ${appliedDiscountPercent}% — Oriģināls: €${(data.original_total).toFixed(2)} → Pēc atlaides: €${(data.discounted_total).toFixed(2)} (Ietaupījums: €${(data.saving).toFixed(2)})`;
            } else {
              discountInfo.textContent = `Atlaide: ${appliedDiscountPercent}% — tiks pielietota pie maksājuma.`;
            }
          }
        } catch (err) {
          discountInfo.textContent = 'Tīkla kļūda pārbaudot kodu.';
          appliedDiscountCode = null;
          appliedDiscountPercent = 0;
        } finally {
          applyBtn.disabled = false;
        }
      });
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        appliedDiscountCode = null;
        appliedDiscountPercent = 0;
        discountInput.value = '';
        discountInfo.style.color = '';
        if (newUserPromo > 0 && selectedSeat.length > 0) {
          discountInfo.style.color = '#16a34a';
          discountInfo.textContent = `🎉 Jaunā lietotāja atlaide: ${newUserPromo}% — automātiski pielietota jūsu pirmajiem 3 pirkumiem!`;
        } else {
          discountInfo.textContent = '';
        }
      });
    }

    finalizeBtn.addEventListener('click', async () => {
      if (finalizeBtn.disabled) return;
      if (!selectedSeat || selectedSeat.length === 0) {
        showToast('Nav izvēlēta vieta. Lūdzu izvēlies vietu pirms maksājuma.', 'warning');
        return;
      }

      const missing = selectedSeat.filter(s => !s.dbId);
      if (missing.length > 0) {
        console.error('[matchPurchase] Some seats missing dbId:', missing);
        showToast('Vienai vai vairākām izvēlētajām vietām nav derīga ID. Sazinies ar administratoru.', 'error');
        return;
      }

      finalizeBtn.disabled = true;
      const prevText = finalizeBtn.textContent;
      finalizeBtn.textContent = 'Apstrādā...';

      try {
       
        for (const sel of selectedSeat) {
          const res = await safeFetch(`/seats/${sel.dbId}/reserve`, {
            method: 'POST',
          });

          if (!res.ok) {
            let msg = `Vieta ${sel.row}-${sel.number} aizņemta vai server error (${res.status}).`;
            try {
              const json = await res.json();
              msg = json.error || json.message || msg;
            } catch (_) {}
            if (res.status === 419) msg = 'CSRF token mismatch — iespējams lapa ir novecojusi. Atsvaidzini lapu un mēģini vēlreiz.';
            showToast(msg, 'error');
            finalizeBtn.disabled = false;
            finalizeBtn.textContent = prevText;
            return;
          }
        }

        const payload = {
          match_id: buyBtn.dataset.matchId,
          seats: selectedSeat.map(s => ({
            seat_id: s.dbId,
            seat_row: s.row,
            seat_number: s.number,
            price: s.price,
          })),
        };

        if (appliedDiscountCode) payload.discount_code = appliedDiscountCode;

        const checkoutResp = await safeFetch('/checkout', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });

        if (!checkoutResp.ok) {
          let text = '';
          try { text = await checkoutResp.text(); } catch (_) {}
          console.error('[matchPurchase] Checkout failed', checkoutResp.status, text);
          showToast('Maksājuma kļūda — lūdzu mēģini vēlreiz.', 'error');
          finalizeBtn.disabled = false;
          finalizeBtn.textContent = prevText;
          return;
        }

        const data = await checkoutResp.json().catch(() => ({}));
        if (data && data.url) {
          // Mark that we're leaving for Stripe so pageshow can release on return
          sessionStorage.setItem('stripeCheckoutPending', '1');
          window.location.href = data.url;
          return;
        }

        showToast(data.error || data.message || 'Maksājuma kļūda — lūdzu mēģini vēlreiz.', 'error');
      } catch (err) {
        console.error('[matchPurchase] Exception:', err);
        showToast('Radās kļūda apstrādes laikā. Pārbaudi savienojumu vai mēģini vēlreiz.', 'error');
      } finally {
        finalizeBtn.disabled = false;
        finalizeBtn.textContent = prevText;
      }
    });
  });

  // Atbrīvo rezervācijas ja lietotājs atgriežas no Stripe ar pārlūka atpakaļ pogu.
  // Pārbauda sessionStorage karogu neatkarīgi no tā vai lapa tika kešota vai no jauna ielādēta.
  window.addEventListener('pageshow', function () {
    if (sessionStorage.getItem('stripeCheckoutPending') === '1') {
      sessionStorage.removeItem('stripeCheckoutPending');
      safeFetch('/payment/release-reservation', {
        method: 'POST',
        credentials: 'include',
      }).catch(function () {});
    }
  });
})();
