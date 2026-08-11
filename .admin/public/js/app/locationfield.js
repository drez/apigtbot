/* gcLocationField — vanilla address-search + Leaflet map widget.
 *
 * Promoted from an earlier project's hand-written address search
 * (the `set_input_options: {type:"location"}` behavior). The emitter emits
 * `gcLocationField.create({input, lat, lng, zip, country})` into the form's
 * onReadyJs where the values are DOM element ids (Propel PhpNames, e.g.
 * "Address1"/"Latitude"); `zip`/`country` may be null.
 *
 * Behavior (mirror of the reference, plus a draggable marker):
 *  - debounced (400ms, min 3 chars) search on input → dropdown of results
 *  - picking a result does NOT overwrite the typed address: a resolved-
 *    address line with a "Use" link is shown; lat/lng inputs are set
 *    (change dispatched), the map updates, zip autofills when configured
 *  - typing clears lat/lng + the resolved line; on blur/change with no
 *    coords the first match is geocoded silently (coords only)
 *  - map container created after the input's container, hidden until
 *    coords exist; marker is draggable — dragend writes lat/lng
 *    immediately, then reverse-geocodes into the resolved line
 *  - geocoding goes through the runtime proxy `ApiGoat/geocode` /
 *    `ApiGoat/reverseGeocode` (Nominatim-shaped JSON)
 *  - Leaflet is lazy-loaded from public/vendor/leaflet/ (the form arrives
 *    via innerHTML, so markup <script src> tags never execute — the
 *    whenLeafletReady onload queue is mandatory)
 *
 * No jQuery, no native alerts. Idempotent per input via input._gcLocBound
 * (safe against screens.js readyJs re-exec). */
