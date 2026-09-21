// Do not edit - this file is generated/managed by the template

// Shared client core (#32): one source for cross-module helpers that were
// copy-pasted per file. index.js loads first (see config/assets.php), so this
// is defined before any app/*.js uses it. escapeHtml escapes the full
// HTML-significant set (&, <, >, ") — fixing the screens.js call sites that
// escaped only '<'.
window.gcCore = window.gcCore || {};
window.gcCore.escapeHtml = function (s) {
    return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
};

// Session CSRF token, from the meta tag the layout emits. Single source for
// the wrapped fetch (below) and any raw XHR (#33) so the selector/lookup isn't
// copy-pasted per call site.
window.gcCore.csrfToken = function () {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? (m.getAttribute('content') || '') : '';
};

// Raw XHR POST that attaches the CSRF token + XHR marker the way
// AuthyMiddleware::checkCsrf expects — for the rare state-changing request that
// can't go through the wrapped window.fetch (file uploads need XHR for upload
// progress). opts: { onProgress(pct,ev), onLoad(xhr), onError(xhr) }. Returns
// the xhr. Centralized (#33) so a second such site doesn't re-roll the token
// plumbing.
window.gcCore.xhrPost = function (url, body, opts) {
    opts = opts || {};
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    var tok = window.gcCore.csrfToken();
    if (tok) { xhr.setRequestHeader('X-Csrf-Token', tok); }
    if (opts.onProgress && xhr.upload) {
        xhr.upload.onprogress = function (ev) {
            if (ev.lengthComputable) { opts.onProgress(Math.round(ev.loaded / ev.total * 100), ev); }
        };
    }
    if (opts.onLoad) { xhr.onload = function () { opts.onLoad(xhr); }; }
    if (opts.onError) { xhr.onerror = function () { opts.onError(xhr); }; }
    xhr.send(body);
    return xhr;
};

// gcFormat (#23 S5): display formatting driven by a container data-attr
// (data-gc-fmt-date='Col1,Col2'), the declarative replacement for the per-table
// formatter onReadyJs (format_date_columns). gcFormatDate is idempotent (returns
// null on already-formatted text), so bindWithin coexists with the legacy
// onReadyJs during the transition off the <script> re-exec arm.
if (!window.gcFormatDate) {
    window.gcFormatDate = function (s) {
        var t = String(s == null ? '' : s).trim();
        // Unix epoch seconds (only opted-in columns ever reach here, so a
        // 10-digit number is unambiguous — e.g. authy_log.timestamp,
        // stripe *_(unix) columns).
        if (/^\d{10}$/.test(t)) {
            var de = new Date(+t * 1000);
            if (isNaN(de.getTime())) { return null; }
            return de.toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        }
        var m = t.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::\d{2})?)?$/);
        if (!m) { return null; }
        var d = new Date(+m[1], +m[2] - 1, +m[3], m[4] ? +m[4] : 0, m[5] ? +m[5] : 0);
        if (isNaN(d.getTime())) { return null; }
        var opts = { year: 'numeric', month: 'short', day: 'numeric' };
        if (m[4]) { opts.hour = 'numeric'; opts.minute = '2-digit'; }
        return d.toLocaleString(undefined, opts);
    };
}
if (!window.gcFormatPhone) {
    window.gcFormatPhone = function (s) {
        var d = String(s == null ? '' : s).replace(/\D+/g, '');
        if (!d) { return ''; }
        if (d.length === 11) { return d.slice(0, 1) + ' ' + d.slice(1, 4) + ' ' + d.slice(4, 7) + ' ' + d.slice(7); }
        if (d.length === 10) { return d.slice(0, 3) + ' ' + d.slice(3, 6) + ' ' + d.slice(6); }
        if (d.length === 7) { return d.slice(0, 3) + ' ' + d.slice(3); }
        return d.replace(/(\d)(?=(\d{3})+$)/g, '$1 ');
    };
}
window.gcFormat = {
    _containers: function (scope, attr) {
        var list = Array.prototype.slice.call(scope.querySelectorAll('[' + attr + ']'));
        if (scope.getAttribute && scope.getAttribute(attr) != null) { list.push(scope); }
        return list;
    },
    _cols: function (ctnr, attr) {
        return (ctnr.getAttribute(attr) || '').split(',').map(function (c) { return c.trim(); }).filter(Boolean);
    },
    bindWithin: function (scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        var self = this;
        // Dates: format [c='Col'] cells; gcFormatDate returns null on non-dates.
        self._containers(scope, 'data-gc-fmt-date').forEach(function (ctnr) {
            self._cols(ctnr, 'data-gc-fmt-date').forEach(function (col) {
                Array.prototype.forEach.call(ctnr.querySelectorAll("[c='" + col + "']"), function (td) {
                    var target = td.querySelector('span') || td;
                    var f = window.gcFormatDate(target.textContent);
                    if (f) { target.textContent = f; }
                });
            });
        });
        // Phones: format [c='Col'] cells + input[name='Col'] values (blur-reformat).
        self._containers(scope, 'data-gc-fmt-phone').forEach(function (ctnr) {
            self._cols(ctnr, 'data-gc-fmt-phone').forEach(function (col) {
                Array.prototype.forEach.call(ctnr.querySelectorAll("[c='" + col + "']"), function (td) {
                    var target = td.querySelector('span') || td;
                    var t = (target.textContent || '').trim();
                    if (t) { target.textContent = window.gcFormatPhone(t); }
                });
                Array.prototype.forEach.call(ctnr.querySelectorAll("input[name='" + col + "']"), function (i) {
                    if (i.__gcPhoneBound) { return; }
                    i.__gcPhoneBound = 1;
                    if (i.value) { i.value = window.gcFormatPhone(i.value); }
                    i.addEventListener('blur', function () { i.value = window.gcFormatPhone(i.value); });
                });
            });
        });
    }
};
(function () {
    function fmtGo() { try { window.gcFormat.bindWithin(document); } catch (e) {} }
    if (document.readyState != 'loading') { fmtGo(); }
    else { document.addEventListener('DOMContentLoaded', fmtGo); }
})();

// #23 S5: Add-button — one delegated click handler keyed on data-gc-add (present
// only on regenerated projects). Un-regenerated projects' Add buttons lack it, so
// this no-ops on them and their inline onReadyJs handles the click — no double-open.
document.addEventListener('click', function (e) {
    var btn = (e.target && e.target.closest) ? e.target.closest('[data-gc-add]') : null;
    if (!btn || !window.gcScreens) { return; }
    e.preventDefault();
    gcScreens.openEdit(btn.getAttribute('data-gc-add'), btn.getAttribute('i') || '', { ip: btn.getAttribute('data-gc-ip') || '' });
});

// #23 S5: label-link — delegated handler keyed on data-gc-link. Clicking a
// [data-gc-link] label navigates (or window.opens) to the linked FK record's edit
// form, using the value of the field named by its label_lien attr. Keyed on
// data-gc-link so un-regenerated projects keep their onReadyJs (no double-nav).
document.addEventListener('click', function (e) {
    var lnk = (e.target && e.target.closest) ? e.target.closest('[data-gc-link]') : null;
    if (!lnk) { return; }
    e.preventDefault();
    var fld = document.getElementById(lnk.getAttribute('label_lien'));
    if (!fld || !fld.value) { return; }
    var url = (typeof _SITE_URL !== 'undefined' ? _SITE_URL : '') + lnk.getAttribute('data-gc-link') + '/edit/' + fld.value;
    if (lnk.getAttribute('data-gc-link-type') === 'new') { window.open(url); }
    else { document.location = url; }
});

// #23 S5: clone (CloneEntry) — delegated click on [data-gc-clone]. POST
// <model>/clone/<rid> (rid read from the nearest [rid] ancestor — the list tr or the
// form container), then either redirect to the new record's edit form (mode='edit') or
// re-run the list search / reload (mode='list'). Keyed on data-gc-clone so un-regenerated
// projects keep their inline onReadyJs (which keys on [j=cloneEntry] without the attr) —
// no double-clone.
document.addEventListener('click', function (e) {
    var ce = (e.target && e.target.closest) ? e.target.closest('[data-gc-clone]') : null;
    if (!ce) { return; }
    e.preventDefault();
    var model = ce.getAttribute('data-gc-clone');
    var mode = ce.getAttribute('data-gc-clone-mode') || 'list';
    var src = ce.closest('[rid]');
    var rid = src ? src.getAttribute('rid') : '';
    var site = (typeof _SITE_URL !== 'undefined' ? _SITE_URL : '');
    fetch(site + model + '/clone/' + encodeURIComponent(rid), {
        method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) { return r.text(); }).then(function (data) {
        if (mode === 'edit') {
            window.location.href = site + model + '/edit/' + data;
        } else {
            var sb = document.querySelector('.msSearchCtnr #ms' + model + 'Bt');
            if (sb) { sb.click(); } else { window.location.reload(); }
        }
    });
});

