(function () {
  var A = window.APPLY; if (!A) return;
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  var modal = $('#pay-modal'), cur = null, timer = null, lastFocus = null;
  function post(url, data) { var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('csrf', A.csrf); return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }); }
  function loadScript(src) { return new Promise(function (ok, bad) { if (document.querySelector('script[src="' + src + '"]')) return ok(); var s = document.createElement('script'); s.src = src; s.onload = ok; s.onerror = function () { bad(new Error('load')); }; document.head.appendChild(s); }); }
  function msg(t, bad) { var m = $('#pay-msg'); m.textContent = t || ''; m.className = 'pay-msg' + (bad ? ' bad' : ''); }
  function openModal() { lastFocus = document.activeElement; modal.hidden = false; document.body.classList.add('modal-open'); $('.pay-x').focus(); }
  function closeModal() { modal.hidden = true; document.body.classList.remove('modal-open'); clearInterval(timer); if (lastFocus && lastFocus.focus) lastFocus.focus(); }
  function tab(name) { $$('.pay-tabs button').forEach(function (b) { b.classList.toggle('on', b.dataset.tab === name); }); $$('.pay-panel').forEach(function (p) { p.hidden = p.dataset.panel !== name; }); msg(''); }

  /* shows the form on the same page once the payment is confirmed (no redirect) */
  function reveal(token) {
    closeModal();
    fetch(A.urls.page + '?fragment=1&token=' + encodeURIComponent(token), { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (h) {
      var area = $('#apply-area'); area.innerHTML = h; initForm();
      window.scrollTo({ top: area.getBoundingClientRect().top + window.pageYOffset - 100, behavior: 'smooth' });
      if (window.history && history.replaceState) history.replaceState(null, '', A.urls.page + '?token=' + token);
    }).catch(function () { window.location.href = A.urls.page + '?token=' + encodeURIComponent(token); });
  }
  function verify(provider, ref, tx) {
    msg('Confirming your payment, please wait...');
    post(A.urls.verify, { provider: provider, ref: ref, tx: tx || '' }).then(function (j) { if (j.ok) reveal(j.token); else msg(j.error || 'We could not confirm the payment.', true); }).catch(function () { msg('Network problem while confirming. Please try again.', true); });
  }
  function paystack(channels) {
    if (!A.paystack) return msg('Paystack is not available yet. Please choose another method.', true);
    loadScript('https://js.paystack.co/v1/inline.js').then(function () {
      PaystackPop.setup({ key: A.paystack, email: cur.email, amount: Math.round(cur.amount * 100), currency: 'NGN', ref: cur.ref, channels: channels,
        metadata: { custom_fields: [{ display_name: 'Parent', variable_name: 'parent', value: cur.name }] },
        callback: function (r) { verify('paystack', r.reference, ''); }, onClose: function () { msg('Window closed. If you already paid, wait a few seconds and press the button again.'); } }).openIframe();
    }).catch(function () { msg('Could not load Paystack. Check your internet connection.', true); });
  }
  function flutterwave(opts) {
    if (!A.flw) return msg('Flutterwave is not available yet. Please choose another method.', true);
    loadScript('https://checkout.flutterwave.com/v3.js').then(function () {
      FlutterwaveCheckout({ public_key: A.flw, tx_ref: cur.ref, amount: cur.amount, currency: 'NGN', payment_options: opts,
        customer: { email: cur.email, phone_number: cur.phone, name: cur.name }, customizations: { title: A.school, description: 'Application form fee' },
        callback: function (d) { if (d && d.transaction_id) verify('flutterwave', cur.ref, String(d.transaction_id)); }, onclose: function () {} });
    }).catch(function () { msg('Could not load Flutterwave. Check your internet connection.', true); });
  }
  function poll() {
    clearInterval(timer);
    timer = setInterval(function () { fetch(A.urls.status + '?ref=' + encodeURIComponent(cur.ref), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) { if (j.status === 'paid') { clearInterval(timer); reveal(j.token); } }); }, 8000);
  }
  function setup(j) {
    cur = j; $$('.amt').forEach(function (e) { e.textContent = j.amount_text; }); $('#pay-ref').textContent = j.ref;
    var b = A.bank || {}; $('#b-bank').textContent = b.bank || 'Not set yet'; $('#b-name').textContent = b.name || 'Not set yet'; $('#b-num').textContent = b.number || 'Not set yet'; $('#b-ref').textContent = j.ref;
    $('#bank-form').hidden = false; $('#b-wait').hidden = true; $('#ussd-note').textContent = A.ussd_note || '';
    $('#ussd-ps').hidden = !A.paystack; $('#ussd-fw').hidden = !A.flw; tab('bank'); msg('');
  }
  function begin(form) {
    var err = $('#start-err'), btn = $('button[type=submit]', form); err.hidden = true; btn.disabled = true;
    var fd = new FormData(form); fd.append('csrf', A.csrf);
    fetch(A.urls.start, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
      btn.disabled = false;
      if (!j.ok) { err.textContent = j.error || 'Something went wrong.'; err.hidden = false; return; }
      if (j.free) { reveal(j.token); return; }
      setup(j); openModal();
    }).catch(function () { btn.disabled = false; err.textContent = 'Network problem. Please try again.'; err.hidden = false; });
  }
  document.addEventListener('submit', function (e) { if (e.target.id === 'start-form') { e.preventDefault(); begin(e.target); } });
  if (modal) {
    $('.pay-x').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
    $$('.pay-tabs button').forEach(function (b) { b.addEventListener('click', function () { tab(b.dataset.tab); }); });
    $('#ps-btn').addEventListener('click', function () { paystack(['card', 'bank', 'qr', 'mobile_money', 'bank_transfer']); });
    $('#fw-btn').addEventListener('click', function () { flutterwave('card,banktransfer,ussd'); });
    $('#ussd-ps').addEventListener('click', function () { paystack(['ussd']); });
    $('#ussd-fw').addEventListener('click', function () { flutterwave('ussd'); });
    $('#b-done').addEventListener('click', function () {
      var s = $('#b-sender').value.trim(); if (!s) { msg('Please enter the name on the account you paid from.', true); return; }
      post(A.urls.bank, { ref: cur.ref, sender: s }).then(function (j) { if (j.ok) { $('#bank-form').hidden = true; $('#b-wait').hidden = false; msg(''); poll(); } else msg(j.error || 'Something went wrong.', true); });
    });
  }

  /* signature pad and passport preview on the application form */
  function initForm() {
    var c = $('#sigpad'); if (!c) return;
    var ctx = c.getContext('2d'), drawing = false, dirty = false; ctx.lineWidth = 2.6; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#102A43';
    function pos(e) { var r = c.getBoundingClientRect(), t = e.touches ? e.touches[0] : e; return { x: (t.clientX - r.left) * c.width / r.width, y: (t.clientY - r.top) * c.height / r.height }; }
    function down(e) { drawing = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
    function move(e) { if (!drawing) return; var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); dirty = true; e.preventDefault(); }
    function up() { drawing = false; }
    c.addEventListener('mousedown', down); c.addEventListener('mousemove', move); window.addEventListener('mouseup', up);
    c.addEventListener('touchstart', down, { passive: false }); c.addEventListener('touchmove', move, { passive: false }); c.addEventListener('touchend', up);
    $('#sig-clear').addEventListener('click', function () { ctx.clearRect(0, 0, c.width, c.height); dirty = false; });
    $('#app-form').addEventListener('submit', function (e) { if (!dirty) { e.preventDefault(); alert('Please sign in the signature box.'); return; } $('#sigdata').value = c.toDataURL('image/png'); });
    var ph = $('#passport'), pv = $('#pp-prev');
    ph.addEventListener('change', function () {
      var f = ph.files[0]; if (!f) return; if (f.size > 2 * 1024 * 1024) { alert('The photo must be 2 MB or smaller.'); ph.value = ''; return; }
      var r = new FileReader(); r.onload = function (ev) { pv.src = ev.target.result; pv.hidden = false; }; r.readAsDataURL(f);
    });
  }
  initForm();
})();
