/*
 * gcEditor — single owner of the CKEditor 5 lifecycle.
 *
 * CKEditor 5 has no global instance registry (unlike CKEditor 4's
 * CKEDITOR.instances[]) and ClassicEditor.create() is async. Every caller in
 * the codebase (screens.js push/pop, the emitted Wysiwyg onReadyJs, the legacy
 * panel save handler in runtime BuilderLayout, and the file-manager image
 * insert) goes through this helper so the CKE5 API lives in exactly one place —
 * mirroring how gcSelectBox.bindWithin(scope) centralizes the select widget.
 *
 * The UMD bundle (composer-managed: vendor/ckeditor/ckeditor5-self-hosted/
 * ckeditor5/ckeditor5.umd.js, fetched by gc build's composer update) exposes the
 * namespace as window.CKEDITOR; the editor config object is window.gcEditorConfig
 * (public/js/ckeditor.config.js).
 */
(function () {
  'use strict';

  // Bound editors as [{el, editor}], newest last. Keyed by ELEMENT, not id:
  // stacked screens (screens.js push) re-emit the same column ids ("Text",
  // "Description", …), so an id-keyed registry made the second drawer look
  // already-bound (its wysiwyg never appeared) and popping it destroyed the
  // FIRST drawer's editor. Element must still have an id (the server emits
  // column name as id); we skip id-less textareas, same as the old code did.
  var entries = [];
  // element of the editor that last held focus — used to target insertHtml()
  // the way CKE4's CKEDITOR.instances[focused] lookup did.
  var lastFocusedEl = null;

  function entryFor(el) {
    for (var i = entries.length - 1; i >= 0; i--) {
      if (entries[i].el === el) { return entries[i]; }
    }
    return null;
  }

  function dropEntry(el) {
    for (var i = entries.length - 1; i >= 0; i--) {
      if (entries[i].el === el) { entries.splice(i, 1); }
    }
  }

  // Textareas to upgrade: the .tinymce class the emitter stamps on CLOB/BLOB
  // wysiwyg columns, plus the name-pattern fallback for I18n columns that don't
  // receive .tinymce (ArticleI18n_Body_*, _Note, _Description, _Content).
  var SELECTOR = 'textarea.tinymce,'
    + ' textarea[id*="_Body_"], textarea[id$="_Body"],'
    + ' textarea[id*="_Note_"], textarea[id$="_Note"],'
    + ' textarea[id*="_Description_"], textarea[id$="_Description"],'
    + ' textarea[id*="_Content_"], textarea[id$="_Content"]';

  function ready() {
    return window.CKEDITOR && window.CKEDITOR.ClassicEditor;
  }

  // Build a per-editor config that adds the "upload from computer" button and a
  // custom upload adapter posting to the file-upload child's endpoint
  // (<model>/upload, plupload-compatible — field "file"). The stored file is
  // web-served at _SITE_URL + <response.File>, which becomes the image src.
  function withUploadConfig(base, uploadUrl, parentId) {
    var K = window.CKEDITOR;
    var cfg = {};
    for (var k in base) { if (Object.prototype.hasOwnProperty.call(base, k)) { cfg[k] = base[k]; } }

    cfg.plugins = (base.plugins || []).slice();
    if (K.ImageUpload && cfg.plugins.indexOf(K.ImageUpload) === -1) { cfg.plugins.push(K.ImageUpload); }

    var items = (base.toolbar && base.toolbar.items)
      ? base.toolbar.items.slice()
      : (Array.isArray(base.toolbar) ? base.toolbar.slice() : []);
    if (items.indexOf('uploadImage') === -1) {
      var at = items.indexOf('link');
      if (at >= 0) { items.splice(at + 1, 0, 'uploadImage'); } else { items.push('uploadImage'); }
    }
    cfg.toolbar = { items: items, shouldNotGroupWhenFull: (base.toolbar && base.toolbar.shouldNotGroupWhenFull) || true };

    cfg.extraPlugins = (base.extraPlugins || []).concat([uploadAdapterPlugin(uploadUrl, parentId)]);
    return cfg;
  }

  // CKEditor 5 custom upload adapter → the GoatCheese file-upload child.
  // uploadUrl is absolute (baked with _SITE_URL by the emitter). The site base
  // (for serving the stored file) is that URL minus its trailing "<model>/upload".
  // parentId links the uploaded file to this record (the child FK is required).
  function uploadAdapterPlugin(uploadUrl, parentId) {
    return function (editor) {
      var siteBase = uploadUrl.replace(/[^/]+\/upload$/, '');
      editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
        return {
          upload: function () {
            return loader.file.then(function (file) {
              return new Promise(function (resolve, reject) {
                var fd = new FormData();
                fd.append('file', file);
                // The server nests POST fields under request['data'], so the
                // parent id goes as top-level 'ip' (matches the plupload client).
                fd.append('ip', parentId || '');
                fetch(uploadUrl, {
                  method: 'POST',
                  credentials: 'same-origin',
                  headers: { 'X-Requested-With': 'XMLHttpRequest' },
                  body: fd
                })
                  .then(function (r) { return r.json().catch(function () { return {}; }); })
                  .then(function (j) {
                    if (!j || j.status !== 'success' || !j.File) {
                      reject((j && j.messages) ? String(j.messages) : 'Upload failed');
                      return;
                    }
                    resolve({ default: siteBase + j.File });
                  })
                  .catch(function (e) { reject(e); });
              });
            });
          },
          abort: function () {}
        };
      };
    };
  }

  function createOne(el) {
    if (!el || !el.id) { return; }
    // Idempotency: already bound or mid-creation. ClassicEditor.create is async,
    // so mark synchronously to survive a second bindWithin before resolution.
    if (el.getAttribute('data-gc-editor')) { return; }
    el.setAttribute('data-gc-editor', 'pending');

    // If the editor lives inside a form that declares a file-upload child
    // (data-gc-upload-model, emitted only when such a child exists), enable the
    // "upload from computer" button and route its bytes to that child.
    var cfg = window.gcEditorConfig || {};
    var holder = el.closest ? el.closest('[data-gc-upload-url]') : null;
    var uploadUrl = holder ? holder.getAttribute('data-gc-upload-url') : null;
    if (uploadUrl) {
        cfg = withUploadConfig(cfg, uploadUrl, holder.getAttribute('data-gc-upload-parent') || '');
    }

    window.CKEDITOR.ClassicEditor.create(el, cfg).then(function (editor) {
      // Element may have been popped before create resolved — tear down.
      if (el.getAttribute('data-gc-editor') === null) {
        editor.destroy();
        return;
      }
      entries.push({ el: el, editor: editor });
      el.setAttribute('data-gc-editor', 'bound');

      // Route CKEditor's warning notifications (e.g. a failed image upload, which
      // the upload adapter rejects with 'Upload failed') to the designed alertb
      // dialog. Otherwise the Notification plugin's default handler falls back to
      // a native window.alert — the "no native alerts" rule. high priority + stop
      // so the default never runs.
      if (editor.plugins.has('Notification')) {
        editor.plugins.get('Notification').on('show:warning', function (evt, data) {
          if (typeof window.alertb === 'function') {
            window.alertb((data && data.title) || 'Notice', (data && data.message) || '');
          }
          evt.stop();
        }, { priority: 'high' });
      }

      // Keep the source textarea always current so serializeData() (which reads
      // textarea.value directly) never misses unsynced editor content.
      editor.model.document.on('change:data', function () {
        editor.updateSourceElement();
      });

      // Track focus for insertHtml() targeting.
      var ft = editor.ui && editor.ui.focusTracker;
      if (ft) {
        ft.on('change:isFocused', function (evt, name, isFocused) {
          if (isFocused) { lastFocusedEl = el; }
        });
      }
    }).catch(function (err) {
      // Degrade gracefully — leave the plain textarea editable.
      el.removeAttribute('data-gc-editor');
      if (window.console) { console.warn('gcEditor: create failed for #' + el.id, err); }
    });
  }

  function destroyOne(el) {
    var entry = entryFor(el);
    dropEntry(el);
    if (lastFocusedEl === el) { lastFocusedEl = null; }
    if (entry) {
      try { entry.editor.updateSourceElement(); } catch (e) {}
      try { entry.editor.destroy(); } catch (e) {}
    }
  }

  var gcEditor = {
    // Upgrade every wysiwyg textarea inside `scope` (Element or document).
    bindWithin: function (scope) {
      if (!ready()) { return; }
      scope = scope || document;
      if (!scope.querySelectorAll) { return; }
      Array.prototype.forEach.call(scope.querySelectorAll(SELECTOR), createOne);
    },

    // Newest binding wins on duplicate ids (the topmost stacked screen).
    get: function (id) {
      for (var i = entries.length - 1; i >= 0; i--) {
        if (entries[i].el.id === id) { return entries[i].editor; }
      }
      return null;
    },

    // Flush editor content back to the underlying textareas in `scope`.
    syncWithin: function (scope) {
      scope = scope || document;
      if (!scope.querySelectorAll) { return; }
      Array.prototype.forEach.call(scope.querySelectorAll('textarea[id]'), function (t) {
        var entry = entryFor(t);
        if (entry) { try { entry.editor.updateSourceElement(); } catch (e) {} }
      });
    },

    // Destroy every editor whose textarea lives in `scope` (called on pop()).
    destroyWithin: function (scope) {
      scope = scope || document;
      if (!scope.querySelectorAll) { return; }
      Array.prototype.forEach.call(scope.querySelectorAll('textarea[id]'), function (t) {
        // Clear the marker first so an in-flight create() tears itself down.
        if (t.getAttribute) { t.removeAttribute('data-gc-editor'); }
        destroyOne(t);
      });
    },

    // Insert HTML at the cursor of the focused editor (file-manager image
    // insert). Mirrors CKE4's CKEDITOR.instances[focused].insertHtml().
    insertHtml: function (html) {
      var focused = lastFocusedEl ? entryFor(lastFocusedEl) : null;
      var editor = focused ? focused.editor : null;
      if (!editor && entries.length === 1) {
        // Fall back to the single editor on the page, if exactly one exists.
        editor = entries[0].editor;
      }
      if (!editor) {
        if (window.alertb) { alertb('Warning', 'Put the cursor where you want to insert the content.'); }
        return;
      }
      editor.model.change(function () {
        var viewFragment = editor.data.processor.toView(html);
        var modelFragment = editor.data.toModel(viewFragment);
        editor.model.insertContent(modelFragment);
      });
      editor.editing.view.focus();
    }
  };

  window.gcEditor = gcEditor;
})();