// #23 S5: refresh-on-child — declarative, keyed on data-gc-rfcc. When a child
// list reports a change (gc:list-refreshed), re-fetch the host form's field
// values and update the configured columns. The map is a flat alphanumeric attr
// "Child=ColA,ColB;Child2=ColC" (PhpNames). Keyed on data-gc-rfcc so
// un-regenerated projects keep their guarded onReadyJs (no double-fetch).
(function () {
    function parseRfcc(s) {
        var map = {};
        (s || '').split(';').forEach(function (g) {
            if (!g) { return; }
            var i = g.indexOf('=');
            if (i < 0) { return; }
            var model = g.slice(0, i);
            var cols = g.slice(i + 1).split(',').filter(Boolean);
            if (model) { map[model] = cols; }
        });
        return map;
    }
    document.addEventListener('gc:list-refreshed', function (e) {
        var m = e.detail && e.detail.model;
        if (!m) { return; }
        Array.prototype.forEach.call(document.querySelectorAll('form[data-gc-rfcc]'), function (form) {
            var map = parseRfcc(form.getAttribute('data-gc-rfcc'));
            if (!map[m]) { return; }
            var host = form.getAttribute('data-gc-rfcc-host') || '';
            var pkName = form.getAttribute('data-gc-rfcc-pk') || '';
            var pk = pkName ? form.querySelector('input[name="' + pkName + '"]') : null;
            if (!pk || !pk.value) { return; }
            var p = new URLSearchParams();
            p.append('a', 'fieldvals');
            p.append('i', pk.value);
            fetch((typeof _SITE_URL !== 'undefined' ? _SITE_URL : '') + host + '/fieldvals', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: p.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res || res.status !== 'success' || !res.data) { return; }
                map[m].forEach(function (col) {
                    if (!(col in res.data)) { return; }
                    var v = res.data[col];
                    var el = form.querySelector('[id="' + col + '"]');
                    if (el && (el.tagName === 'INPUT' || el.tagName === 'SELECT')) { el.value = v; }
                    var lbl = form.querySelector('[id="' + col + '_label"]');
                    if (lbl) { lbl.textContent = v; }
                });
            }).catch(function () { /* stale until reload on network error */ });
        });
    });
})();


