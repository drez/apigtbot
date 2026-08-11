/*
 * CKEditor 5 configuration consumed by gcEditor (public/js/gceditor.js).
 *
 * Loaded after public/vendor/ckeditor5/ckeditor5.umd.js, so the CKE5 namespace
 * is available as window.CKEDITOR with each plugin as a named export. This
 * object reproduces the feature set the old CKEditor 4 build offered (see the
 * migration plan): formatting, headings, lists, alignment/indent, links,
 * images, tables, source view, find & replace, undo/redo — plus
 * GeneralHtmlSupport to preserve the old `allowedContent = true` behaviour
 * (arbitrary HTML survives a save round-trip).
 *
 * licenseKey 'GPL' = the free open-source license required by CKEditor 5 v44+.
 */
(function () {
  'use strict';
  var K = window.CKEDITOR;
  if (!K) { return; }

  window.gcEditorConfig = {
    licenseKey: 'GPL',

    plugins: [
      K.Essentials, K.Paragraph, K.Heading,
      K.Bold, K.Italic, K.Strikethrough, K.RemoveFormat,
      K.List, K.ListProperties,
      K.Alignment, K.Indent, K.IndentBlock,
      K.Link, K.AutoLink, K.LinkImage,
      // Image rendering/manipulation only. The "upload from computer" button
      // (ImageUpload + uploadImage) is added per-editor by gcEditor ONLY when
      // the form has a file-upload child to receive the bytes — there is no
      // unconditional upload button (it would have nowhere to upload to).
      K.Image, K.ImageToolbar, K.ImageStyle, K.ImageCaption, K.ImageResize,
      K.Table, K.TableToolbar,
      K.BlockQuote, K.CodeBlock,
      K.SourceEditing, K.FindAndReplace,
      K.GeneralHtmlSupport
    ],

    toolbar: {
      items: [
        'undo', 'redo', '|',
        'heading', '|',
        'bold', 'italic', 'strikethrough', 'removeFormat', '|',
        'link', 'insertTable', 'blockQuote', 'codeBlock', '|',
        'bulletedList', 'numberedList', '|',
        'alignment', 'outdent', 'indent', '|',
        'findAndReplace', 'sourceEditing'
      ],
      shouldNotGroupWhenFull: true
    },

    heading: {
      options: [
        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
      ]
    },

    image: {
      toolbar: [
        'imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|',
        'toggleImageCaption', 'imageTextAlternative'
      ]
    },

    table: {
      contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    },

    link: {
      addTargetToExternalLinks: false
    },

    // Reproduces CKEditor 4's `config.allowedContent = true`: keep any HTML the
    // user (or a programmatic insert) puts in, without stripping tags/attrs.
    htmlSupport: {
      allow: [
        { name: /.*/, attributes: true, classes: true, styles: true }
      ]
    }
  };
})();
