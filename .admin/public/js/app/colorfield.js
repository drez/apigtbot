/* gcColorField — vanilla color widget. Enhances any <input data-gc-color>:
 * wraps it in .gc-color-field, adds a clickable swatch that opens the native
 * <input type="color"> picker, keeps the hex text editable, and allows empty
 * (not-required). No jQuery. Idempotent via input._gcBound. */
(function () {
    'use strict';

    var HEX_FULL = /^#[0-9a-fA-F]{6}$/;

    function paint(swatch, value) {
        if (HEX_FULL.test(value)) {
            swatch.style.background = value;
            swatch.classList.remove('is-empty');
        } else {
            swatch.style.background = '';
            swatch.classList.add('is-empty');
        }
    }

    function bindOne(input) {
        if (input._gcBound) { return; }
        input._gcBound = true;

        var wrap = document.createElement('span');
        wrap.className = 'gc-color-field';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var swatch = document.createElement('button');
        swatch.type = 'button';
        swatch.className = 'gc-color-swatch';
        swatch.tabIndex = -1;
        swatch.setAttribute('aria-label', 'Pick a color');
        wrap.appendChild(swatch);

        var native = document.createElement('input');
        native.type = 'color';
        native.className = 'gc-color-native';
        native.tabIndex = -1;
        native.setAttribute('aria-hidden', 'true');
        wrap.appendChild(native);

        paint(swatch, input.value);

        swatch.addEventListener('click', function () {
            native.value = HEX_FULL.test(input.value) ? input.value : '#000000';
            native.click();
        });
        native.addEventListener('input', function () {
            input.value = native.value;
            paint(swatch, input.value);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        input.addEventListener('input', function () {
            paint(swatch, input.value);
        });
    }

    window.gcColorField = {
        bindWithin: function (root) {
            root = root || document;
            if (!root.querySelectorAll) { return; }
            var inputs = root.querySelectorAll('input[data-gc-color]');
            Array.prototype.forEach.call(inputs, bindOne);
        }
    };

    if (document.readyState !== 'loading') {
        window.gcColorField.bindWithin(document);
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            window.gcColorField.bindWithin(document);
        });
    }
})();