// #23 S5: quick-add — the modal/create-flow routine, relocated from the
// emitter's onReadyJs. gcQuickAdd.bindWithin reads the declarative
// data-gc-quickadd form attr (a JSON array of per-FK-column configs) and
// calls window.gcQuickAddInit for each. gcQuickAddInit guards each select on
// data-gc-qa-bound, so re-binding (push + DOMContentLoaded) is idempotent.
// Keyed on data-gc-quickadd so un-regenerated projects keep their onReadyJs.
(function(){
if (typeof window.gcQuickAddInit === 'undefined') {
  window.gcQuickAddInit = function(cfg){
    // The list page's search form reuses the same column id (e.g. #IdClient in
    // the collapsed filter AND in the edit screen), and the edit sheet renders
    // on top of the list. Scope to the host edit form first so the link lands
    // on the visible form, not the hidden search widget.
    var hostForm = document.getElementById('form' + cfg.host);
    var sel = (hostForm && hostForm.querySelector(cfg.sel)) || document.querySelector(cfg.sel);
    if (!sel || sel.getAttribute('data-gc-qa-bound')) { return; }
    sel.setAttribute('data-gc-qa-bound', '1');
    var postUrl = _SITE_URL + cfg.host + '/quickadd';
    var link = document.createElement('a');
    link.setAttribute('href', '#');
    link.className = 'gc-quickadd-link';
    link.style.cssText = 'margin-left:6px;font-size:18px;color:#1a73e8;text-decoration:none;cursor:pointer;vertical-align:middle;display:inline-flex;align-items:center;padding:2px 4px;';
    link.innerHTML = '<i class="ri-add-circle-line" aria-hidden="true"></i>';
    link.setAttribute('aria-label', cfg.linkLabel);
    link.title = cfg.linkLabel;
    // Determine the right anchor so the icon appears after the whole visible
    // field widget, not buried inside it or before sibling elements.
    // 1. gcSelectBox: hidden input lives inside label.select-label — put the
    //    icon after the label so clicking it cannot accidentally toggle the dropdown.
    // 2. autocomplete: sel is the hidden id input; the visible text input has id
    //    cfg.key + 'Autoc' and sits as a sibling BEFORE sel — anchor after it.
    // 3. fallback: anchor after sel itself.
    //
    // The edit-drawer uses flex-direction:column on .form-row, so inserting the
    // icon as a bare sibling after the widget stacks it BELOW the widget on its
    // own line. Fix: wrap the widget + icon together in an inline-flex row so
    // they always sit side-by-side regardless of the parent's flex-direction.
    var widgetLabel = sel.closest && sel.closest('label.select-label');
    var anchor;
    if (widgetLabel) {
      anchor = widgetLabel;
    } else {
      var acScope = (hostForm || sel.parentNode || document);
      var acText = acScope.querySelector(cfg.sel + 'Autoc');
      anchor = acText || sel;
    }
    if (anchor.parentNode) {
      var wrapper = document.createElement('div');
      wrapper.className = 'gc-quickadd-row';
      wrapper.style.cssText = 'display:flex;align-items:center;gap:6px;';
      anchor.parentNode.insertBefore(wrapper, anchor);
      wrapper.appendChild(anchor);
      wrapper.appendChild(link);
    }
    // depends_on gating: when the same column's autocomplete depends on host
    // form sources, quick-add must wait for them too — a row created before
    // the sources are set could not even be picked (the dependent gets
    // cleared/constrained by those sources). Disable the link until every
    // source has a value; re-evaluate on each source's change.
    var deps = (cfg.dependsOn && cfg.dependsOn.length) ? cfg.dependsOn : null;
    function depHasAll() {
      if (!deps) { return true; }
      for (var di = 0; di < deps.length; di++) {
        var srcDep = document.querySelector(deps[di].sel);
        if (!srcDep || String(srcDep.value) === '') { return false; }
      }
      return true;
    }
    function depSyncLink() {
      if (!deps) { return; }
      if (depHasAll()) {
        link.removeAttribute('aria-disabled');
        link.style.opacity = '';
        link.style.pointerEvents = '';
        link.title = cfg.linkLabel;
      } else {
        link.setAttribute('aria-disabled', 'true');
        link.style.opacity = '.45';
        link.style.pointerEvents = 'none';
        link.title = cfg.dependsOnMsg || '';
      }
    }
    if (deps) {
      depSyncLink();
      deps.forEach(function (d) {
        var srcDep = document.querySelector(d.sel);
        if (srcDep) { srcDep.addEventListener('change', depSyncLink); }
      });
    }
    // The modal is built node by node (createElement + textContent), never by
    // concatenating HTML: f.label, f.options and cfg.title reach us through
    // gettext on column descriptions, so a translated .po string was an
    // injection vector straight into innerHTML. Same reason res.message is
    // rendered through qaStatus() below — a {Model}ServiceWrapper::quickAdd
    // override chooses that text server-side.
    var QA_FIELD_STYLE = 'width:100%;border:1px solid #d9dce0;border-radius:6px;'
      + 'padding:8px;background:#fff;box-sizing:border-box;';
    var qaFields = document.createDocumentFragment();
    for (var i = 0; i < cfg.fields.length; i++) {
      var f = cfg.fields[i];
      var inId = 'gcqa_' + cfg.key + '_' + f.col;
      var row = document.createElement('div');
      row.className = (f.type === 'textarea') ? 'form-row stacked' : 'form-row';
      var lblEl = document.createElement('span');
      lblEl.className = 'lbl';
      lblEl.textContent = (f.label == null) ? '' : String(f.label);
      row.appendChild(lblEl);
      var inEl;
      if (f.type === 'textarea') {
        inEl = document.createElement('textarea');
        inEl.style.cssText = 'text-align:left;' + QA_FIELD_STYLE + 'min-height:60px;';
      } else if (f.type === 'select') {
        inEl = document.createElement('select');
        inEl.style.cssText = QA_FIELD_STYLE;
        inEl.appendChild(document.createElement('option')); // blank, value=''
        (f.options || []).forEach(function (o) {
          var opt = document.createElement('option');
          opt.value = String(o);
          opt.textContent = String(o);
          inEl.appendChild(opt);
        });
      } else {
        inEl = document.createElement('input');
        inEl.type = (f.type === 'date' ? 'date' : (f.type === 'number' ? 'number' : 'text'));
      }
      inEl.id = inId;
      inEl.setAttribute('data-col', f.col);
      row.appendChild(inEl);
      qaFields.appendChild(row);
    }

    var modal = document.createElement('div');
    modal.className = 'scan-modal gc-quickadd-modal';
    modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;'
      + 'background:rgba(0,0,0,0.7);z-index:10000;overflow-y:auto;display:none;';
    var qaContent = document.createElement('div');
    qaContent.className = 'scan-modal-content proto-form';
    qaContent.style.cssText = 'max-width:460px;position:relative;margin:40px auto;'
      + 'border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;';
    var qaHead = document.createElement('div');
    qaHead.style.cssText = 'display:flex;justify-content:space-between;align-items:center;'
      + 'padding:14px 18px;background:#fff;border-bottom:1px solid #e6e8eb;';
    var qaTitle = document.createElement('strong');
    qaTitle.style.cssText = 'font-size:1.05em;';
    qaTitle.textContent = (cfg.title == null) ? '' : String(cfg.title);
    var qaClose = document.createElement('button');
    qaClose.type = 'button';
    qaClose.className = 'gc-qa-close';
    qaClose.style.cssText = 'background:none;border:none;font-size:22px;cursor:pointer;'
      + 'line-height:1;color:#555;';
    qaClose.textContent = '\u00D7';
    qaHead.appendChild(qaTitle);
    qaHead.appendChild(qaClose);
    var qaCard = document.createElement('div');
    qaCard.className = 'form-card';
    qaCard.appendChild(qaFields);
    var qaFoot = document.createElement('div');
    qaFoot.style.cssText = 'display:flex;gap:10px;justify-content:flex-end;padding:14px 18px;';
    var qaSave = document.createElement('a');
    qaSave.href = 'javascript:;';
    qaSave.className = 'gc-qa-save';
    qaSave.style.cssText = 'background:#28a745;color:#fff;border:none;border-radius:8px;'
      + 'padding:10px 22px;font-weight:600;font-size:0.95em;cursor:pointer;text-decoration:none;';
    qaSave.textContent = (cfg.saveLabel == null) ? '' : String(cfg.saveLabel);
    var qaCancel = document.createElement('a');
    qaCancel.href = 'javascript:;';
    qaCancel.className = 'gc-qa-cancel';
    qaCancel.style.cssText = 'background:#dc3545;color:#fff;border:none;border-radius:8px;'
      + 'padding:10px 22px;font-weight:600;font-size:0.95em;cursor:pointer;text-decoration:none;';
    qaCancel.textContent = (cfg.cancelLabel == null) ? '' : String(cfg.cancelLabel);
    qaFoot.appendChild(qaSave);
    qaFoot.appendChild(qaCancel);
    var qaStatusEl = document.createElement('div');
    qaStatusEl.className = 'gc-qa-status';
    qaStatusEl.style.cssText = 'text-align:center;min-height:18px;padding:0 18px 14px;';
    qaContent.appendChild(qaHead);
    qaContent.appendChild(qaCard);
    qaContent.appendChild(qaFoot);
    qaContent.appendChild(qaStatusEl);
    modal.appendChild(qaContent);
    document.body.appendChild(modal);
    // The modal lives on document.body, outside the pushed screen that owns
    // the select — so it does NOT die with that screen. Remember it here and
    // gcQuickAdd.destroyWithin() (called from screens.js pop(), next to
    // gcEditor.destroyWithin) takes it down; without that every drawer push
    // leaked one modal carrying duplicate gcqa_<key>_<col> ids.
    sel.__gcQaModal = modal;

    // Status line, always as text. color defaults to the error red.
    function qaStatus(msg, color) {
      qaStatusEl.textContent = '';
      if (msg == null || msg === '') { return; }
      var sp = document.createElement('span');
      sp.style.color = color || 'red';
      sp.textContent = String(msg);
      qaStatusEl.appendChild(sp);
    }
    function openModal() {
      Array.prototype.forEach.call(modal.querySelectorAll('input,textarea'), function(el){ el.value = ''; });
      qaStatus('');
      var sv = modal.querySelector('.gc-qa-save'); if (sv) { sv.style.pointerEvents = 'auto'; }
      modal.style.display = 'block';
      var first = modal.querySelector('input,textarea'); if (first) { first.focus(); }
    }
    function closeModal() { modal.style.display = 'none'; }
    // Defense in depth: even if the pointer-events gate is bypassed
    // (keyboard activation, stale style), never open while sources are empty.
    link.addEventListener('click', function (e) { e.preventDefault(); if (!depHasAll()) { return; } openModal(); });
    Array.prototype.forEach.call(modal.querySelectorAll('.gc-qa-close, .gc-qa-cancel'), function(el){ el.addEventListener('click', closeModal); });
    modal.addEventListener('click', function(e){ if (e.target === modal) { closeModal(); } });
    var saveBtn = modal.querySelector('.gc-qa-save');
    if (saveBtn) {
      saveBtn.addEventListener('click', function(){
        var fields = {}, ok = false;
        Array.prototype.forEach.call(modal.querySelectorAll('[data-col]'), function(el){
          var c = el.getAttribute('data-col'), v = el.value;
          fields[c] = v;
          if (String(v).trim() !== '') { ok = true; }
        });
        if (!ok) { qaStatus(cfg.requiredMsg); return; }
        saveBtn.style.pointerEvents = 'none';
        qaStatus('...', '#888');
        var params = new URLSearchParams();
        params.append('a', 'quickadd');
        params.append('fkt', cfg.table);
        for (var k in fields) { if (Object.prototype.hasOwnProperty.call(fields, k)) { params.append('fields[' + k + ']', fields[k]); } }
        if (cfg.fromForm) {
          for (var fk in cfg.fromForm) {
            if (!Object.prototype.hasOwnProperty.call(cfg.fromForm, fk)) { continue; }
            var srcEl = document.querySelector('#form' + cfg.host + ' #' + cfg.fromForm[fk]);
            if (srcEl && srcEl.value !== '') { params.append('fields[' + fk + ']', srcEl.value); }
          }
        }
        // label[] keeps it an array server-side; a stringified array would make
        // quickAdd() fall back to ALL posted fields (including from_form ids).
        (cfg.label || []).forEach(function(lc){ params.append('label[]', lc); });
        fetch(postUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: params.toString() })
          .then(function(r){ return r.text().then(function(t){ var d = null; try { d = JSON.parse(t); } catch (e) {} return { ok: r.ok, data: d }; }); })
          .then(function(resp){
            var res = resp.data;
            if (resp.ok && res && res.status === 'success' && res.id) {
              var newLabel = res.label || fields[cfg.fields[0].col];
              if (sel.tagName === 'SELECT') {
                var opt = document.createElement('option');
                opt.value = res.id;
                opt.textContent = newLabel;
                sel.appendChild(opt);
                sel.value = res.id;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
                try { if (sel.classList.contains('js-select-label') && window.gcSelectBox) { gcSelectBox.bind(sel); } } catch (e) {}
              } else if (window.gcSelectBox && gcSelectBox.addOption && sel.closest && sel.closest('label.select-label')) {
                // gcSelectBox widget (hidden .selextbox-input inside
                // label.select-label): append + select through the widget's
                // own primitive. addOption() routes through pick(), which
                // commits the hidden input AND fires its change (bubbles), so
                // no extra dispatch here — a second change would make the
                // widget's external-change path re-sync redundantly.
                gcSelectBox.addOption(sel, res.id, newLabel);
              } else {
                // set_autocomplete column: sel is the hidden id input with a
                // sibling <key>Autoc text input carrying the visible label.
                // Scope the lookup to the host form — the underlying list
                // page's search form reuses the same Autoc input id.
                var acScope = document.getElementById('form' + cfg.host) || sel.parentNode || document;
                var acText = acScope.querySelector(cfg.sel + 'Autoc');
                sel.value = res.id;
                if (acText) { acText.value = newLabel; }
                sel.dispatchEvent(new Event('change', { bubbles: true }));
              }
              closeModal();
              if (typeof sw_message === 'function') { sw_message(cfg.savedMsg); }
            } else {
              qaStatus((res && res.message) || cfg.failMsg);
              saveBtn.style.pointerEvents = 'auto';
            }
          })
          .catch(function(){
            qaStatus(cfg.failMsg);
            saveBtn.style.pointerEvents = 'auto';
          });
      });
    }
  };
}
})();
window.gcQuickAdd = {
    // Take down the body-level modals owned by the selects inside `scope`
    // (a screen about to be popped). Mirrors gcEditor.destroyWithin, and
    // screens.js pop() calls it from the same place. Clearing the bound flag
    // means re-opening the screen re-binds cleanly instead of no-oping on a
    // stale guard whose modal is gone.
    destroyWithin: function (scope) {
        if (!scope || !scope.querySelectorAll) { return; }
        Array.prototype.forEach.call(scope.querySelectorAll('[data-gc-qa-bound]'), function (sel) {
            var m = sel.__gcQaModal;
            if (m && m.parentNode) { m.parentNode.removeChild(m); }
            sel.__gcQaModal = null;
            sel.removeAttribute('data-gc-qa-bound');
        });
    },
    bindWithin: function (scope) {
        scope = scope || document;
        if (!scope.querySelectorAll || typeof window.gcQuickAddInit !== 'function') { return; }
        Array.prototype.forEach.call(scope.querySelectorAll('form[data-gc-quickadd]'), function (form) {
            var raw = form.getAttribute('data-gc-quickadd');
            if (!raw) { return; }
            var configs;
            try { configs = JSON.parse(raw); } catch (e) { return; }
            if (!Array.isArray(configs)) { return; }
            configs.forEach(function (cfg) { try { window.gcQuickAddInit(cfg); } catch (e) { /* one config failing must not break the rest */ } });
        });
    }
};
(function () {
    function qaGo() { try { window.gcQuickAdd.bindWithin(document); } catch (e) {} }
    if (document.readyState != 'loading') { qaGo(); }
    else { document.addEventListener('DOMContentLoaded', qaGo); }
})();