(function () {
    'use strict';

    /* Load Leaflet (CSS + JS) on demand and run cb once "L" exists.
     * Shared queue: several fields / repeated create() calls funnel into a
     * single <script> injection. Degrades silently if the asset is missing. */
    function whenLeafletReady(cb) {
        if (window.L) { cb(); return; }
        if (!window.__leafletLoadQueue) {
            window.__leafletLoadQueue = [];
            if (!document.querySelector('link[data-leaflet]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = _SITE_URL + 'public/vendor/leaflet/leaflet.css';
                link.setAttribute('data-leaflet', '1');
                document.head.appendChild(link);
            }
            var s = document.createElement('script');
            s.src = _SITE_URL + 'public/vendor/leaflet/leaflet.js';
            s.onload = function () {
                var q = window.__leafletLoadQueue || [];
                window.__leafletLoadQueue = null;
                q.forEach(function (fn) { try { fn(); } catch (e) { /* noop */ } });
            };
            s.onerror = function () { window.__leafletLoadQueue = null; };
            document.head.appendChild(s);
        }
        if (window.__leafletLoadQueue) { window.__leafletLoadQueue.push(cb); }
        else if (window.L) { cb(); }
    }

    /* Plain GET (the CSRF fetch wrapper leaves GET untouched), same-origin
     * credentials, 5s abort — mirrors the reference fetch discipline. */
    function getJson(url, params, onSuccess, onError) {
        var qs = new URLSearchParams(params);
        var ctrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        var timer = ctrl ? setTimeout(function () { ctrl.abort(); }, 5000) : null;
        var opts = { credentials: 'same-origin' };
        if (ctrl) { opts.signal = ctrl.signal; }
        fetch(url + '?' + qs.toString(), opts)
            .then(function (res) {
                if (!res.ok) { throw new Error('HTTP ' + res.status); }
                return res.json();
            })
            .then(function (data) {
                if (timer) { clearTimeout(timer); }
                if (onSuccess) { onSuccess(data); }
            })
            .catch(function () {
                if (timer) { clearTimeout(timer); }
                if (onError) { onError(); }
            });
    }

    function create(cfg) {
        var input = document.getElementById(cfg.input);
        if (!input || input._gcLocBound) { return; }
        input._gcLocBound = true;

        var searchTimeout = null;
        var map = null;
        var marker = null;
        var mapId = cfg.input + '_gcLocationMap';

        function coordSel(id) { return '#' + id + ', [name="' + id + '"]'; }

        function setCoordInputs(id, value, fireChange) {
            if (!id) { return; }
            document.querySelectorAll(coordSel(id)).forEach(function (el) {
                el.value = value;
                if (fireChange) { el.dispatchEvent(new Event('change', { bubbles: true })); }
            });
        }

        function getCoordValue(id) {
            var el = id ? document.querySelector(coordSel(id)) : null;
            return el ? el.value : '';
        }

        /* ---- container / dropdown / map DOM (created once per bind) ---- */
        if (!input.closest('.address-search-container')) {
            var wrapDiv = document.createElement('div');
            wrapDiv.className = 'address-search-container';
            input.parentNode.insertBefore(wrapDiv, input);
            wrapDiv.appendChild(input);
            var dd = document.createElement('div');
            dd.className = 'address-search-dropdown';
            dd.style.display = 'none';
            input.insertAdjacentElement('afterend', dd);
            input.classList.add('address-search-input');
        }
        var container = input.closest('.address-search-container');
        var dropdown = container.querySelector(':scope > .address-search-dropdown');

        if (!document.getElementById(mapId)) {
            var mapEl = document.createElement('div');
            mapEl.id = mapId;
            mapEl.className = 'gc-location-map';
            mapEl.style.display = 'none';
            container.insertAdjacentElement('afterend', mapEl);
        }

        /* ---- resolved-address line with "Use" link ---- */
        function showResolved(text) {
            var resolved = container.querySelector(':scope > .address-search-resolved');
            if (!resolved) {
                resolved = document.createElement('div');
                resolved.className = 'address-search-resolved';
                resolved.style.display = 'none';
                var icon = document.createElement('i');
                icon.className = 'ri-map-pin-line';
                var span = document.createElement('span');
                span.className = 'address-search-resolved-text';
                var use = document.createElement('a');
                use.href = '#';
                use.className = 'address-search-resolved-use';
                use.textContent = 'Use';
                resolved.appendChild(icon);
                resolved.appendChild(document.createTextNode(' '));
                resolved.appendChild(span);
                resolved.appendChild(document.createTextNode(' '));
                resolved.appendChild(use);
                container.appendChild(resolved);
                use.addEventListener('click', function (e) {
                    e.preventDefault();
                    var t = span.textContent;
                    if (t) {
                        input.value = t;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        resolved.style.display = 'none';
                    }
                });
            }
            var resolvedText = resolved.querySelector('.address-search-resolved-text');
            if (resolvedText) { resolvedText.textContent = text || ''; }
            resolved.style.display = text ? '' : 'none';
        }

        function clearResolved() {
            var resolved = container.querySelector(':scope > .address-search-resolved');
            if (!resolved) { return; }
            resolved.style.display = 'none';
            var resolvedText = resolved.querySelector('.address-search-resolved-text');
            if (resolvedText) { resolvedText.textContent = ''; }
        }

        /* ---- map + draggable marker ---- */
        function onMarkerDragEnd() {
            if (!marker) { return; }
            var ll = marker.getLatLng();
            var lat = Number(ll.lat.toFixed(8));
            var lng = Number(ll.lng.toFixed(8));
            // coords first, independent of the reverse geocode
            setCoordInputs(cfg.lat, String(lat), true);
            setCoordInputs(cfg.lng, String(lng), true);
            getJson(_SITE_URL + 'ApiGoat/reverseGeocode', { lat: lat, lng: lng }, function (result) {
                if (result && result.display_name) {
                    showResolved(result.display_name);
                }
            });
        }

        function updateMap(lat, lon) {
            lat = parseFloat(lat);
            lon = parseFloat(lon);
            if (isNaN(lat) || isNaN(lon)) { return; }
            whenLeafletReady(function () {
                var mapEl = document.getElementById(mapId);
                if (!mapEl) { return; }
                mapEl.style.display = 'block';
                if (!map) {
                    map = L.map(mapId).setView([lat, lon], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);
                    marker = L.marker([lat, lon], { draggable: true }).addTo(map);
                    marker.on('dragend', onMarkerDragEnd);
                } else {
                    map.setView([lat, lon], 15);
                    marker.setLatLng([lat, lon]);
                }
                setTimeout(function () { map.invalidateSize(); }, 50);
            });
        }

        function setCoordsAndMap(lat, lon) {
            var selLat = lat || '';
            var selLon = lon || '';
            setCoordInputs(cfg.lat, selLat, true);
            setCoordInputs(cfg.lng, selLon, true);
            if (selLat && selLon) { updateMap(selLat, selLon); }
        }

        function applyZipFromPostcode(postcode) {
            if (!cfg.zip) { return; }
            var zipEl = document.getElementById(cfg.zip)
                || document.querySelector('[name="' + cfg.zip + '"]');
            if (zipEl) {
                zipEl.value = postcode || '';
                zipEl.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function applyGeocodeResult(result) {
            if (!result) { return; }
            var postcode = (result.address && result.address.postcode) ? result.address.postcode : '';
            if (result.display_name) {
                showResolved(result.display_name);
            }
            setCoordsAndMap(result.lat || '', result.lon || '');
            applyZipFromPostcode(postcode);
        }

        /* ---- geocode via the runtime proxy (Nominatim-shaped JSON) ---- */
        function geocodeAddress(query, onSuccess, onError) {
            var params = { q: query, limit: 8 };
            if (cfg.country) { params.country = cfg.country; }
            getJson(_SITE_URL + 'ApiGoat/geocode', params, function (results) {
                if (onSuccess) { onSuccess(results || []); }
            }, onError);
        }

        function dropdownMessage(text) {
            dropdown.textContent = '';
            var div = document.createElement('div');
            div.className = 'address-search-empty';
            div.textContent = text;
            dropdown.appendChild(div);
            dropdown.style.display = 'block';
        }

        function renderResults(results) {
            if (results.length === 0) {
                dropdownMessage('No results found');
                return;
            }
            dropdown.textContent = '';
            results.forEach(function (result) {
                var a = result.address || {};
                var postcode = a.postcode || '';
                var line1 = ((a.house_number ? a.house_number + ' ' : '')
                    + (a.road || a.street || '')).trim();
                var line2 = ((a.city || a.town || '') + ', '
                    + (postcode ? postcode + ' ' : '')
                    + (a.country || ''));
                var item = document.createElement('div');
                item.className = 'address-search-item';
                item.dataset.lat = result.lat || '';
                item.dataset.lon = result.lon || '';
                item.dataset.postcode = postcode;
                var strong = document.createElement('strong');
                strong.textContent = line1;
                item.appendChild(strong);
                item.appendChild(document.createElement('br'));
                item.appendChild(document.createTextNode(line2));
                // short resolved label: "house road, city, country" (postcode
                // stripped — it lands in the zip field when configured)
                var afterBr = line2;
                if (postcode) { afterBr = afterBr.split(postcode).join(''); }
                item.dataset.address = (line1 + ', ' + afterBr)
                    .replace(/,\s*,/g, ',').replace(/\s+/g, ' ').replace(/,\s*$/, '').trim();
                item.addEventListener('click', function () {
                    showResolved(this.dataset.address);
                    setCoordsAndMap(this.dataset.lat, this.dataset.lon);
                    applyZipFromPostcode(this.dataset.postcode);
                    dropdown.style.display = 'none';
                });
                dropdown.appendChild(item);
            });
            dropdown.style.display = 'block';
        }

        /* ---- events ---- */
        input.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            var query = this.value.trim();
            setCoordInputs(cfg.lat, '', false);
            setCoordInputs(cfg.lng, '', false);
            clearResolved();

            if (!dropdown) { return; }
            if (query.length < 3) {
                dropdown.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(function () {
                geocodeAddress(query, renderResults, function () {
                    dropdownMessage('Error searching addresses');
                });
            }, 400);
        });

        // blur/change fallback: silently geocode the first match (coords
        // only, the typed address text is never touched)
        function onAddressCommit() {
            var query = input.value.trim();
            var hasCoords = getCoordValue(cfg.lat) && getCoordValue(cfg.lng);
            if (!query || query.length < 3 || hasCoords) { return; }
            geocodeAddress(query, function (results) {
                if (results && results.length) {
                    applyGeocodeResult(results[0]);
                }
            });
        }
        input.addEventListener('change', onAddressCommit);
        input.addEventListener('blur', onAddressCommit);

        // Init map for an existing record that already has coordinates
        var initLat = getCoordValue(cfg.lat);
        var initLon = getCoordValue(cfg.lng);
        if (initLat && initLon) {
            updateMap(initLat, initLon);
        }
    }

    // Close any open dropdown on outside click — bound once, document-level
    // (survives form re-injection; per-instance dropdowns are re-queried).
    if (!window.__gcLocationOutsideBound) {
        window.__gcLocationOutsideBound = true;
        document.addEventListener('click', function (e) {
            if (!(e.target.closest && e.target.closest('.address-search-container'))) {
                document.querySelectorAll('.address-search-dropdown').forEach(function (d) {
                    d.style.display = 'none';
                });
            }
        });
    }

    window.gcLocationField = { create: create };
})();
