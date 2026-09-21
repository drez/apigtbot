/* GoatCheese custom selectbox — vanilla rewrite (jquery-core removal stage 3).
 *
 * Replaces the ~800-line jQuery `SelectBox` widget with a plain-DOM
 * implementation, preserving the exact markup/CSS/event contract:
 *
 *   <label class="select-label js-select-label [multiple] [disabled]"
 *          data-name="..." [data-child-select="..."]>
 *     <span class="select-label-span [gray] [default]">label</span>
 *     <div class="mobile-header"><span class="select-close-button
 *          js-select-close-button">x</span></div>
 *     <ul class="scrollable select-element ..." data-default-selected="[...]">
 *       <li class="default" data-label=".." data-value="default"><span>Clear</span></li>
 *       <li data-label=".." data-value=".."><strong>..</strong></li>
 *     </ul>
 *     <input type="hidden" class="selextbox-input" s="d" value="">
 *   </label>
 *
 * State classes preserved: label.show / .show-top / .mobile ; span.gray /
 * .default / .selected ; li.selected / .hovered / .default / .found.
 *
 * The vanilla widget is the API (gcSelectBox.bindWithin/bind/open/close/setVal/
 * refresh). $.fn.SelectBox was removed in stage 5 C.1 — every emitter readyJs
 * site + the v2 bridges (screens.js, filter.js) now call gcSelectBox.bindWithin
 * directly. The remaining jQuery entry shims ($.fn.open/close/setVal/destroy and
 * the cross-cutting $.fn.val override) were removed in stage 5 once jQuery itself
 * was dropped — this file is now fully jQuery-free. The live parent → child
 * cascade rides on setOptions() below: app/cascade.js re-fetches a child's
 * option list from the server and hands it here, and the widget's native
 * 'change' on the hidden .selextbox-input is the sole sync path (the jQuery
 * $.fn.val override that used to fire it is long gone).
 *
 * Deliberately NOT reproduced (niche / already-dead in the original):
 *   - drag-range mouse select + shift-click range (rare power-user gesture)
 *   - the "slice last char" fuzzy search fallback (prefix match kept)
 *   - ctrl+A select-all-typed-text micro-interaction
 *
 * Dropdown positioning (openLabel): the menu is position:absolute, so it is
 * clipped by the nearest overflow ancestor (list filter `.sheet-body`, the
 * drawer `.proto-screen`, any scroll container), NOT by the viewport. So the
 * flip/clamp math measures the VISIBLE region = viewport ∩ every overflow
 * ancestor (see clipRectFor) and never sizes the menu taller than the space on
 * the chosen side — so a box low in a short panel flips up and still fits
 * inside the panel instead of spilling past its top edge. Menus close on scroll
 * so the clamped geometry can't go stale. Skipped on mobile (fixed bottom sheet).
 */