// #23 S5: child-bulk — mass-action toggle, shift-select range, selected-count,
// and NtN junction save-on-check (plus the dormant del-all / bulk-open handlers),
// relocated off the child-list onReadyJs. gcChildBulk.bindWithin reads the child
// list <form>'s declarative data-gc-cbulk JSON config + its data-ip (parent pk) and
// wires each control scoped to that form. Idempotent: every control is guarded on
// the same element flag the retired inline block used (__maChildBound /
// __shiftChildBound / __ntnBound / __delAllChildBound / __buChildBound), so binding
// again on a re-render is a no-op. Keyed on form[data-gc-cbulk] so un-regenerated
// child lists (no attr) keep their inline onReadyJs and this no-ops on them.
window.gcChildBulk = {
    bindWithin: function (scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        var site = (typeof _SITE_URL !== 'undefined' ? _SITE_URL : '');
        Array.prototype.forEach.call(scope.querySelectorAll('[data-gc-cbulk]'), function (form) {
            var cfg;
            try { cfg = JSON.parse(form.getAttribute('data-gc-cbulk')); } catch (e) { return; }
            if (!cfg || !cfg.model) { return; }
            var model = cfg.model;
            var pc = form.getAttribute('data-gc-cbpc');
            if (pc == null) { pc = ''; }
            var ip = form.getAttribute('data-ip');
            if (ip == null) { ip = ''; }
            var boxes = function () { return form.querySelectorAll('[j=check_multi_' + model + ']'); };
            var picked = function () { return form.querySelectorAll('[j=check_multi_' + model + ']:checked'); };
            var collect = function () {
                var i = [];
                Array.prototype.forEach.call(picked(), function (c) {
                    if (c.name) { i.push(encodeURIComponent(c.name) + '=' + encodeURIComponent(c.value)); }
                });
                return i.join('&');
            };

            // mass-action toggle: flip every row checkbox via a synthetic click so
            // each box's own change handler (NtN save) still fires.
            var ma = form.querySelector('#mass-action-' + model);
            if (ma && !ma.__maChildBound) {
                ma.__maChildBound = 1;
                ma.addEventListener('click', function (e) {
                    e.preventDefault();
                    Array.prototype.forEach.call(boxes(), function (c) { c.click(); });
                });
            }

            // shift-select range + live selected-count.
            var chk = Array.prototype.slice.call(form.querySelectorAll('.actionrow input[type=checkbox]'));
            var lastChecked;
            chk.forEach(function (cb) {
                if (cb.__shiftChildBound) { return; }
                cb.__shiftChildBound = 1;
                cb.addEventListener('click', function (e) {
                    if (!lastChecked) { lastChecked = this; return; }
                    if (e.ctrlKey) {
                        var start = chk.indexOf(this);
                        var end = chk.indexOf(lastChecked);
                        chk.slice(Math.min(start, end), Math.max(start, end) + 1).forEach(function (x) { x.checked = lastChecked.checked; });
                    }
                    lastChecked = this;
                    var sc = form.querySelector('.pagination-wrapper .selectedCount');
                    if (sc) { sc.textContent = '(' + picked().length + ' selected)'; }
                });
            });

            // NtN junction save-on-check: POST each toggle, roll back + toast on failure.
            if (cfg.ntn) {
                // Match the form-save / delete feedback design: prefer the gcScreens
                // proto-toast pill (what a drawer Save shows) over the legacy full-width
                // sw_message bar, so a cross-ref toggle looks consistent with the rest of
                // the app. Falls back to sw_message when the push client isn't present.
                var ntnToast = function (m, err) {
                    if (window.gcScreens && gcScreens.toast) { gcScreens.toast(m); }
                    else { sw_message(m, err); }
                };
                Array.prototype.forEach.call(boxes(), function (cb) {
                    if (cb.__ntnBound) { return; }
                    cb.__ntnBound = 1;
                    cb.addEventListener('change', function () {
                        var c = this;
                        c.style.cursor = 'wait';
                        fetch(site + cfg.virtual + '/NtNsave' + model, {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: 'i=' + encodeURIComponent(c.value) + '&ui=' + encodeURIComponent(pc)
                        }).then(function (r) { return r.text(); }).then(function (data) {
                            var resp = (typeof data === 'string') ? JSON.parse(data) : data;
                            var msg = (resp && resp.messages && resp.messages[0]) ? resp.messages[0] : '';
                            if (resp && resp.status === 'success') { ntnToast(msg || cfg.savedMsg); }
                            else { c.checked = !c.checked; ntnToast(msg || cfg.failMsg, true); }
                            c.style.cursor = '';
                        }).catch(function () { c.checked = !c.checked; ntnToast(cfg.failMsg, true); c.style.cursor = ''; });
                    });
                });
            }

            // del-all (dormant: del_all_child is disabled in the emitter).
            if (cfg.del) {
                var da = form.querySelector('#del_all');
                if (da && !da.__delAllChildBound) {
                    da.__delAllChildBound = 1;
                    da.addEventListener('click', function () {
                        if (picked().length > 0) {
                            fetch(site + 'mod/act/' + cfg.table + 'Act.php', {
                                method: 'POST', credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
                                body: 'a=del_all_' + model + '&ip=' + encodeURIComponent(ip) + '&i=' + encodeURIComponent(collect()) + '&ui=' + encodeURIComponent(pc)
                            }).then(function (r) { return r.text(); }).then(function () {
                                var cg = document.querySelector('#pannelSForm [j=conglet_' + cfg.parent + '][p=' + model + ']');
                                if (cg) { cg.click(); }
                            });
                        } else { alertb(cfg.attentionMsg, cfg.checkOneMsg); }
                    });
                }
            }

            // bulk-open (dormant fleet-wide: add_child_bulk is commented in every schema).
            if (cfg.bu) {
                var bf = form.querySelector('#bulkUpdateForm');
                if (bf && !bf.__buChildBound) {
                    bf.__buChildBound = 1;
                    bf.addEventListener('click', function () {
                        if (picked().length > 0) {
                            if (window.gcScreens) {
                                gcScreens.openPanel({
                                    url: site + cfg.table + '/bulkUpdateForm' + model,
                                    method: 'POST',
                                    data: { ip: ip, i: collect(), ui: pc },
                                    title: cfg.buTitle,
                                    refreshOnClose: true
                                });
                            }
                        } else { alertb(cfg.attentionMsg, cfg.checkOneMsg); }
                        return false;
                    });
                }
            }
        });
    }
};
(function () {
    function cbGo() { try { window.gcChildBulk.bindWithin(document); } catch (e) {} }
    if (document.readyState != 'loading') { cbGo(); }
    else { document.addEventListener('DOMContentLoaded', cbGo); }
})();

// #23 S5: list-level bulk — the parent-list mass-action toggle, shift-select range,
// selected-count and bulk-open button, relocated off the list onReadyJs. gcListBulk
// reads the list <form>'s data-gc-lbulk config but uses DOCUMENT-WIDE element lookups:
// list.js relocateSwHeaderActions() moves #mass-action-<model> / #bulkUpdateForm out of
// the form into the topbar, so form-scoping would miss them (the retired inline block
// used document-wide selectors for the same reason). Idempotent via the same per-element
// guards (__maAllBound / __shiftBound / __buFormBound). Keyed on form[data-gc-lbulk] so
// un-regenerated lists keep their inline onReadyJs and this no-ops on them. Re-bound on
// gc:list-refreshed (list.js fetchList swap) since a refresh re-renders the controls.
window.gcListBulk = {
    bindWithin: function () {
        if (!document.querySelector) { return; }
        var site = (typeof _SITE_URL !== 'undefined' ? _SITE_URL : '');
        Array.prototype.forEach.call(document.querySelectorAll('[data-gc-lbulk]'), function (form) {
            var cfg;
            try { cfg = JSON.parse(form.getAttribute('data-gc-lbulk')); } catch (e) { return; }
            if (!cfg || !cfg.model) { return; }
            var model = cfg.model;
            var boxes = function () { return document.querySelectorAll('[j=check_multi_' + model + ']'); };
            var picked = function () { return document.querySelectorAll('[j=check_multi_' + model + ']:checked'); };

            // mass-action toggle: set every row checkbox to the toggle's state (direct,
            // no synthetic click — list rows have no per-row change handler).
            var ma = document.getElementById('mass-action-' + model);
            if (ma && !ma.__maAllBound) {
                ma.__maAllBound = 1;
                ma.addEventListener('click', function () {
                    var v = this.checked;
                    Array.prototype.forEach.call(boxes(), function (c) { c.checked = v; });
                });
            }

            // shift-select range + live selected-count.
            var chk = Array.prototype.slice.call(document.querySelectorAll('.actionrow input[type=checkbox]'));
            var lastChecked;
            chk.forEach(function (cb) {
                if (cb.__shiftBound) { return; }
                cb.__shiftBound = 1;
                cb.addEventListener('click', function (e) {
                    if (!lastChecked) { lastChecked = this; return; }
                    if (e.ctrlKey) {
                        var start = chk.indexOf(this);
                        var end = chk.indexOf(lastChecked);
                        chk.slice(Math.min(start, end), Math.max(start, end) + 1).forEach(function (x) { x.checked = lastChecked.checked; });
                    }
                    lastChecked = this;
                    var sc = document.querySelector('.pagination-wrapper .selectedCount');
                    if (sc) { sc.textContent = '(' + picked().length + ' selected)'; }
                });
            });

            // bulk-open: open the bulk-edit panel for the checked rows.
            if (cfg.bu) {
                var bf = document.getElementById('bulkUpdateForm');
                if (bf && !bf.__buFormBound) {
                    bf.__buFormBound = 1;
                    bf.addEventListener('click', function () {
                        if (picked().length > 0) {
                            if (window.gcScreens) {
                                var i = [];
                                Array.prototype.forEach.call(picked(), function (c) { if (c.name) { i.push(encodeURIComponent(c.name) + '=' + encodeURIComponent(c.value)); } });
                                gcScreens.openPanel({
                                    url: site + model + '/bulkUpdateForm',
                                    method: 'POST',
                                    data: { i: i.join('&') },
                                    title: cfg.buTitle,
                                    refreshOnClose: true
                                });
                            }
                        } else { alertb(cfg.attentionMsg, cfg.checkOneMsg); }
                        return false;
                    });
                }
            }
        });
    }
};
(function () {
    function lbGo() { try { window.gcListBulk.bindWithin(); } catch (e) {} }
    if (document.readyState != 'loading') { lbGo(); }
    else { document.addEventListener('DOMContentLoaded', lbGo); }
    // A list refresh (list.js fetchList) re-renders the controls + checkboxes; re-bind.
    document.addEventListener('gc:list-refreshed', lbGo);
})();

