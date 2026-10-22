// ==========================================================
//  TECH McRAE — EMPLOYEE PORTAL
//  Reads server config from window.PORTAL (set in the blade)
// ==========================================================
(function () {
  const CFG = window.PORTAL || {};
  const TZ  = 'Asia/Jakarta';

  // ----------------------------------------------------------
  //  Helpers
  // ----------------------------------------------------------
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  function wibParts() {
    const f = new Intl.DateTimeFormat('en-GB', {
      timeZone: TZ, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    }).formatToParts(new Date());
    const get = t => f.find(p => p.type === t)?.value || '00';
    return { h: get('hour'), m: get('minute'), s: get('second') };
  }
  function wibDateStr() {
    return new Intl.DateTimeFormat('en-GB', {
      timeZone: TZ, weekday: 'long', day: 'numeric', month: 'short', year: 'numeric'
    }).format(new Date());
  }
  function fmtHMS(ms) {
    if (ms < 0) ms = 0;
    const t = Math.floor(ms / 1000);
    const h = Math.floor(t / 3600);
    const m = Math.floor((t % 3600) / 60);
    const s = t % 60;
    const p = n => String(n).padStart(2, '0');
    return `${p(h)}:${p(m)}:${p(s)}`;
  }

  // ----------------------------------------------------------
  //  Live header date + big clock
  // ----------------------------------------------------------
  function tickClock() {
    const { h, m } = wibParts();
    const big = $('#bigClock');
    if (big) big.firstChild.nodeValue = `${h}:${m}`;
    const dt = $('#headerDate');
    if (dt) dt.textContent = wibDateStr();
    const dtm = $('#headerDateMirror');
    if (dtm) dtm.textContent = wibDateStr();
    const ci = $('#ciNow');
    if (ci) ci.textContent = `${h}:${m}`;
  }

  // ----------------------------------------------------------
  //  Worked timer (only when clocked-in) + expected-out tick
  // ----------------------------------------------------------
  function tickWorked() {
    if (CFG.state !== 'clocked_in' || !CFG.checkInEpochMs) return;
    const workedEl = $('#workedValue');
    if (workedEl) workedEl.textContent = fmtHMS(Date.now() - CFG.checkInEpochMs);

    // expected-out checkmark vs cross
    const eo = $('#expectedIcon');
    if (eo && CFG.expectedEpochMs) {
      const reached = Date.now() >= CFG.expectedEpochMs;
      eo.textContent = reached ? 'check' : 'close';
      eo.className = 'material-symbols-rounded ' + (reached ? 'eo-ok' : 'eo-no');
    }
  }

  setInterval(() => { tickClock(); tickWorked(); }, 1000);
  tickClock(); tickWorked();

  // ----------------------------------------------------------
  //  Generic modal open / close
  // ----------------------------------------------------------
  function openModal(id) { const el = $('#' + id); if (el) el.classList.add('open'); }
  function closeModal(el) { el.classList.remove('open'); }

  $$('[data-open-modal]').forEach(btn =>
    btn.addEventListener('click', () => openModal(btn.dataset.openModal)));

  $$('[data-close-modal]').forEach(btn =>
    btn.addEventListener('click', () => closeModal(btn.closest('.modal-overlay'))));

  $$('.modal-overlay').forEach(ov =>
    ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov); }));

  // ----------------------------------------------------------
  //  CLOCK-IN modal — WFO / WFH selection + live banner clock
  // ----------------------------------------------------------
  const ciTypeInput = $('#ciType');
  $$('#clockInModal .cm-choice').forEach(choice => {
    choice.addEventListener('click', () => {
      $$('#clockInModal .cm-choice').forEach(c => c.classList.remove('selected'));
      choice.classList.add('selected');
      if (ciTypeInput) ciTypeInput.value = choice.dataset.type;
    });
  });

  // ----------------------------------------------------------
  //  CLOCK-OUT modal — live "now" + worked today
  // ----------------------------------------------------------
  function tickClockOutModal() {
    const modal = $('#clockOutModal');
    if (!modal || !modal.classList.contains('open')) return;
    const { h, m } = wibParts();
    const nowEl = $('#coNow');
    if (nowEl) nowEl.textContent = `${h}:${m}`;
    const nowEl2 = $('#coNow2');
    if (nowEl2) nowEl2.textContent = `${h}:${m}`;
    const coWorked = $('#coWorked');
    if (coWorked && CFG.checkInEpochMs) coWorked.textContent = fmtHMS(Date.now() - CFG.checkInEpochMs);
  }
  setInterval(tickClockOutModal, 1000);

  // ----------------------------------------------------------
  //  REQUEST-LEAVE modal — type select (opacity), live day count
  // ----------------------------------------------------------
  const rlTypeInput = $('#rlTypeId');
  const rlFrom = $('#rlFrom');
  const rlTo   = $('#rlTo');
  const rlDays = $('#rlDays');
  const rlConfirm = $('#rlConfirm');
  const rlError = $('#rlError');

  $$('#requestLeaveModal .rl-bal').forEach(card => {
    card.addEventListener('click', () => {
      $$('#requestLeaveModal .rl-bal').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      if (rlTypeInput) rlTypeInput.value = card.dataset.typeId;
    });
  });

  function computeDays() {
    if (!rlFrom || !rlTo || !rlDays) return;
    const a = rlFrom.value ? new Date(rlFrom.value) : null;
    const b = rlTo.value ? new Date(rlTo.value) : null;
    let d = 0;
    if (a && b) {
      const diff = Math.round((b - a) / 86400000) + 1;
      d = diff > 0 ? diff : 0;
    }
    rlDays.textContent = d || '0';
    if (rlError) rlError.textContent = (a && b && b < a) ? 'End date must be after start date.' : '';
  }
  [rlFrom, rlTo].forEach(el => el && el.addEventListener('change', computeDays));
  computeDays();

  if ($('#requestLeaveForm')) {
    $('#requestLeaveForm').addEventListener('submit', e => {
      if (!rlTypeInput.value) { e.preventDefault(); rlError.textContent = 'Please select a leave type.'; }
      else if (!rlFrom.value || !rlTo.value) { e.preventDefault(); rlError.textContent = 'Please pick both dates.'; }
      else if (new Date(rlTo.value) < new Date(rlFrom.value)) { e.preventDefault(); rlError.textContent = 'End date must be after start date.'; }
    });
  }

  // ----------------------------------------------------------
  //  Auto-open success modal after a redirect
  // ----------------------------------------------------------
  if (CFG.openModal) {
    const map = {
      success_clock_in: 'successClockInModal',
      success_clock_out: 'successClockOutModal',
      success_leave: 'successLeaveModal'
    };
    const id = map[CFG.openModal];
    if (id) openModal(id);
  }

  // ----------------------------------------------------------
  //  Recent-leave "View" buttons (Detail Modal)
  // ----------------------------------------------------------
  const typeColors = {
      'Annual':    'var(--blue)',
      'Sick':      'var(--red)',
      'Maternity': 'var(--pink)',
      'Paternity': 'var(--yellow)',
      'Emergency': 'var(--red)',
      'Unpaid':    'var(--darkgreen)'
  };
  
  $$('.leave-view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = btn.dataset;
      
      $('#lrReqId').textContent = d.id;
      
      const hStatus = $('#lrHeaderStatus');
      hStatus.textContent = d.status;
      hStatus.className = 'status-pill status-' + d.status.toLowerCase();
      
      // Formatting the range nicely
      $('#lrDateRange').innerHTML = d.range.replace(' - ', ' –<br>');
      $('#lrDays').textContent = d.days;
      $('#lrReason').textContent = '“' + (d.reason || 'No reason provided') + '”';
      
      const typeEl = $('#lrType');
      typeEl.textContent = d.type;
      typeEl.style.color = typeColors[d.type] || 'var(--darkblue)';
      
      const statusEl = $('#lrStatusText');
      statusEl.textContent = d.status;
      
      if (d.status.toLowerCase() === 'approved' || d.status.toLowerCase() === 'accepted') {
          statusEl.style.color = 'var(--darkgreen)';
      } else if (d.status.toLowerCase() === 'rejected') {
          statusEl.style.color = 'var(--red)';
      } else if (d.status.toLowerCase() === 'pending') {
          statusEl.style.color = 'var(--yellow)';
      } else {
          statusEl.style.color = 'var(--darkblue)';
      }
      
      $('#lrStatusText').textContent = d.status;
      
      // Only show the "Withdraw" button if it's still Pending!
      const actions = $('#lrDetailActions');
      const notesBlock = $('#lrNotesBlock');
      const notesText = $('#lrNotes');

      if (d.status.toLowerCase() === 'pending') {
          actions.style.display = 'flex';
          $('#lrWithdrawForm').action = '/portal/leave/' + d.dbid + '/withdraw';
          notesBlock.style.display = 'none'; // Hide notes when pending
      } else {
          actions.style.display = 'none'; // Hide withdraw when decided
          notesBlock.style.display = 'block'; // Show notes
          notesText.textContent = d.note ? '“' + d.note + '”' : 'No notes added.';
      }
      
      openModal('leaveDetailModal');
    });
  });

  // ----------------------------------------------------------
  //  Table sorting (both portal tables)
  // ----------------------------------------------------------
  $$('.att-table th, .leave-table th').forEach((header, index) => {
    const icon = header.querySelector('.sort-icon');
    if (!icon) return;
    let asc = true;
    header.style.cursor = 'pointer';
    const colIndex = Array.from(header.parentNode.children).indexOf(header);
    header.addEventListener('click', () => {
      const table = header.closest('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.querySelector('[colspan]'));
      rows.sort((a, b) => {
        const x = a.children[colIndex]?.textContent.trim() || '';
        const y = b.children[colIndex]?.textContent.trim() || '';
        const num = !isNaN(parseFloat(x)) && !isNaN(parseFloat(y));
        return num ? (asc ? x - y : y - x)
                   : (asc ? x.localeCompare(y, undefined, { numeric: true }) : y.localeCompare(x, undefined, { numeric: true }));
      });
      rows.forEach(r => tbody.appendChild(r));
      asc = !asc;
      icon.textContent = asc ? '▲' : '▼';
    });
  });
})();
