/**
 * Wallet buy-discount page logic.
 * Requires window.VPWalletConfig = { buyUrl, costMap } set inline by the blade template.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var cfg     = window.VPWalletConfig || {};
    var buyUrl  = cfg.buyUrl  || '';
    var costMap = cfg.costMap || {};

    var csrfMeta       = document.querySelector('meta[name="csrf-token"]');
    var csrf           = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var walletBalanceEl = document.getElementById('coins');
    var resultBox       = document.getElementById('purchase-result');
    var purchasedCodeEl = document.getElementById('purchased-code');
    var myCardsList     = document.getElementById('my-cards-list');

    function showError(msg) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
    }

    async function buy(percent) {
      try {
        var res = await fetch(buyUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ discount_percent: percent })
        });

        var data = await res.json();

        if (!res.ok) {
          var err = (data && data.error) ? data.error : 'Kļūda pērkot atlažu karti';
          if (typeof err === 'object') err = JSON.stringify(err);
          showError(err);
          return;
        }

        var card = data.discount_card;
        var cost = costMap[card.discount_percent] || 0;
        var cur  = parseInt((walletBalanceEl || {}).textContent || '0', 10);
        if (walletBalanceEl) walletBalanceEl.textContent = Math.max(0, cur - cost);

        var node = document.createElement('div');
        node.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100';
        node.innerHTML =
          '<div class="flex items-center gap-3">' +
            '<div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-400 to-yellow-300 flex items-center justify-center shadow-sm">' +
              '<span class="text-xs font-extrabold text-white">' + card.discount_percent + '%</span>' +
            '</div>' +
            '<div>' +
              '<div class="text-sm font-medium text-gray-800">' + card.discount_percent + '% atlaide' +
                '<span class="ml-2 text-xs text-green-600">&#x25CF; Aktīva</span></div>' +
              '<div class="text-xs text-gray-400 font-mono">' + card.code + '</div>' +
            '</div>' +
          '</div>' +
          '<button class="copy-code-btn px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50' +
            ' text-gray-700 text-xs font-medium rounded-lg transition" data-code="' + card.code + '">Kopēt</button>';

        var empty = myCardsList ? myCardsList.querySelector('.text-center') : null;
        if (empty) empty.remove();
        if (myCardsList) myCardsList.prepend(node);

        if (purchasedCodeEl) purchasedCodeEl.textContent = card.code;
        if (resultBox) {
          resultBox.classList.remove('hidden');
          resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      } catch (err) {
        showError((err && err.message) || 'Tīkla kļūda');
      }
    }

    document.querySelectorAll('.buy-discount-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var percent = parseInt(this.dataset.percent, 10);
        if (typeof window.vpConfirm === 'function') {
          window.vpConfirm('Iegādāties ' + percent + '% atlažu karti?', function () { buy(percent); }, { confirmText: 'Iegādāties' });
        } else {
          buy(percent);
        }
      });
    });

    document.body.addEventListener('click', function (e) {
      var btn = e.target.matches('.copy-code-btn') ? e.target
              : e.target.matches('#copy-purchased-code') ? e.target
              : null;
      if (!btn) return;
      var code = btn.dataset.code || (purchasedCodeEl ? purchasedCodeEl.textContent : '');
      if (!code) return;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(function () {
          btn.textContent = 'Kopēts!';
          setTimeout(function () { btn.textContent = 'Kopēt'; }, 2000);
        }).catch(function () { prompt('Kopē šo kodu:', code); });
      } else {
        prompt('Kopē šo kodu:', code);
      }
    });
  });
})();