// #23 S5: bulk-edit panel save — the bulkUpdateForm panel (opened by list/child
// bulk-open) carried its save handler, the [s=d] change->ck marking and the add-child-
// popup hide as inline onReadyJs. gcBulkSave reads the panel form's data-gc-busave config
// {model, saveId, url, json, ui, dialog} and wires them scoped to the form. Idempotent via
// the same __buBound / __buChgBound guards. Keyed on form[data-gc-busave] so un-regenerated
// panels keep their inline onReadyJs and this no-ops. Bound from screens.js push() (the
// panel is a pushed screen). gcEditor.syncWithin replaces the old inline mceSaveJs.
window.gcBulkSave = {
    bindWithin: function (scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        var site = (typeof _SITE_URL !== 'undefined' ? _SITE_URL : '');
        Array.prototype.forEach.call(scope.querySelectorAll('[data-gc-busave]'), function (form) {
            var cfg;
            try { cfg = JSON.parse(form.getAttribute('data-gc-busave')); } catch (e) { return; }
            if (!cfg || !cfg.saveId) { return; }
            // Add-child popups stay hidden in the bulk panel.
            Array.prototype.forEach.call(form.querySelectorAll('[data-group=addChildPopup]'), function (e) { e.style.display = 'none'; });
            // Editing any [s=d] field marks its companion ck_ checkbox so the row's
            // column is included in the bulk update.
            Array.prototype.forEach.call(form.querySelectorAll('[s=d]'), function (fld) {
                if (fld.getAttribute('j') === 'ck_bulkUP') { return; }
                if (fld.__buChgBound) { return; }
                fld.__buChgBound = 1;
                fld.addEventListener('change', function () {
                    var ck = document.getElementById('ck_' + fld.id);
                    if (ck) { ck.setAttribute('checked', 'checked'); ck.checked = true; }
                });
            });
            var sv = form.querySelector('#' + cfg.saveId);
            if (sv && !sv.__buBound) {
                sv.__buBound = 1;
                sv.addEventListener('click', function () {
                    if (window.gcEditor) { try { gcEditor.syncWithin(form); } catch (e) {} }
                    document.body.style.cursor = 'progress';
                    sv.style.cursor = 'progress';
                    var p = new URLSearchParams();
                    Array.prototype.forEach.call(form.querySelectorAll('[s=d]'), function (f2) {
                        if (f2.disabled || !f2.name) { return; }
                        var tt = (f2.type || '').toLowerCase();
                        if ((tt === 'checkbox' || tt === 'radio') && !f2.checked) { return; }
                        if (f2.tagName === 'SELECT' && f2.multiple) {
                            Array.prototype.forEach.call(f2.selectedOptions, function (o) { p.append(f2.name, o.value); });
                            return;
                        }
                        p.append(f2.name, f2.value);
                    });
                    var bd = new URLSearchParams();
                    bd.set('d', p.toString());
                    bd.set('ui', cfg.ui || '');
                    bd.set('dialog', cfg.dialog || '');
                    var reset = function () { document.body.style.cursor = 'default'; sv.style.cursor = 'default'; };
                    fetch(site + cfg.url, {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
                        body: bd.toString()
                    }).then(function (r) { return cfg.json ? r.json() : r.text(); }).then(function (data) {
                        var ok = cfg.json ? (data && data['ok'] == 'ok') : true;
                        if (ok && window.gcScreens) { gcScreens.popAfterSave(null); }
                        // A refused / rolled-back bulk update says why; the
                        // panel used to just stay open in silence.
                        else if (!ok && cfg.json && data && data.message) { alertb('Not saved', String(data.message)); }
                        reset();
                    }).catch(reset);
                });
            }
        });
    }
};

var isSelected_IarcAutoc = 0;
var isSelected_IarcAutoccShow = 0;
var CCautocSuccessIarc = '';
var CCautocChangeIarc = '';
var CCautocSearchIarc = '';
var CCautocFocusIarc = '';

var default_width = 0;
var default_height = 0;
var fullscreen_timer;
var fullscreen_click = false;

var act_confirm = '';
var alert_close = '';
var has_search = true;
var act_negatif = '';

// Shared modal-dialog accessibility wiring for the gc-confirm shells (confirm,
// alert, re-auth). The jQuery-UI dialogs these replaced provided role/aria-modal,
// a focus trap, Escape-to-dismiss and focus restore for free; the vanilla
// rebuild dropped all of that. gcDialogA11y restores it: marks the box as a
// modal dialog, labels it from the title/message elements, moves focus inside,
// traps Tab within the box, maps Escape to the cancel action, and restores focus
// to the previously-focused element on teardown.
//
//   opts: { role, titleEl, msgEl, initialFocus, onCancel }
//   returns a teardown() to call when the dialog closes.
window.__gcDlgSeq = window.__gcDlgSeq || 0;
window.gcDialogA11y = function (dim, box, opts) {
    opts = opts || {};
    var prevFocus = document.activeElement;
    box.setAttribute('role', opts.role || 'alertdialog');
    box.setAttribute('aria-modal', 'true');
    if (!box.hasAttribute('tabindex')) { box.setAttribute('tabindex', '-1'); }
    if (opts.titleEl) {
        if (!opts.titleEl.id) { opts.titleEl.id = 'gc-dlg-t-' + (++window.__gcDlgSeq); }
        box.setAttribute('aria-labelledby', opts.titleEl.id);
    }
    if (opts.msgEl) {
        if (!opts.msgEl.id) { opts.msgEl.id = 'gc-dlg-m-' + (++window.__gcDlgSeq); }
        box.setAttribute('aria-describedby', opts.msgEl.id);
    }

    function focusables() {
        return Array.prototype.slice.call(box.querySelectorAll(
            'button, [href], input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) { return !el.disabled && el.offsetParent !== null; });
    }

    requestAnimationFrame(function () {
        var initial = opts.initialFocus || focusables()[0] || box;
        try { initial.focus(); } catch (e) { /* detached */ }
    });

    function onKey(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if (typeof opts.onCancel === 'function') { e.preventDefault(); opts.onCancel(); }
            return;
        }
        if (e.key === 'Tab' || e.keyCode === 9) {
            var f = focusables();
            if (!f.length) { e.preventDefault(); box.focus(); return; }
            var first = f[0], last = f[f.length - 1];
            if (!box.contains(document.activeElement)) {
                e.preventDefault(); first.focus();
            } else if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        }
    }
    document.addEventListener('keydown', onKey, true);

    return function teardown() {
        document.removeEventListener('keydown', onKey, true);
        if (prevFocus && typeof prevFocus.focus === 'function') {
            try { prevFocus.focus(); } catch (e) { /* gone from DOM */ }
        }
    };
};

window.confirm = function (obj, act_t) {
    // Designed confirm via gcScreens (was jQuery-UI #confirmDialog). Runs the
    // act_confirm callback on accept, act_negatif on cancel. Function-callback
    // only — every emitter callsite passes a closure (S7; the legacy eval-string
    // branch was removed).
    //
    // This is NOT the native synchronous confirm: it always returns undefined,
    // so a boolean-style `if (confirm(...))` guard silently never proceeds (the
    // dashboard Restart button shipped four broken iterations on exactly that).
    // Fail loudly on native-style calls instead of swallowing them.
    if (typeof act_t !== 'function') {
        throw new TypeError('window.confirm is the async gc modal: use confirm(message, onAccept) or gcScreens.confirm(message, opts).then(...)');
    }
    act_confirm = act_t;
    if (window.gcScreens && gcScreens.confirm) {
        gcScreens.confirm(obj, { confirmLabel: 'Yes', danger: false }).then(function (ok) {
            if (ok) {
                if (typeof act_confirm === 'function') { act_confirm(); }
            } else {
                if (typeof act_negatif === 'function') { act_negatif(); }
            }
        });
    }
};

window.onload = function () {
    if (location.hash) { window.scrollTo(0, 0); }
};
function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}

(function () {
    function go() {
        checkAlive();
        window.addEventListener('focus', function () { checkAlive(); });
        window.addEventListener('blur', function () { checkAlive(); });
    }
    if (document.readyState != 'loading') { go(); }
    else { document.addEventListener('DOMContentLoaded', go); }
})();