(function () {
  'use strict';

  var OPEN_CLASS = 'show';
  var SELECTED = 'selected';
  var HOVERED = 'hovered';
  var KEYPRESS_RESET_MS = 700;

  function isMobileUA() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  // --- per-label element accessors -----------------------------------------
  function span(label) { return label.querySelector('.select-label-span'); }
  // list() is portal-aware: openLabel may move the menu <ul> out of a scrollable
  // overlay (to document.body) so the panel can't reveal-scroll it. label._gcMenu
  // keeps pointing at the same <ul> wherever it lives, so every accessor resolves.
  function list(label) { return (label._gcMenu && label._gcMenu.isConnected) ? label._gcMenu : label.querySelector('ul.select-element'); }
  function input(label) { return label.querySelector('input.selextbox-input') || label.querySelector('input'); }
  function items(label) { var u = list(label); return u ? Array.prototype.slice.call(u.querySelectorAll(':scope > li')) : []; }
  function isMultiple(label) { return label.hasAttribute('multiple'); }
  function isDisabled(label) { return label.hasAttribute('disabled'); }
  function liLabel(li) { return li.getAttribute('data-label'); }
  function liValue(li) { return li.getAttribute('data-value'); }
  function defaultLi(label) { var u = list(label); return u ? u.querySelector(':scope > li.default') : null; }
  // Query the option list (portal-aware): the <li>s live in the menu, which may
  // be portaled out of the label, so never query the label subtree directly.
  function mq(label, sel) { var u = list(label); return u ? u.querySelector(sel) : null; }
  function mqa(label, sel) { var u = list(label); return u ? u.querySelectorAll(sel) : []; }

  // --- value / label rendering ---------------------------------------------

  // Re-render the visible label text + state from the CURRENTLY selected <li>s.
  // Does NOT touch the hidden input (caller decides).
  function renderLabelFromSelection(label) {
    var sp = span(label);
    var selected = mqa(label, ':scope > li.' + SELECTED);
    if (selected.length === 0) {
      var dl = defaultLi(label);
      sp.textContent = dl ? (liLabel(dl) || '') : '';
      sp.classList.add('gray');
      return;
    }
    var labels = [];
    Array.prototype.forEach.call(selected, function (li) {
      if (li.classList.contains('default')) { return; }
      labels.push(liLabel(li));
    });
    if (labels.length === 0) {
      var d = defaultLi(label);
      sp.textContent = d ? (liLabel(d) || '') : '';
      sp.classList.add('gray');
    } else {
      sp.textContent = labels.join(', ');
      sp.classList.remove('gray');
    }
  }

  // Write the hidden input value from current selection and re-render label.
  // `fireChange` dispatches a native change so external listeners (ChildSelect
  // cascade, validators) run. We set the value DIRECTLY (not via the jQuery
  // val override) to avoid a feedback loop with our own change listener.
  function commitValue(label, fireChange) {
    var inp = input(label);
    var selected = items(label).filter(function (li) {
      return li.classList.contains(SELECTED) && !li.classList.contains('default');
    });
    var values = selected.map(liValue);
    inp.value = values.join(',');

    var ul = list(label);
    if (ul) { ul.setAttribute('data-default-selected', JSON.stringify(values.length ? values : [''])); }

    if (values.length === 0) {
      var dl = defaultLi(label);
      if (dl) { dl.classList.add(SELECTED); }
    }
    renderLabelFromSelection(label);

    if (fireChange) {
      // Mark so the input's own change listener skips the redundant re-render.
      label._gcInternalChange = true;
      var ev;
      try { ev = new Event('change', { bubbles: true }); }
      catch (e) { ev = document.createEvent('HTMLEvents'); ev.initEvent('change', true, false); }
      inp.dispatchEvent(ev);
      label._gcInternalChange = false;
    }
  }

  // Apply selection for a single click/keyboard pick.
  function pick(label, li, multiple) {
    if (multiple) {
      var dl = defaultLi(label);
      if (li.classList.contains('default')) {
        // "Clear": deselect everything.
        items(label).forEach(function (x) { x.classList.remove(SELECTED); });
        commitValue(label, true);
        return;
      }
      if (dl) { dl.classList.remove(SELECTED); }
      li.classList.toggle(SELECTED);
      commitValue(label, true);
    } else {
      items(label).forEach(function (x) { x.classList.remove(SELECTED); });
      li.classList.add(SELECTED);
      var sp = span(label);
      if (li.classList.contains('default')) { sp.classList.add('default'); }
      else { sp.classList.remove('default'); }
      commitValue(label, true);
    }
    checkDefaultVisibility(label);
  }

  // Hide the "Clear" default row when nothing real is selected (matches the
  // original's checkDefault: only show Clear once a real value is chosen).
  function checkDefaultVisibility(label) {
    var dl = defaultLi(label);
    if (!dl) { return; }
    var hasReal = items(label).some(function (li) {
      return li.classList.contains(SELECTED) && !li.classList.contains('default');
    });
    dl.style.display = hasReal ? '' : 'none';
  }

  // Re-sync selection + label FROM the hidden input value (external set path).
  function updateSelectedFromValue(label) {
    var inp = input(label);
    var raw = inp.value || '';
    items(label).forEach(function (li) { li.classList.remove(SELECTED); });

    if (raw === '') {
      var dl = defaultLi(label);
      if (dl) { dl.classList.add(SELECTED); }
      renderLabelFromSelection(label);
      checkDefaultVisibility(label);
      return;
    }
    var values = isMultiple(label) ? raw.split(',') : [raw];
    values.forEach(function (v) {
      var li = mq(label, ':scope > li[data-value="' + cssEscapeValue(v) + '"]');
      if (li) { li.classList.add(SELECTED); }
    });
    renderLabelFromSelection(label);
    checkDefaultVisibility(label);
  }

  // Minimal value escaper for the [data-value="..."] selector (values are
  // ids/codes; guard quotes/backslashes defensively).
  function cssEscapeValue(v) {
    return String(v).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  }

  // Visible region for `el`'s dropdown = the viewport intersected with every
  // overflow ancestor's box. The menu is position:absolute, so any ancestor
  // that establishes a clipping box (overflow-y auto/scroll/hidden) cuts the
  // menu off — measuring the raw viewport is what let a dropdown low in a short
  // panel spill past the panel's top/bottom edge. Returns viewport-relative
  // top/bottom in CSS px.
  // Inline geometry set by openLabel must beat the scoped stylesheets: the
  // drawer CSS (_formv2.scss) pins ul.select-element's top/bottom/max-height
  // with !important, and a plain `el.style.x = v` loses to that — the menu
  // stayed 280px tall inside a shorter scroll body and got clipped (no way to
  // reach the tail of the list). Setting with the 'important' priority wins;
  // closeLabel's `style.x = ''` still removes it.
  function setImp(el, prop, val) { el.style.setProperty(prop, val, 'important'); }

  function clipRectFor(el) {
    var top = 0;
    var bottom = window.innerHeight || document.documentElement.clientHeight;
    var a = el.parentElement;
    while (a && a !== document.body && a !== document.documentElement) {
      var cs = getComputedStyle(a);
      // Only vertical clipping matters here. A box with overflow-y other than
      // visible (the browser also forces overflow-y to a clipping value when
      // overflow-x is non-visible, reflected in the computed value) clips us.
      if (/(auto|scroll|hidden|clip)/.test(cs.overflowY) || /(auto|scroll|hidden|clip)/.test(cs.overflow)) {
        var ar = a.getBoundingClientRect();
        if (ar.top > top) { top = ar.top; }
        if (ar.bottom < bottom) { bottom = ar.bottom; }
      }
      a = a.parentElement;
    }
    return { top: top, bottom: bottom };
  }

  // --- open / close ---------------------------------------------------------
  function openLabel(label) {
    if (isDisabled(label)) { return; }
    closeAllExcept(label);
    // Snapshot scrollable-ancestor positions BEFORE any DOM change, so the
    // portaled-menu path can undo the browser's one-time reveal-scroll of the
    // panel on open (captured here = the user's real scroll position).
    var _gcOpenScrolls = [];
    (function () {
      var a = label.parentElement;
      while (a && a !== document.body && a !== document.documentElement) {
        var cs = getComputedStyle(a);
        if (/(auto|scroll)/.test(cs.overflowY) || /(auto|scroll)/.test(cs.overflowX)) {
          _gcOpenScrolls.push({ el: a, top: a.scrollTop, left: a.scrollLeft });
        }
        a = a.parentElement;
      }
    })();
    // Position the dropdown so it always fits the VISIBLE region, regardless of
    // which CSS scope styles it. The flip is driven by inline styles here (they
    // beat every scoped stylesheet rule — the old class-only flip was scoped to
    // drawer forms and did nothing for list filters, child lists, etc.). Space
    // is measured against `clipRectFor(label)` — the viewport intersected with
    // every overflow ancestor — because the position:absolute menu is clipped
    // by those ancestors, not by the viewport. The height is clamped to the
    // space actually available on the chosen side and is NEVER forced larger
    // than that, so a tall list scrolls internally instead of spilling past a
    // panel edge or off-screen. Skipped on mobile (fixed bottom sheet).
    // closeLabel() resets these.
    var menu = list(label);
    if (menu && !(label._gcMobile || isMobileUA())) {
      var r = label.getBoundingClientRect();
      var GAP = 4, CAP = 280, MARGIN = 8;
      if (label.closest('.va-filter-panel')) {
        // The filter popover's body scrolls; an absolute menu inside it makes
        // the browser reveal-scroll the panel (and the list behind it) on open
        // and cramps the flipped-up menu. Portal the menu to <body> and fix it
        // to the viewport — out of the scroll container entirely, so opening
        // scrolls nothing and it's sized against the viewport. It carries
        // .gc-portal-menu for self-contained styling; closeLabel() restores it.
        if (!menu._gcHome) { menu._gcHome = { parent: menu.parentNode, next: menu.nextSibling }; }
        menu.classList.add('gc-portal-menu');
        document.body.appendChild(menu);
        var vh = window.innerHeight || document.documentElement.clientHeight;
        var sDown = (vh - r.bottom) - GAP - MARGIN;
        var sUp = r.top - GAP - MARGIN;
        var up = (sDown < CAP) && (sUp > sDown);
        setImp(menu, 'position', 'fixed');
        setImp(menu, 'left', r.left + 'px');
        setImp(menu, 'width', r.width + 'px');
        if (up) {
          label.classList.add('show-top');
          setImp(menu, 'top', 'auto');
          setImp(menu, 'bottom', (vh - r.top + GAP) + 'px');
        } else {
          label.classList.remove('show-top');
          setImp(menu, 'bottom', 'auto');
          setImp(menu, 'top', (r.bottom + GAP) + 'px');
        }
        setImp(menu, 'max-height', Math.min(CAP, Math.max(0, up ? sUp : sDown)) + 'px');
        setImp(menu, 'overflow-y', 'auto');
      } else {
        // Default: position:absolute, clamped to the VISIBLE region (viewport ∩
        // every overflow ancestor — see clipRectFor). Inline styles beat scoped
        // CSS; height clamped to the chosen side so a tall list scrolls
        // internally instead of spilling past a panel edge. closeLabel() resets.
        var clip = clipRectFor(label);
        var spaceDown = (clip.bottom - r.bottom) - GAP - MARGIN;
        var spaceUp = (r.top - clip.top) - GAP - MARGIN;
        var flipUp = (spaceDown < CAP) && (spaceUp > spaceDown);
        var maxH = Math.min(CAP, Math.max(0, flipUp ? spaceUp : spaceDown));
        if (flipUp) {
          label.classList.add('show-top');
          setImp(menu, 'top', 'auto');
          setImp(menu, 'bottom', 'calc(100% + ' + GAP + 'px)');
        } else {
          label.classList.remove('show-top');
          menu.style.top = '';
          menu.style.bottom = '';
        }
        setImp(menu, 'max-height', maxH + 'px');
        setImp(menu, 'overflow-y', 'auto');
      }
    }
    label.classList.add(OPEN_CLASS);
    // Reveal the selected option now that the list is displayed: drawer CSS
    // keeps a closed list display:none (see _formv2.scss), and a display:none
    // element has no offsets to scroll to.
    if (mq(label, 'li.selected')) {
      var ulSel = list(label);
      var selLi = mq(label, 'li.selected');
      if (ulSel && selLi) { ulSel.scrollTop = selLi.offsetTop - ulSel.offsetTop; }
    }
    // Portaled (filter-panel) case: the browser still does a one-time
    // reveal-scroll of the panel body on open. The menu is fixed to the
    // viewport and doesn't need the panel scrolled, so undo that scroll (sync +
    // a couple of frames) and gate the scroll-close handler briefly so it
    // doesn't treat this self-induced scroll as a "user scrolled away → close".
    if (menu && menu._gcHome) {
      var locks = _gcOpenScrolls;
      var relock = function () {
        for (var i = 0; i < locks.length; i++) {
          if (locks[i].el.scrollTop !== locks[i].top) { locks[i].el.scrollTop = locks[i].top; }
          if (locks[i].el.scrollLeft !== locks[i].left) { locks[i].el.scrollLeft = locks[i].left; }
        }
      };
      label._gcJustOpened = true;
      relock();
      requestAnimationFrame(relock);
      // The reveal-scroll can fire on a later tick than our frames cover; relock
      // on every scroll during the grace window so it's undone whenever it lands
      // (the close-on-scroll handler is gated by _gcJustOpened so it won't fire).
      var onScrollRelock = function () { relock(); };
      document.addEventListener('scroll', onScrollRelock, true);
      setTimeout(function () {
        relock();
        document.removeEventListener('scroll', onScrollRelock, true);
        label._gcJustOpened = false;
      }, 400);
    }
  }

  function closeLabel(label) {
    label.classList.remove(OPEN_CLASS);
    var h = label.querySelector('.' + HOVERED);
    if (h) { h.classList.remove(HOVERED); }
    var cm = list(label);
    if (cm) {
      cm.style.top = ''; cm.style.bottom = ''; cm.style.maxHeight = ''; cm.style.overflowY = '';
      cm.style.position = ''; cm.style.left = ''; cm.style.width = '';
      // Move a portaled menu back into its label so the markup/selectors and
      // scoped CSS are exactly as before opening.
      if (cm._gcHome) {
        cm.classList.remove('gc-portal-menu');
        cm._gcHome.parent.insertBefore(cm, cm._gcHome.next);
        cm._gcHome = null;
      }
    }
    setTimeout(function () { label.classList.remove('show-top'); }, 500);
    label._gcSearch = '';
  }

  function closeAllExcept(except) {
    Array.prototype.forEach.call(document.querySelectorAll('.js-select-label.' + OPEN_CLASS), function (l) {
      if (l !== except) { closeLabel(l); }
    });
  }

  // --- type-ahead search ----------------------------------------------------
  function clearFound(label) {
    Array.prototype.forEach.call(mqa(label, 'li.found'), function (li) {
      li.classList.remove('found');
      var s = li.querySelector('strong');
      if (s) { s.textContent = s.textContent; }
    });
  }

  function runSearch(label) {
    var term = (label._gcSearch || '').trim();
    clearFound(label);
    if (!term) { return; }
    var matched = null;
    items(label).forEach(function (li) {
      if (li.classList.contains('default')) { return; }
      var lab = (liLabel(li) || '').toLowerCase();
      if (lab.indexOf(term.toLowerCase()) === 0) {
        li.classList.add('found');
        if (!matched) { matched = li; }
      }
    });
    if (matched) {
      var ul = list(label);
      if (ul) { ul.scrollTop = matched.offsetTop - ul.offsetTop; }
    }
  }

  // --- keyboard nav (within an open label) ---------------------------------
  function moveHighlight(label, dir) {
    var lis = items(label).filter(function (li) { return li.style.display !== 'none'; });
    if (!lis.length) { return; }
    var cur = mq(label, 'li.' + HOVERED);
    var idx = cur ? lis.indexOf(cur) : -1;
    if (cur) { cur.classList.remove(HOVERED); }
    idx += dir;
    if (idx < 0) { idx = 0; }
    if (idx > lis.length - 1) { idx = lis.length - 1; }
    var next = lis[idx];
    next.classList.add(HOVERED);
    var ul = list(label);
    if (ul) {
      var top = next.offsetTop - ul.offsetTop;
      if (top < ul.scrollTop) { ul.scrollTop = top; }
      else if (top + next.offsetHeight > ul.scrollTop + ul.clientHeight) {
        ul.scrollTop = top + next.offsetHeight - ul.clientHeight;
      }
    }
  }

  // --- option replacement (live ChildSelect cascade) ------------------------
  // Replace the option rows of a bound selectbox with a freshly fetched list.
  // `options` is [{value, label, v}, ...] exactly as the server sends it (labels
  // already ucfirst'd server-side — this is a dumb renderer). The li.default
  // ("Clear") and li.null ("Empty value") sentinel rows are kept; every other
  // <li> is dropped and rebuilt through the DOM API (never innerHTML: labels
  // come from user data / translations).
  //
  // Value resolution, WITHOUT going through pick(): keep the hidden input's
  // current value when the new list still carries a matching row (including the
  // '_null' sentinel the user may have picked), otherwise `mode` decides —
  // 'keep-or-first' takes the first real row (required FK), anything else
  // ('keep-or-empty') clears it (nullable FK). A label[multiple] holds a comma
  // list instead: it keeps the intersection with the new rows and ignores
  // `mode` entirely.
  //
  // NEVER dispatches 'change': the caller compares the returned value with the
  // one it read before and fires the event itself, so a reload that leaves the
  // value alone cannot echo back into the cascade. Returns the resulting value.
  function setOptions(label, options, mode) {
    var ul = list(label);
    var inp = input(label);
    if (!ul || !inp) { return inp ? inp.value : ''; }

    Array.prototype.forEach.call(ul.querySelectorAll(':scope > li'), function (li) {
      if (li.classList.contains('default') || li.classList.contains('null')) { return; }
      ul.removeChild(li);
    });

    var firstReal = null;
    (options || []).forEach(function (o) {
      if (!o) { return; }
      var value = (o.value == null) ? '' : String(o.value);
      var text = (o.label == null) ? '' : String(o.label);
      // Same skip as the server renderer (optionListeSelect): the ['', '', '']
      // blank sentinel a nullable FK option list starts with is not a row —
      // li.default already clears the field.
      if (value === '' && text === '') { return; }
      var li = document.createElement('li');
      li.setAttribute('v', (o.v == null) ? '' : String(o.v));
      li.setAttribute('unselectable', 'on');
      li.setAttribute('data-label', text);
      li.setAttribute('data-value', value);
      var strong = document.createElement('strong');
      strong.setAttribute('unselectable', 'on');
      strong.setAttribute('title', text);
      strong.textContent = text;
      li.appendChild(strong);
      ul.appendChild(li);
      if (!firstReal) { firstReal = li; }
    });

    var current = inp.value || '';
    var value = '';
    if (isMultiple(label)) {
      // A multi-select's hidden input is a comma list, which can never match a
      // single li[data-value]. Keep the intersection of the current selection
      // with the new rows and NEVER apply `mode`: falling back to one row would
      // silently replace everything the user picked.
      value = current === '' ? '' : current.split(',').filter(function (v) {
        return v !== '' && !!mq(label, ':scope > li[data-value="' + cssEscapeValue(v) + '"]');
      }).join(',');
    } else if (current !== '' && mq(label, ':scope > li[data-value="' + cssEscapeValue(current) + '"]')) {
      value = current;
    } else if (mode === 'keep-or-first' && firstReal) {
      // NOT `|| ''`: a legitimate data-value="0" (0-indexed enum-backed select)
      // is falsy and would be turned into an empty selection.
      var fv = liValue(firstReal);
      value = (fv == null) ? '' : fv;
    }
    inp.value = value;
    updateSelectedFromValue(label);
    checkDefaultVisibility(label);
    return value;
  }

  // Quick-add hook: append one option to a bound selectbox and select it.
  // el = the hidden .selextbox-input (or the label.select-label itself).
  // data-label keeps the ORIGINAL case: renderLabelFromSelection() uses it as
  // the visible label text; type-ahead lowercases at compare time (runSearch).
  // If an option with the same value already exists it is reused (no dupes).
  // Selecting goes through pick(), which commits the hidden input and fires
  // its native change (bubbles) so external listeners/dependents stay in sync.
  function addOption(el, value, text, select) {
    var label = el && el.classList && el.classList.contains('select-label') ? el : (el && el.closest ? el.closest('label.select-label') : null);
    if (!label) { return false; }
    var ul = list(label);
    if (!ul) { return false; }
    var li = ul.querySelector('li[data-value="' + cssEscapeValue(value) + '"]');
    if (!li) {
      li = document.createElement('li');
      li.setAttribute('data-value', String(value));
      li.setAttribute('data-label', String(text));
      var strong = document.createElement('strong');
      strong.setAttribute('title', String(text));
      strong.textContent = String(text);
      li.appendChild(strong);
      ul.appendChild(li);
    }
    if (select !== false) { pick(label, li, isMultiple(label)); }
    return true;
  }

  // --- per-label binding ----------------------------------------------------
  function bindOne(label) {
    if (isDisabled(label)) { return; }
    if (label._gcBound) { return; }
    label._gcBound = true;
    label._gcSearch = '';

    var ul = list(label);
    if (ul) { label._gcMenu = ul; }
    if (ul && !ul.getAttribute('data-default-selected')) {
      ul.setAttribute('data-default-selected', JSON.stringify(['']));
    }

    // Initial state: select first option if nothing chosen yet.
    var inp = input(label);
    var hasDefaultSelected = !!mq(label, 'li.default.selected');
    if (!hasDefaultSelected && (!inp || inp.value === '')) {
      var firstLi = ul ? ul.querySelector('li') : null;
      if (firstLi) { pick(label, firstLi, false); }
    }
    if (inp && inp.classList.contains('changed-value')) {
      updateSelectedFromValue(label);
    } else if (inp && inp.value !== '') {
      updateSelectedFromValue(label);
    }
    checkDefaultVisibility(label);

    if (isMobileUA()) {
      label.classList.add('mobile');
      label._gcMobile = true;
      var closeBtn = label.querySelector('.js-select-close-button');
      if (closeBtn) { closeBtn.addEventListener('click', function () { closeLabel(label); }); }
    }

    // Toggle open/close on the label span.
    var sp = span(label);
    if (sp) {
      // Prevent the mouse-focus default: focusing the span (or, via the filter
      // popover's focus trap, the panel) makes the browser reveal-scroll the
      // scrollable panel to the top on open — the "list jumps" effect. Click
      // still fires (open) and keyboard focus/nav is unaffected.
      sp.addEventListener('mousedown', function (e) { e.preventDefault(); });
      sp.addEventListener('click', function () {
        if (label.classList.contains(OPEN_CLASS)) { closeLabel(label); }
        else { openLabel(label); }
      });
    }

    // Option click (delegated).
    if (ul) {
      ul.addEventListener('click', function (e) {
        var li = e.target.closest('li');
        if (!li || !ul.contains(li)) { return; }
        pick(label, li, isMultiple(label));
        if (!isMultiple(label)) { closeLabel(label); }
      });
    }

    // Hidden input change → re-sync from value (external set path: the jQuery
    // $.fn.val override or setVal dispatch a change here). Skip when WE just
    // fired it from commitValue (guard flag) to avoid redundant work.
    if (inp) {
      inp.addEventListener('change', function () {
        if (label._gcInternalChange) { return; }
        updateSelectedFromValue(label);
      });
    }

    // Keyboard nav + type-ahead while focused/open.
    if (sp) { sp.setAttribute('tabindex', sp.getAttribute('tabindex') || '0'); }
    label.addEventListener('keydown', function (e) {
      if (!label.classList.contains(OPEN_CLASS)) {
        if (e.keyCode === 13 || e.keyCode === 32) { e.preventDefault(); openLabel(label); }
        return;
      }
      var key = e.keyCode || e.which;
      if (key === 38) { e.preventDefault(); moveHighlight(label, -1); }
      else if (key === 40) { e.preventDefault(); moveHighlight(label, 1); }
      else if (key === 13) {
        e.preventDefault();
        var hov = mq(label, 'li.' + HOVERED) || mq(label, 'li.found');
        if (hov) { pick(label, hov, isMultiple(label)); if (!isMultiple(label)) { closeLabel(label); } }
      } else if (key === 27) { closeLabel(label); }
    });
    label.addEventListener('keypress', function (e) {
      if (!label.classList.contains(OPEN_CLASS)) { return; }
      var key = e.keyCode || e.which;
      if (key === 13 || key === 10 || key === 27) { return; }
      var ch = String.fromCharCode(key);
      if (!ch) { return; }
      label._gcSearch = (label._gcSearch || '') + ch;
      clearTimeout(label._gcSearchTimer);
      label._gcSearchTimer = setTimeout(function () { runSearch(label); }, 1);
      var resetTimer = setTimeout(function () { label._gcSearch = ''; }, KEYPRESS_RESET_MS);
      label._gcSearchReset = resetTimer;
    });
  }

  // Global outside-click / scroll close (bound once).
  var globalBound = false;
  function bindGlobal() {
    if (globalBound) { return; }
    globalBound = true;
    document.addEventListener('click', function (e) {
      var open = document.querySelectorAll('.js-select-label.' + OPEN_CLASS);
      if (!open.length) { return; }
      Array.prototype.forEach.call(open, function (label) {
        // A portaled menu lives outside the label (in <body>), so also treat
        // clicks inside it as "inside" — otherwise picking an option would
        // count as an outside click and close before the pick is handled.
        var portaled = label._gcMenu;
        var inside = label.contains(e.target) || (portaled && portaled.contains(e.target));
        if (!inside) {
          // For a multi-select, keep it open while ctrl/meta/shift held (matches
          // the original's multi-select stickiness).
          var keep = isMultiple(label) && (e.ctrlKey || e.metaKey || e.shiftKey) && !label._gcMobile;
          if (!keep) { closeLabel(label); }
        }
      });
    }, true);

    // Close any open menu when an ancestor scrolls: the dropdown is clamped to
    // the visible region at open time, so scrolling would otherwise drag the
    // menu against (and past) a panel edge it no longer fits. Capture-phase so
    // it catches scrolls on inner containers (.sheet-body, drawer body), not
    // just the window. Ignore scrolls that originate inside an open menu's own
    // option list. Mobile bottom-sheet is position:fixed and unaffected.
    document.addEventListener('scroll', function (e) {
      var open = document.querySelectorAll('.js-select-label.' + OPEN_CLASS);
      if (!open.length) { return; }
      Array.prototype.forEach.call(open, function (label) {
        if (label._gcMobile) { return; }
        // Ignore the one-time reveal-scroll the browser fires right after open
        // (a fixed/portaled menu doesn't go stale from it).
        if (label._gcJustOpened) { return; }
        var ul = list(label);
        // Ignore scrolling the menu's own option list; close on any other scroll.
        if (ul && e.target === ul) { return; }
        closeLabel(label);
      });
    }, true);
  }

  // --- public API -----------------------------------------------------------
  var gcSelectBox = {
    // Bind every .js-select-label inside `root` (Element or document).
    bindWithin: function (root) {
      bindGlobal();
      root = root || document;
      var labels = root.querySelectorAll ? root.querySelectorAll('.js-select-label') : [];
      Array.prototype.forEach.call(labels, bindOne);
    },
    // Bind a single label element.
    bind: function (label) { bindGlobal(); if (label) { bindOne(label); } },
    open: function (label) { if (label) { openLabel(label); } },
    close: function (label) { if (label) { closeLabel(label); } },
    // Set value + re-render (external set path). Mirrors the old setVal:
    // writes the hidden input then syncs the display.
    setVal: function (label, val) {
      var inp = input(label);
      if (!inp) { return; }
      inp.value = Array.isArray(val) ? val.join(',') : (val == null ? '' : val);
      updateSelectedFromValue(label);
    },
    // Cascade hook (app/cascade.js): swap the option rows for a freshly fetched
    // list and resolve the value ('keep-or-first' | 'keep-or-empty'). Fires no
    // 'change' — the caller does, and only when the value actually moved.
    setOptions: setOptions,
    // Quick-add hook: append one option (value/text) and select it (unless
    // select === false). Accepts the hidden .selextbox-input or the label.
    addOption: addOption,
    // Re-sync the visible label/selection FROM the hidden input value. Used by
    // the $.fn.val override (jQuery's .change() does not reach our native
    // addEventListener('change'), so the override calls this explicitly).
    refresh: function (label) { if (label) { updateSelectedFromValue(label); } }
  };
  window.gcSelectBox = gcSelectBox;

  if (!String.prototype.capitalize) {
    String.prototype.capitalize = function () {
      return this.charAt(0).toUpperCase() + this.slice(1);
    };
  }
})();
