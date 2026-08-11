/* gcUpload — vanilla file-upload widget (plupload removal, 2026-06).
 *
 * One shared implementation for the three emitted upload modes
 * (list-header, edit-form with replace, child-list). The emitter passes a
 * per-table config; everything generic lives here.
 *
 * Server contract (unchanged from the plupload era, also used by
 * gceditor.js): POST multipart to <Table>/upload, file field "file",
 * extra fields from `params`; response JSON
 * {status:'success'|'failure', File, idPk, messages:[...]}.
 *
 * `filters` keeps plupload's HJSON shape so no schema changes anywhere:
 * { max_file_size: '10mb', mime_types: [{title, extensions:'jpg,png'}] }
 */
(function () {
    'use strict';

    function parseSize(s) {
        if (typeof s === 'number') { return s; }
        var m = /^\s*([\d.]+)\s*(gb|mb|kb|b)?\s*$/i.exec(String(s || ''));
        if (!m) { return 0; }
        var mult = { b: 1, kb: 1024, mb: 1048576, gb: 1073741824 };
        return Math.round(parseFloat(m[1]) * mult[(m[2] || 'b').toLowerCase()]);
    }

    function extList(filters) {
        var exts = [];
        ((filters && filters.mime_types) || []).forEach(function (mt) {
            String(mt.extensions || '').split(',').forEach(function (e) {
                e = e.trim().toLowerCase();
                if (e) { exts.push(e); }
            });
        });
        return exts;
    }

    /* Progress / error DOM — byte-compatible with the markup the emitted
     * plupload blocks built (classes .upl-progress/.upl-bar/.upl-bar-fill/
     * .upl-pct/.upl-errors styled in the existing CSS). */
    function buildProgress() {
        var w  = document.createElement('div'); w.className  = 'upl-progress';
        var b  = document.createElement('div'); b.className  = 'upl-bar';
        var bf = document.createElement('div'); bf.className = 'upl-bar-fill';
        var p  = document.createElement('div'); p.className  = 'upl-pct'; p.textContent = '0%';
        b.appendChild(bf); w.appendChild(b); w.appendChild(p);
        return w;
    }

    function flattenErrors(list) {
        var out = [];
        (list || []).forEach(function (item) {
            if (Array.isArray(item)) { item.forEach(function (m) { out.push(String(m)); }); }
            else if (item && typeof item === 'object') { out.push(JSON.stringify(item)); }
            else if (item != null && item !== '') { out.push(String(item)); }
        });
        return out;
    }

    function renderErrors(list) {
        var w = document.createElement('div'); w.className = 'upl-errors';
        var t = document.createElement('div'); t.className = 'upl-err-title'; t.textContent = 'Upload failed';
        var ul = document.createElement('ul');
        flattenErrors(list).forEach(function (m) {
            var li = document.createElement('li'); li.textContent = m; ul.appendChild(li);
        });
        w.appendChild(t); w.appendChild(ul);
        return w;
    }

    function setPct(pct) {
        // alertb routes through gcScreens.alert → alertDialog(), which appends
        // .gc-confirm-box to <body>; the progress markup lands in its
        // .gc-confirm-msg.
        var ctx = document.querySelector('.gc-confirm-box .gc-confirm-msg');
        if (!ctx) { return; }
        var bf = ctx.querySelector('.upl-bar-fill');
        if (bf) { bf.style.width = pct + '%'; }
        var pc = ctx.querySelector('.upl-pct');
        if (pc) { pc.textContent = pct + '%'; }
    }

    /* opts:
     *   browseBtn  (id, required)  click → file picker
     *   dropEl     (id, optional)  drag-drop target
     *   url        (required)      POST target
     *   filters    plupload-shaped (see header)
     *   multi      default true; false = single file per pick
     *   title      alertb title for validate() rejections (default 'Upload')
     *   params     object OR function(file) → {field: value} appended to FormData
     *   validate   function(files) → error string | null, before any upload
     *   onFileDone function(respJson, file) per parsed server response —
     *              fires on failure responses too (mirrors the old plupload
     *              FileUploaded hook position); check respJson.status
     *   onComplete function(errorsArray) after the queue drains
     */
    function create(opts) {
        var browseBtn = document.getElementById(opts.browseBtn);
        if (!browseBtn) { return null; }
        var exts = extList(opts.filters);
        var maxBytes = parseSize(opts.filters && opts.filters.max_file_size);
        var errors = [];
        var uploading = false;

        var input = document.createElement('input');
        input.type = 'file';
        input.style.display = 'none';
        if (opts.multi !== false) { input.multiple = true; }
        if (exts.length) {
            input.accept = exts.map(function (e) { return '.' + e; }).join(',');
        }
        (browseBtn.parentNode || document.body).appendChild(input);

        function fail(file, msg) {
            errors.push((file ? file.name + ': ' : '') + msg);
        }

        function filterFiles(files) {
            var ok = [];
            files.forEach(function (f) {
                var dot = f.name.lastIndexOf('.');
                var ext = dot > -1 ? f.name.slice(dot + 1).toLowerCase() : '';
                if (exts.length && exts.indexOf(ext) === -1) { fail(f, 'File type not allowed'); return; }
                if (maxBytes && f.size > maxBytes) {
                    fail(f, 'File too large (max ' + opts.filters.max_file_size + ')'); return;
                }
                ok.push(f);
            });
            return ok;
        }

        function uploadOne(file, done) {
            var fd = new FormData();
            fd.append('file', file, file.name);
            var params = (typeof opts.params === 'function') ? (opts.params(file) || {}) : (opts.params || {});
            Object.keys(params).forEach(function (k) { fd.append(k, params[k]); });
            // #33: raw XHR (needed for upload-progress events) routed through the
            // shared gcCore.xhrPost, which attaches the CSRF token + XHR marker the
            // way AuthyMiddleware::checkCsrf expects — no per-site token plumbing.
            window.gcCore.xhrPost(opts.url, fd, {
                onProgress: function (pct) { setPct(pct); },
                onLoad: function (xhr) {
                    var resp = null;
                    try { resp = JSON.parse(xhr.responseText); } catch (e) { /* non-JSON */ }
                    if (xhr.status < 200 || xhr.status >= 300) {
                        fail(file, 'HTTP ' + xhr.status);
                    } else if (!resp) {
                        fail(file, 'Invalid server response');
                    } else if (resp.status !== 'success') {
                        flattenErrors(resp.messages || ['Upload failed']).forEach(function (m) { fail(file, m); });
                    }
                    if (resp && typeof opts.onFileDone === 'function') { opts.onFileDone(resp, file); }
                    done();
                },
                onError: function () { fail(file, 'Network error'); done(); }
            });
        }

        function start(fileList) {
            var files = Array.prototype.slice.call(fileList || []);
            if (!files.length || uploading) { return; }
            if (opts.multi === false) { files = files.slice(0, 1); }
            errors = [];
            if (typeof opts.validate === 'function') {
                var err = opts.validate(files);
                if (err) { input.value = ''; alertb(opts.title || 'Upload', err); return; }
            }
            var queue = filterFiles(files);
            input.value = '';
            if (!queue.length) {
                if (errors.length) { alertb('Upload failed', renderErrors(errors)); }
                return;
            }
            uploading = true;
            alertb('Uploading', buildProgress());
            var i = 0;
            (function next() {
                if (i >= queue.length) {
                    uploading = false;
                    if (typeof opts.onComplete === 'function') { opts.onComplete(errors.slice()); }
                    else if (errors.length) { alertb('Upload failed', renderErrors(errors)); }
                    return;
                }
                setPct(0);
                uploadOne(queue[i++], next);
            })();
        }

        browseBtn.addEventListener('click', function (e) { e.preventDefault(); input.click(); });
        input.addEventListener('change', function () { start(input.files); });

        var dropEl = opts.dropEl ? document.getElementById(opts.dropEl) : null;
        if (dropEl) {
            ['dragenter', 'dragover'].forEach(function (t) {
                dropEl.addEventListener(t, function (e) { e.preventDefault(); dropEl.classList.add('gc-upl-dragover'); });
            });
            dropEl.addEventListener('dragleave', function (e) {
                // Entering a child fires dragleave on the parent — only clear
                // the highlight when the pointer truly left the drop zone.
                if (!dropEl.contains(e.relatedTarget)) {
                    dropEl.classList.remove('gc-upl-dragover');
                }
            });
            dropEl.addEventListener('drop', function (e) {
                e.preventDefault();
                dropEl.classList.remove('gc-upl-dragover');
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) { start(e.dataTransfer.files); }
            });
        }

        return { start: start, input: input };
    }

    /* ---- #23 S5: declarative bind (capability-keyed off the <script> re-exec) ----
     *
     * The emitter used to ship the three upload inits as inline onReadyJs
     * (`gcUpload.create({...})`) re-executed by screens.js push() / the list
     * page's ready block. S5 replaces each with a self-contained config element
     *
     *   <span class='gc-upl-cfg' data-gc-upl='list|form|child' data-gc-upl-*='…'></span>
     *
     * carrying only DATA (url, filters flattened to max+ext, FK/PK names, labels,
     * runtime ip/user). The standard per-mode callback logic (validate/params/
     * onComplete) lives here, so nothing project-specific is emitted as JS.
     * Regenerated projects carry the config element and no onReadyJs; un-
     * regenerated projects keep the onReadyJs and emit no config element, so
     * bindWithin no-ops on them — never both, no double-bind. */
    function filtersFromCfg(el) {
        var max = el.getAttribute('data-gc-upl-max');
        var ext = el.getAttribute('data-gc-upl-ext');
        var f = {};
        if (max) { f.max_file_size = max; }
        if (ext) { f.mime_types = [{ title: 'files', extensions: ext }]; }
        return f;
    }

    // List-header upload: the anchor/wrapper (pickfilesForm) is emitted in the
    // list header; N files → N new rows, then redirect to the list.
    function bindList(el) {
        if (!document.getElementById('pickfilesForm')) { return; }
        var done = el.getAttribute('data-gc-upl-done') || '';
        create({
            browseBtn: 'pickfilesForm',
            dropEl: 'pickfilesForm',
            url: el.getAttribute('data-gc-upl-url'),
            filters: filtersFromCfg(el),
            onComplete: function (errors) {
                document.body.style.cursor = 'default';
                if (errors.length) { alertb('Upload failed', renderErrors(errors)); return; }
                if (done) { document.location = done; }
            }
        });
    }

    // Child-list upload: anchor (pickfiles) in the child list header; ip is the
    // parent FK value (runtime), IdUser the session user; on done, re-click the
    // child's conglet toggle to refresh the list.
    function bindChild(el) {
        if (!document.getElementById('pickfiles')) { return; }
        var conglet = el.getAttribute('data-gc-upl-conglet') || '';
        var child = el.getAttribute('data-gc-upl-child') || '';
        create({
            browseBtn: 'pickfiles',
            dropEl: 'pickfiles',
            url: el.getAttribute('data-gc-upl-url'),
            multi: el.getAttribute('data-gc-upl-multi') !== '0',
            filters: filtersFromCfg(el),
            params: {
                ip: el.getAttribute('data-gc-upl-ip') || '',
                IdUser: el.getAttribute('data-gc-upl-user') || ''
            },
            onComplete: function (errors) {
                document.body.style.cursor = 'default';
                if (errors.length) { alertb('Upload failed', renderErrors(errors)); return; }
                var cg = document.querySelector('[j=conglet_' + conglet + '][p="' + child + '"]');
                if (cg) { cg.click(); }
            }
        });
    }

    // Edit-form upload (replace mode): the server-managed file-path input is
    // swapped for an Upload button; the upload carries ip (parent select) +
    // idUpd (this row's PK). Builds the button DOM, then create().
    function bindForm(el) {
        var fileName = el.getAttribute('data-gc-upl-file');
        var fileInput = fileName ? document.querySelector("[name='" + fileName + "']") : null;
        if (!fileInput || document.getElementById('pickfilesFormUpl')) { return; }
        var url = el.getAttribute('data-gc-upl-url');
        var fk = el.getAttribute('data-gc-upl-fk') || '';
        var pkName = el.getAttribute('data-gc-upl-pk') || '';
        var parentLbl = el.getAttribute('data-gc-upl-parent') || 'parent';
        var table = el.getAttribute('data-gc-upl-table') || '';
        var done = el.getAttribute('data-gc-upl-done') || '';
        var txtUpload = el.getAttribute('data-gc-upl-txt-upload') || 'Upload';
        var txtReplace = el.getAttribute('data-gc-upl-txt-replace') || 'Replace';
        var txtFailed = el.getAttribute('data-gc-upl-txt-failed') || 'Upload failed';
        var hasFile = !!(fileInput.value && fileInput.value.trim());
        // File path is server-managed: read-only when set, hidden when empty.
        if (hasFile) {
            fileInput.readOnly = true;
            fileInput.setAttribute('readonly', 'readonly');
        } else {
            fileInput.style.display = 'none';
        }
        var pkEl = pkName ? document.querySelector("[name='" + pkName + "']") : null;
        var wrap = document.createElement('div');
        wrap.id = 'upload-form-' + table;
        wrap.className = 'gc-form-upload';
        wrap.style.cssText = 'position:relative;margin-top:6px;';
        var btn = document.createElement('a');
        btn.href = 'Javascript:';
        btn.id = 'pickfilesFormUpl';
        btn.className = 'gc-upl-btn';
        btn.style.cssText = 'display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 12px;border-radius:6px;background:#00d1b2;color:#fff;font-size:13px;font-weight:600;line-height:1;text-decoration:none;box-shadow:0 1px 2px rgba(0,209,178,.3);cursor:pointer;';
        btn.onmouseenter = function () { btn.style.background = '#00b89a'; };
        btn.onmouseleave = function () { btn.style.background = '#00d1b2'; };
        var ic = document.createElement('i');
        ic.className = 'ri-upload-2-line';
        var lb = document.createElement('span');
        lb.textContent = hasFile ? txtReplace : txtUpload;
        btn.appendChild(ic); btn.appendChild(lb);
        var list = document.createElement('div');
        list.id = 'filelistFormUpl';
        wrap.appendChild(btn); wrap.appendChild(list);
        fileInput.parentNode.insertBefore(wrap, fileInput.nextSibling);
        create({
            browseBtn: 'pickfilesFormUpl',
            dropEl: 'pickfilesFormUpl',
            url: url,
            // Replace mode targets ONE row → block multi-pick; create keeps multi.
            multi: !hasFile,
            title: txtUpload,
            filters: filtersFromCfg(el),
            validate: function () {
                var fkEl = document.querySelector("[name='" + fk + "']");
                if (!fkEl || !fkEl.value) { return 'Select a ' + parentLbl + ' before uploading'; }
                return null;
            },
            params: function () {
                var fkEl = document.querySelector("[name='" + fk + "']");
                return { ip: fkEl ? fkEl.value : '', idUpd: pkEl ? pkEl.value : '' };
            },
            onComplete: function (errors) {
                if (errors.length) { alertb(txtFailed, renderErrors(errors)); return; }
                // Standalone edit page → list; child drawer → pop + refresh parent.
                var inOwnEdit = (location.pathname || '').indexOf('/' + table + '/edit') !== -1;
                if (!inOwnEdit && window.gcScreens && typeof gcScreens.popAfterSave === 'function') {
                    gcScreens.popAfterSave(null);
                } else if (done) {
                    document.location = done;
                }
            }
        });
    }

    function bindWithin(scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        Array.prototype.forEach.call(scope.querySelectorAll('[data-gc-upl]'), function (el) {
            if (el.__gcUplBound) { return; }
            el.__gcUplBound = true;
            try {
                var mode = el.getAttribute('data-gc-upl');
                if (mode === 'list') { bindList(el); }
                else if (mode === 'child') { bindChild(el); }
                else if (mode === 'form') { bindForm(el); }
            } catch (e) { /* degrade gracefully */ }
        });
    }

    window.gcUpload = { create: create, renderErrors: renderErrors, bindWithin: bindWithin };

    // Initial page (main list / standalone edit). Drawer fragments are bound by
    // screens.js push(). Idempotent: re-runs no-op via __gcUplBound.
    (function () {
        function uplGo() { try { bindWithin(document); } catch (e) {} }
        if (document.readyState != 'loading') { uplGo(); }
        else { document.addEventListener('DOMContentLoaded', uplGo); }
    })();
})();