const SessionManager = {
    isShowingDialog: false,
    pendingQueue: [],
    dim: null,

    // A queued item is a replay descriptor produced by the fetch wrapper:
    // { input, init, resolve, reject } — the original fetch() call's args plus
    // the original promise's settlers, so after re-auth we re-run the wrapped
    // fetch and settle the caller's promise with the replayed Response.
    handleExpired: function(descriptor) {
        this.pendingQueue.push(descriptor);
        if (!this.isShowingDialog) {
            this.showDialog();
        }
    },

    // Bespoke re-auth modal on the gc-confirm shell (was jQuery-UI
    // #sessionExpiredDialog). Builds its own user/pass form with the same field
    // IDs the reauth POST reads, so no jQuery-UI and no emitted dialog markup.
    buildModal: function() {
        var self = this;
        var dim = document.createElement('div');
        dim.className = 'gc-confirm-dim';
        var box = document.createElement('div');
        box.className = 'gc-confirm-box';
        // Unique IDs + element refs: the legacy BuilderLayout markup still
        // carries #session_expired_* fields until they are removed, so never
        // look these up by a shared global id.
        box.innerHTML =
            '<div class="gc-confirm-title">Session expired</div>' +
            '<div class="gc-confirm-msg">Please sign in again to continue.</div>' +
            '<div class="hide gc-reauth-error"></div>' +
            '<input type="text" placeholder="Username" class="gc-reauth-input" autocomplete="username">' +
            '<input type="password" placeholder="Password" class="gc-reauth-input" autocomplete="current-password">' +
            '<div class="gc-confirm-btns">' +
            '<button type="button" class="gc-confirm-btn gc-reauth-reload">Reload page</button>' +
            '<button type="button" class="gc-confirm-btn primary gc-reauth-signin">Sign In</button>' +
            '</div>';
        dim.appendChild(box);
        document.body.appendChild(dim);
        this.errEl = box.querySelector('.gc-reauth-error');
        this.userEl = box.querySelector('input[type="text"]');
        this.passEl = box.querySelector('input[type="password"]');
        this.signinBtn = box.querySelector('.gc-reauth-signin');
        box.querySelector('.gc-reauth-reload').addEventListener('click', function() { location.reload(); });
        this.signinBtn.addEventListener('click', function() { self.doReauth(); });
        this.passEl.addEventListener('keypress', function(e) { if (e.keyCode === 13) { self.doReauth(); } });
        this.userEl.addEventListener('keypress', function(e) { if (e.keyCode === 13) { self.passEl.focus(); } });
        requestAnimationFrame(function() { dim.classList.add('show'); });
        // Accessibility: modal dialog role (it's a sign-in form), focus trap and
        // focus restore. No Escape-to-cancel: re-auth is mandatory, the escape
        // hatch is the explicit "Reload page" button. Teardown runs on success.
        this.teardownA11y = (typeof window.gcDialogA11y === 'function')
            ? window.gcDialogA11y(dim, box, {
                role: 'dialog',
                titleEl: box.querySelector('.gc-confirm-title'),
                msgEl: box.querySelector('.gc-confirm-msg'),
                initialFocus: this.userEl
            })
            : function () {};
        return dim;
    },

    showDialog: function() {
        this.isShowingDialog = true;
        if (!this.dim) { this.dim = this.buildModal(); }
        if (this.errEl) { this.errEl.className = 'hide gc-reauth-error'; this.errEl.innerHTML = ''; }
        if (this.userEl) { this.userEl.value = ''; }
        if (this.passEl) { this.passEl.value = ''; }
        var self = this;
        setTimeout(function() { if (self.userEl) { self.userEl.focus(); } }, 200);
    },

    doReauth: function() {
        var self = this;
        if (this.signinBtn) { this.signinBtn.disabled = true; this.signinBtn.textContent = '...'; }
        var body = new URLSearchParams();
        body.set('u', this.userEl ? this.userEl.value : '');
        body.set('p', this.passEl ? this.passEl.value : '');
        body.set('csrf', window.gcCore.csrfToken()); // #33: shared source
        var resetBtn = function () {
            if (self.signinBtn) { self.signinBtn.disabled = false; self.signinBtn.textContent = 'Sign In'; }
        };
        // Use the native fetch directly (SessionManager._nativeFetch) so the
        // re-auth POST is never itself intercepted by the 401 wrapper below.
        SessionManager._nativeFetch(_SITE_URL + 'Authy/auth', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: body
        }).then(function (resp) {
            return resp.json().then(function (data) {
                if (resp.ok && data && data.status === 'success') {
                    self.onReauthSuccess();
                } else {
                    self.onReauthFailed((data && data.messages) || 'Login failed');
                    resetBtn();
                }
            });
        }).catch(function () {
            self.onReauthFailed('Login failed');
            resetBtn();
        });
    },

    onReauthSuccess: function() {
        this.isShowingDialog = false;
        if (typeof this.teardownA11y === 'function') { this.teardownA11y(); this.teardownA11y = null; }
        if (this.dim) {
            var d = this.dim;
            this.dim = null;
            d.classList.remove('show');
            setTimeout(function() { if (d.parentNode) { d.parentNode.removeChild(d); } }, 180);
        }
        var queue = this.pendingQueue.splice(0);
        queue.forEach(function(descriptor) {
            // Re-run the original request through the wrapped fetch and pipe its
            // outcome into the caller's still-pending promise.
            SessionManager.wrappedFetch(descriptor.input, descriptor.init)
                .then(descriptor.resolve, descriptor.reject);
        });
    },

    onReauthFailed: function(msg) {
        // Login errors are plain text; render via textContent (not innerHTML) so
        // a server message can never inject markup. messages may be a string or
        // an array (the Authy/auth response shape).
        if (this.errEl) {
            this.errEl.textContent = Array.isArray(msg) ? msg.join(' ') : (msg == null ? '' : String(msg));
            this.errEl.className = 'gc-reauth-error';
        }
    }
};

// Global fetch wrapper restoring the 401 re-auth behavior that previously lived
// in $.ajaxPrefilter. The emitter+runtime have moved to fetch(), so this is what
// gives them global session-expiry handling: on a 401 (except the auth endpoint
// itself, or callers that opt out via init.noSessionInterceptor) the original
// request is queued, the re-auth modal is shown, and on success the request is
// replayed and the caller's original promise resolves with the replayed Response.
SessionManager._nativeFetch = window.fetch ? window.fetch.bind(window) : null;

SessionManager.wrappedFetch = function (input, init) {
    var native = SessionManager._nativeFetch;
    init = init || {};
    // Opt-out flag (parity with the old noSessionInterceptor jQuery option) and
    // the auth endpoint itself must never be intercepted/queued.
    var optOut = !!init.noSessionInterceptor;
    var url = (typeof input === 'string') ? input : (input && input.url) || '';
    // Authentication endpoints must never be intercepted by the 401 re-auth
    // wrapper: a 401 from them is a failed *login*, not an expired session, and
    // they run on the login page where there is no session to re-auth. Covers
    // Google sign-in (Authy/google) — a 401 there (e.g. route not yet in the
    // privilege exclude list) would otherwise pop a spurious "Session expired"
    // dialog instead of letting the login handler show its own message.
    var isAuthEndpoint = /\/Authy\/(auth|google|login|logout|reset|register|forgotten|confirm)\b/.test(url);

    // noSessionInterceptor is our own flag, not a valid RequestInit key — strip a
    // shallow copy so it is never forwarded to native fetch.
    var passInit = init;
    if (optOut) {
        passInit = {};
        for (var k in init) { if (k !== 'noSessionInterceptor') { passInit[k] = init[k]; } }
    }

    // Attach the session CSRF token to every state-changing request so the
    // server-side gate (AuthyMiddleware::checkCsrf) can verify it — but ONLY for
    // same-origin requests, so the token can never leak to a third-party host if
    // app code ever fetch()es one with a mutating verb.
    var method = ((passInit && passInit.method) || (input && input.method) || 'GET').toUpperCase();
    var sameOrigin = true;
    try { sameOrigin = new URL(url, location.href).origin === location.origin; } catch (e) { sameOrigin = true; }
    if (method !== 'GET' && method !== 'HEAD' && sameOrigin) {
        var csrfTok = window.gcCore.csrfToken(); // #33: shared source
        if (csrfTok) {
            if (passInit === init) {
                passInit = {};
                for (var ck in init) { passInit[ck] = init[ck]; }
            }
            var hdrs = passInit.headers;
            if (typeof Headers !== 'undefined' && hdrs instanceof Headers) {
                if (!hdrs.has('X-Csrf-Token')) { hdrs.set('X-Csrf-Token', csrfTok); }
            } else {
                var merged = {};
                var hasTok = false;
                for (var hk in (hdrs || {})) {
                    merged[hk] = hdrs[hk];
                    if (hk.toLowerCase() === 'x-csrf-token') { hasTok = true; }
                }
                if (!hasTok) { merged['X-Csrf-Token'] = csrfTok; }
                passInit.headers = merged;
            }
        }
    }

    var p = native(input, passInit);
    if (optOut || isAuthEndpoint) { return p; }

    return p.then(function (resp) {
        if (resp && resp.status === 401) {
            // Suspend: hand the caller a fresh promise and queue a replay
            // descriptor settled in onReauthSuccess.
            return new Promise(function (resolve, reject) {
                SessionManager.handleExpired({ input: input, init: init, resolve: resolve, reject: reject });
            });
        }
        return resp;
    });
};

if (SessionManager._nativeFetch) {
    window.fetch = function (input, init) {
        return SessionManager.wrappedFetch(input, init);
    };
}

let checkedAlive = false;
checkAlive = () => {

  // Never probe session liveness on the login page: there is no session yet, so
  // GuiManager would 401 and pop a spurious "Session expired" dialog. This is
  // exactly what the Google sign-in picker triggers — opening/closing the GIS
  // account chooser fires window blur/focus (both wired to checkAlive), so on
  // the login page a Google sign-in would always raise the dialog.
  if (document.getElementById('login-form')) { return; }

  if (!checkedAlive) {
      var aliveBody = new URLSearchParams();
      aliveBody.set('a', 'alive');
      // noSessionInterceptor: this request does its own 401 handling (show the
      // dialog directly) so it must bypass the global wrapper's queue/replay.
      window.fetch(_SITE_URL + 'GuiManager', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
          body: aliveBody,
          noSessionInterceptor: true
      }).then(function (resp) {
          if (resp.status === 401) {
              SessionManager.showDialog();
              return;
          }
          return resp.json().then(function (data) {
              if (!data || data['status'] != 'success') {
                  SessionManager.showDialog();
              }
          });
      }).catch(function () {
          location.reload();
      });
  }
  checkedAlive = true;
  setTimeout(function(){ checkedAlive=false; }, 10000);
}

document.addEventListener('keypress', function (e) {
    /* Backspace */
    var ae = document.activeElement;
    var tag = ae ? (ae.tagName || '').toLowerCase() : '';
    if (e.keyCode === 8 && tag !== 'input' && tag !== 'textarea') {
        e.preventDefault();
    }
});

(function () {
  function go() {

    // Helpers: jQuery .show()/.hide() parity via display toggling. Animations
    // (slideDown/slideUp/animate) are reduced to instant display/scroll changes;
    // the visual slide is dropped (noted in the conversion report).
    function showEl(el) { if (el) { el.style.display = ''; } }
    function hideEl(el) { if (el) { el.style.display = 'none'; } }
    function isHidden(el) {
        if (!el) { return true; }
        return window.getComputedStyle(el).display === 'none';
    }

    document.addEventListener('keydown', function (event) {
        if (document.querySelector('.can-save')) {
            if (event.ctrlKey || event.metaKey) {
                if (String.fromCharCode(event.which).toLowerCase() == 's') {
                    var saveBtn = document.querySelector('.can-save');
                    if (saveBtn) { saveBtn.click(); }
                    event.preventDefault();
                }
            }
        }
    });

    if (navigator.platform == 'iPad' || navigator.platform == 'iPhone' || navigator.platform == 'iPod') {
        document.querySelectorAll('.sw-header').forEach(function (el) { el.style.position = 'absolute'; });
        document.querySelectorAll('.content-wrapper').forEach(function (el) { el.style.top = '0px'; });
        document.querySelectorAll('.custom-controls-add').forEach(function (el) { el.style.display = 'inline-block'; el.style.float = 'none'; });
        document.querySelectorAll('.custom-controls').forEach(function (el) { el.style.display = 'inline-block'; el.style.float = 'none'; });
    }

    document.querySelectorAll('.center-panel').forEach(function (panel) {
        panel.addEventListener('touchmove', function () {
            document.querySelectorAll('.sw-header').forEach(function (el) { el.style.position = 'fixed'; });
        });
    });

    document.querySelectorAll('.scroll-top').forEach(function (el) {
        el.addEventListener('click', function () {
            document.querySelectorAll('.content-wrapper').forEach(function (cw) { cw.scrollTop = 0; });
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.trigger-menu')) {
            var body = document.getElementById('body');
            if (body) { body.classList.toggle('toggle-left-panel'); }
            e.preventDefault();
        }
    });

    document.querySelectorAll('.msSearchCtnr').forEach(function (el) {
        var entity = el.getAttribute('data-entity');
        if (entity && localStorage.getItem('searchOpen_' + entity) === 'open') {
            showEl(el);
        } else {
            hideEl(el);
        }
    });

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.trigger-search');
        if (!trigger) { return; }
        var header = trigger.closest('.search-entity-header');
        var container = header ? header.nextElementSibling : null;
        if (container && !container.classList.contains('msSearchCtnr')) { container = null; }
        if (!container) { e.preventDefault(); return; }
        var entity = container.getAttribute('data-entity');
        if (isHidden(container)) {
            showEl(container); // was slideDown(500) — animation dropped
            if (entity) { localStorage.setItem('searchOpen_' + entity, 'open'); }
        } else {
            hideEl(container);
            if (entity) { localStorage.setItem('searchOpen_' + entity, 'closed'); }
        }
        e.preventDefault();
    });

    /*  overflow div content*/
    window.addEventListener('resize', function () {
        var w = window.innerWidth;
        if (w > 583) {
            document.querySelectorAll('.ac-header .right').forEach(function (el) { el.removeAttribute('style'); });
        }
        setDivContent();
    });
    setDivContent();



    /* admin */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.sw-header .controls-button')) {
            document.querySelectorAll('.custom-controls').forEach(function (el) { el.classList.toggle('toggle-sw-options'); });
            e.preventDefault();
        }
    });


    wrap_autoc('Iarc', 'Authy', 'Iarc', '', '', '', 'std', { term: ['Username', 'Email'] }, '', 'select-box-');

    // Anchor on the panel form id, not its container: the impersonation box
    // moved from .left-panel-wrapper into the app drawer (#drImpersonatePanel).
    var iarcInput = document.querySelector('#select-box-Authy #Iarc');
    if (iarcInput) {
        iarcInput.addEventListener('change', function () {
            var authyBox = document.getElementById('select-box-Authy');
            var iarcCsrfInput = document.getElementById('IarcCsrf');
            if (authyBox && authyBox.getAttribute('data-authy') != this.value && this.value != '') {
                var iarcCsrf = authyBox.getAttribute('data-csrf') || (iarcCsrfInput ? iarcCsrfInput.value : '') || '';
                document.location = _SITE_URL + 'admin?iarc=' + encodeURIComponent(this.value) + '&iarc_csrf=' + encodeURIComponent(iarcCsrf);
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.scroll-top')) { return; }
        var in_popup = false;

        document.querySelectorAll('.ui-dialog').forEach(function (dlg) {
            if (!isHidden(dlg)) {
                in_popup = true;
                var content = dlg.querySelector('.ui-dialog-content');
                if (content) { content.scrollTo({ top: 0, behavior: 'smooth' }); }
            }
        });

        if (in_popup == false) {
            document.querySelectorAll('.content-wrapper').forEach(function (cw) {
                cw.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        e.preventDefault();
    });

    document.querySelectorAll('.left-panel .disconnect').forEach(function (el) {
        el.addEventListener('click', function (e) {
            confirm('Logout?', function () { document.location = _SITE_URL + 'Authy/logout'; });
            e.preventDefault();
        });
    });

    document.addEventListener('change', function (e) {
        if (e.target.closest('.divStdform input, .divStdform select, .divStdform textarea')) {
            document.querySelectorAll('.divtd input[type="button"]').forEach(function (el) { el.classList.add('can-save'); });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.target.closest('.divStdform input, .divStdform textarea')) {
            document.querySelectorAll('.divtd input[type="button"]').forEach(function (el) { el.classList.add('can-save'); });
        }
    });

    document.querySelectorAll('.ac-menu > li').forEach(function (li) {
        li.addEventListener('click', function (e) {
            if (!(e.target.classList && e.target.classList.contains('ac-alert-count'))) {
                var wasActive = li.classList.contains('active');
                document.querySelectorAll('.ac-menu > li').forEach(function (el) { el.classList.remove('active'); });
                if (!wasActive) {
                    document.querySelectorAll('.ac-menu > li ul').forEach(function (el) { hideEl(el); }); // was slideUp()
                }
                li.querySelectorAll('ul.sub-menu').forEach(function (el) { showEl(el); }); // was slideDown()
                li.classList.add('active');
            }
        });
    });

    var activeInMenu = document.querySelector('.ac-menu li .active');
    if (activeInMenu) {
        var parentUl = activeInMenu.closest('ul');
        if (parentUl && parentUl.classList.contains('sub-menu')) {
            showEl(parentUl);
            if (parentUl.parentElement) { parentUl.parentElement.classList.add('active'); }
        }
    }
    var locParts = document.location.href.replace("'._SITE_URL.'", "").split("#");
    if (locParts[1]) {
        var jh = document.querySelector("[jhref='#" + locParts[1] + "']");
        if (jh) {
            var jhParent = jh.parentElement;
            if (jhParent) {
                jhParent.classList.add('active');
                jhParent.querySelectorAll('ul').forEach(function (el) { showEl(el); });
            }
        }
    }
  }
  if (document.readyState != 'loading') { go(); }
  else { document.addEventListener('DOMContentLoaded', go); }
})();

var timerDivContent = 0;
var noPerfectScroll;
var noSetHeight;

function setDivContent() {
    // No-op: both former targets are gone — .ui-tabs-nav (jQuery-UI, removed)
    // and .formContent [j=ogf] (that markup is no longer emitted by the
    // builder). The ixogf GuiManager endpoint still exists server-side but
    // nothing triggers it client-side. Kept as a no-op so the existing
    // call sites stay valid until the Stage-4 DOMReady sweep removes them.
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, dec = (typeof dec_point === 'undefined') ? '.' : dec_point, s = '', toFixedFix = function (n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
    }; s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function alertb(title, msg, close) {
    // Designed alert via gcScreens (was jQuery-UI #alertDialog). title/msg are
    // HTML (upload error lists carry markup); close runs on dismissal.
    alert_close = close || null;
    if (window.gcScreens && gcScreens.alert) {
        gcScreens.alert(title, msg, function () {
            // Function-callback only (S7; the legacy eval-string branch was
            // removed once html_helper's error-field-focus alert_close became a
            // closure).
            if (typeof alert_close === 'function') { alert_close(); }
            alert_close = null;
        });
    }
}

function addslashes(str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function wrap_autoc(name, table, childTable, IdParent, Id, autoCpJsVar, version, paramD, ms, formParentStr, formParentFull) {
    var formParent = 'form' + table;
    if (formParentFull) {
        formParent = formParentFull;
    } else if (formParentStr) {
        formParent = formParentStr + table;
    } else if (ms == 1) {
        formParent = 'formMs' + table;
    }

    var isIarc = (name === 'Iarc');
    var inputSel = '#' + formParent + ' #' + name + 'Autoc';
    var hiddenSel = '#' + formParent + ' #' + name;
    var url = isIarc ? (_SITE_URL + 'Authy/autoc') : (_SITE_URL + table + '/autoc');

    // Contract-agnostic param builder. paramD is the emitted object-literal tail
    // (begins with ',') spliced into the request literal exactly as the legacy
    // jQuery-UI wrap_autoc did, so depends_on/extra_params and `str:request.term`
    // keep resolving at request time. Works for both the set_autocomplete
    // contract (fkt/show/id/filter/str/limit) and the Iarc impersonation search
    // (Username/Email + iarc_csrf).
    function buildData(term) {
        var input = document.querySelector(inputSel);
        var ipVal = (input && input.closest('form')) ? input.closest('form').id : '';
        // Declarative spec (S6): build the autocomplete request from a plain
        // object — NO eval. The emitter (set_autocomplete) ships the spec;
        // depends_on selectors and the term resolve at request time, so cascades
        // and bracketed where[] keys keep working per keystroke. A non-object
        // paramD (should not occur post-regeneration) degrades to an empty spec
        // rather than reintroducing eval.
        var s = (paramD && typeof paramD === 'object') ? paramD : {};
        var dataParam = { maxRows: 12, ip: ipVal, a: 'autoc', t: childTable };
        if (s.fkt != null)    { dataParam.fkt = s.fkt; }
        if (s.show != null)   { dataParam.show = s.show; }
        if (s.id != null)     { dataParam.id = s.id; }
        if (s.filter != null) { dataParam.filter = s.filter; }
        if (s.limit != null)  { dataParam.limit = s.limit; }
        // term → one or more request keys ('str' for set_autocomplete;
        // ['Username','Email'] for the Iarc impersonation search).
        [].concat(s.term || []).forEach(function (k) { dataParam[k] = term; });
        // depends_on: [requestKey, hostFormSelector] pairs — read the live
        // host-form value so dependent autocompletes filter per keystroke.
        // Resolve FORM-SCOPED (the input's own form) so a form-relative '#Field'
        // selector works in both the edit (#form<T>) and search (#formMs<T>) form;
        // fall back to a document lookup for legacy full '#form<T> #Field' specs.
        var whereForm = (input && input.closest) ? input.closest('form') : null;
        (s.where || []).forEach(function (p) {
            var el = (whereForm ? whereForm.querySelector(p[1]) : null) || document.querySelector(p[1]);
            dataParam[p[0]] = el ? el.value : '';
        });
        // static extras (literal defaults + literal extra_params)
        if (s.set) { Object.keys(s.set).forEach(function (k) { dataParam[k] = s.set[k]; }); }
        // navigator.<prop> extras (e.g. lang from navigator.language)
        if (s.nav) {
            Object.keys(s.nav).forEach(function (k) {
                dataParam[k] = (typeof navigator !== 'undefined') ? navigator[s.nav[k]] : '';
            });
        }
        if (isIarc) {
            var authyBox = document.getElementById('select-box-Authy');
            var iarcCsrfInput = document.getElementById('IarcCsrf');
            dataParam.iarc_csrf = (authyBox && authyBox.getAttribute('data-csrf')) || (iarcCsrfInput ? iarcCsrfInput.value : '') || '';
            dataParam.term = term;
        }
        return dataParam;
    }

    if (window.gcAutocomplete) {
        window.gcAutocomplete.bind({
            inputSel: inputSel,
            hiddenSel: hiddenSel,
            url: url,
            minLength: isIarc ? 3 : 2,
            buildData: buildData
        });
    }
    // (Removed the eval(autoCpJsVar) arg-6 branch: arg 6 is always '' and the
    //  AutoCp_ defaults are now baked into the declarative spec's `set`. S6
    //  eval-free.)
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

if (!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, '');
    };
}

function colorField(id, color, containerId) {
    // Vanilla: use [id="..."] attribute selectors so ids containing "[]"
    // (multi-select field names) need no jQuery-style escaping.
    var root = containerId ? document.getElementById(containerId) : document;
    if (!root) { return; }
    var byId = function (theId) { return root.querySelector('[id="' + theId + '"]'); };

    var field = byId(id);
    if (!field) { return; }
    if (field.getAttribute('type') == 'button') {
        return false;
    }

    var obj;
    if (field.getAttribute('type') != 'hidden') {
        obj = field.classList.contains('hide') ? byId(id + 'Text') : field;
    } else if (field.classList.contains('selextbox-input')) {
        /*select*/
        obj = root.querySelector('[data-name="' + id + '"] span');
    } else {
        obj = byId(id + 'Text');
    }
    if (!obj) { return; }

    obj.setAttribute('obc', window.getComputedStyle(obj).backgroundColor);
    obj.style.backgroundColor = color;
}

// $.fn.bindSave removed (jquery core removal, stage 5): the regular edit-screen
// save flow is owned by app/screens.js (delegated .nav-save handler), and panel/
// standalone forms now emit a self-contained vanilla save listener directly from
// runtime BuilderLayout::decoratedForm. Nothing calls $(...).bindSave() anymore.


function sw_message(message, error, id, stick) {
    if (message === true) {
        sw_message_remove(id);
        return;
    }
    stick = !!stick;
    var class_error = (error == true) ? 'error' : 'success';

    var ul = document.querySelector('.sw-message');
    if (!ul) {
        ul = document.createElement('ul');
        ul.className = 'sw-message';
        document.body.appendChild(ul);
    }
    if (!ul.querySelector('li.' + id)) {
        var li = document.createElement('li');
        li.className = 'new ' + id + ' ' + class_error;
        // Status messages are plain text (emitted as sw_message('Saved') etc.,
        // addslashes-escaped server-side); render via textContent so none can
        // inject markup, matching the toast/taginput sinks.
        li.textContent = message;
        ul.appendChild(li);
    }

    setTimeout(function () {
        var container = document.querySelector('.sw-message');
        if (!container) { return; }
        Array.prototype.forEach.call(container.querySelectorAll('li.new'), function (line) {
            line.classList.remove('new');
            if (stick === false) {
                setTimeout(function () {
                    sw_message_remove(id);
                }, 1000);
            }
        });
    }, 10);
}

function sw_message_remove(id) {
    setTimeout(function () {
        Array.prototype.forEach.call(document.querySelectorAll('.sw-message li.' + id), function (li) {
            if (li.parentNode) { li.parentNode.removeChild(li); }
        });
        var ul = document.querySelector('.sw-message');
        if (ul && !ul.querySelector('li')) {
            // Replaces jQuery .slideUp(250): fade out, then remove the empty <ul>.
            ul.style.transition = 'opacity .25s ease';
            ul.style.opacity = '0';
            setTimeout(function () {
                if (ul.parentNode && !ul.querySelector('li')) {
                    ul.parentNode.removeChild(ul);
                }
            }, 250);
        }
    }, 750);
    document.body.style.cursor = 'default';
}

/**
 * Get timezone data (offset and dst)
 *
 *  Inspired by: http://goo.gl/E41sTi
 *
 * @returns {{offset: number, dst: number}}
 */
function getTimeZoneData() {
	var today = new Date();
  	var jan = new Date(today.getFullYear(), 0, 1);
  	var jul = new Date(today.getFullYear(), 6, 1);
  	var dst = today.getTimezoneOffset() < Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
  
  	return {
    	offset: -today.getTimezoneOffset() / 60,
    	dst: +dst
  	};
}

// CSP enforcement (#19): action links render href="#" placeholders (the runtime
// htmlLink() collapses javascript: hrefs, which a strict CSP blocks). Their
// behavior is delegated (j=/class handlers); preventDefault bare-'#' clicks so
// they act as buttons instead of jumping to top. Real fragment links
// (href="#section") are not matched. Registered at load — capture not needed.
document.addEventListener('click', function (e) {
    var t = e.target;
    var a = (t && t.closest) ? t.closest('a[href="#"]') : null;
    if (a) { e.preventDefault(); }
});
